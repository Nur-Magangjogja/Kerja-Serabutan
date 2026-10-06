<?php

namespace Tests\Feature;

use App\Livewire\Customer\Chat\Index as CustomerChatIndex;
use App\Livewire\Mitra\Chat\Index as MitraChatIndex;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class SupportThreadConcurrencyD2FTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sending_multiple_support_messages_reuses_single_partner_report(): void
    {
        $customer = User::factory()->create([
            'role'     => 'customer',
            'status'   => 'active',
            'verified' => true,
        ]);

        Auth::login($customer);
        Cache::flush();

        $component = Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('admin_tab', 'support');

        // First message
        $component->set('message', 'Halo admin, saya butuh bantuan awal.')
            ->call('sendMessage');

        $reportsCount1 = PartnerReport::where('reporter_id', $customer->id)
            ->where('report_type', 'dukungan_umum')
            ->count();
        $this->assertEquals(1, $reportsCount1, 'Exactly one support report should be created after first message.');

        $firstReport = PartnerReport::where('reporter_id', $customer->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertEquals('in_progress', $firstReport->status);

        // Second message with completely different body
        $component->set('message', 'Pesan lanjutan kedua mengenai kendala akun.')
            ->call('sendMessage');

        $reportsCount2 = PartnerReport::where('reporter_id', $customer->id)
            ->where('report_type', 'dukungan_umum')
            ->count();
        $this->assertEquals(1, $reportsCount2, 'Still exactly one support report should exist after second message.');

        // Verify both messages belong to the same single support report
        $messages = PartnerReportMessage::where('partner_report_id', $firstReport->id)->orderBy('id')->get();
        $this->assertCount(2, $messages, 'Both submitted messages must be retained under the same support report.');
        $this->assertEquals('Halo admin, saya butuh bantuan awal.', $messages[0]->message);
        $this->assertEquals('Pesan lanjutan kedua mengenai kendala akun.', $messages[1]->message);
    }

    public function test_mitra_sending_multiple_support_messages_reuses_single_partner_report(): void
    {
        $mitra = User::factory()->create([
            'role'     => 'mitra',
            'status'   => 'active',
            'verified' => true,
        ]);

        Auth::login($mitra);
        Cache::flush();

        $component = Livewire::test(MitraChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('admin_tab', 'support');

        // First message
        $component->set('message', 'Halo admin mitra, saya ada pertanyaan komisi.')
            ->call('sendMessage');

        $reportsCount1 = PartnerReport::where('reporter_id', $mitra->id)
            ->where('report_type', 'dukungan_umum')
            ->count();
        $this->assertEquals(1, $reportsCount1, 'Exactly one support report should be created for mitra.');

        $firstReport = PartnerReport::where('reporter_id', $mitra->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        // Second message
        $component->set('message', 'Pertanyaan kedua terkait penarikan dana.')
            ->call('sendMessage');

        $reportsCount2 = PartnerReport::where('reporter_id', $mitra->id)
            ->where('report_type', 'dukungan_umum')
            ->count();
        $this->assertEquals(1, $reportsCount2, 'Still exactly one support report should exist for mitra.');

        $messages = PartnerReportMessage::where('partner_report_id', $firstReport->id)->orderBy('id')->get();
        $this->assertCount(2, $messages, 'Both messages must be attached to the single active mitra support thread.');
    }

    public function test_support_messages_do_not_contaminate_or_reuse_investigation_reports(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active', 'verified' => true]);
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active', 'verified' => true]);

        $help = Help::create([
            'user_id'        => $customer->id,
            'mitra_id'       => $mitra->id,
            'title'          => 'Bantuan Pembersihan',
            'category'       => 'Pembersihan',
            'description'    => 'Deskripsi bantuan',
            'price'          => 50000,
            'status'         => Help::STATUS_IN_PROGRESS,
            'address'        => 'Jl Kaliurang',
            'latitude'       => -7.7100,
            'longitude'      => 110.4000,
            'payment_status' => 'paid',
        ]);

        // Create an active investigation report for this help
        $investigationReport = PartnerReport::create([
            'reporter_id'      => $customer->id,
            'reported_user_id' => $mitra->id,
            'reported_help_id' => $help->id,
            'category'         => 'dari_customer',
            'report_type'      => 'pelayanan_tidak_sesuai',
            'title'            => 'Laporan Investigasi',
            'message'          => 'Mitra tidak sesuai deskripsi',
            'status'           => 'pending',
        ]);

        Auth::login($customer);
        Cache::flush();

        // Send a message via support tab
        $component = Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('admin_tab', 'support');

        $component->set('message', 'Pesan ke bantuan umum, bukan investigasi.')
            ->call('sendMessage');

        // Verify that a NEW dukungan_umum report was created, and investigation was NOT reused
        $supportReports = PartnerReport::where('reporter_id', $customer->id)
            ->where('report_type', 'dukungan_umum')
            ->get();
        $this->assertCount(1, $supportReports);

        $investigationMessages = PartnerReportMessage::where('partner_report_id', $investigationReport->id)->get();
        $this->assertCount(0, $investigationMessages, 'Investigation report must not receive support messages.');
    }

    public function test_support_messages_do_not_cross_reuse_between_different_users(): void
    {
        $customerA = User::factory()->create(['role' => 'customer', 'status' => 'active', 'verified' => true]);
        $customerB = User::factory()->create(['role' => 'customer', 'status' => 'active', 'verified' => true]);

        // Customer A sends support message
        Auth::login($customerA);
        Cache::flush();
        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->set('message', 'Pesan dari Customer A')
            ->call('sendMessage');

        $reportA = PartnerReport::where('reporter_id', $customerA->id)->where('report_type', 'dukungan_umum')->first();
        $this->assertNotNull($reportA);

        // Customer B sends support message
        Auth::login($customerB);
        Cache::flush();
        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->set('message', 'Pesan dari Customer B')
            ->call('sendMessage');

        $reportB = PartnerReport::where('reporter_id', $customerB->id)->where('report_type', 'dukungan_umum')->first();
        $this->assertNotNull($reportB);

        $this->assertNotEquals($reportA->id, $reportB->id, 'Users must have isolated support reports.');
        $this->assertEquals(1, PartnerReportMessage::where('partner_report_id', $reportA->id)->count());
        $this->assertEquals(1, PartnerReportMessage::where('partner_report_id', $reportB->id)->count());
    }
}
