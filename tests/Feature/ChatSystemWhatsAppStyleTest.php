<?php

namespace Tests\Feature;

use App\Livewire\Customer\Chat\Index as CustomerChatIndex;
use App\Livewire\Mitra\Chat\Index as MitraChatIndex;
use App\Models\Chat;
use App\Models\Help;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class ChatSystemWhatsAppStyleTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $mitra;
    protected User $otherCustomer;
    protected Help $help;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role'   => 'customer',
            'status' => 'active',
        ]);

        $this->mitra = User::factory()->create([
            'role'   => 'mitra',
            'status' => 'active',
        ]);

        $this->otherCustomer = User::factory()->create([
            'role'   => 'customer',
            'status' => 'active',
        ]);

        $this->help = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'title'          => 'Bantuan Test',
            'description'    => 'Deskripsi bantuan test',
            'price'          => 50000,
            'status'         => 'accepted',
            'address'        => 'Jl Test No 1',
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
            'payment_status' => 'paid',
        ]);
    }

    public function test_customer_cannot_access_other_users_help_chat()
    {
        Auth::login($this->otherCustomer);

        Livewire::test(CustomerChatIndex::class, ['help' => $this->help->id])
            ->assertRedirect(route('customer.chat'));
    }

    public function test_customer_can_access_own_help_chat()
    {
        Auth::login($this->customer);

        Livewire::test(CustomerChatIndex::class, ['help' => $this->help->id])
            ->assertSet('active_help_id', $this->help->id)
            ->assertSet('selected_partner_id', $this->mitra->id);
    }

    public function test_mitra_cannot_access_unassigned_help_chat()
    {
        $otherMitra = User::factory()->create(['role' => 'mitra', 'status' => 'active']);
        Auth::login($otherMitra);

        Livewire::test(MitraChatIndex::class, ['help' => $this->help->id])
            ->assertRedirect(route('mitra.chat'));
    }

    public function test_mitra_can_access_assigned_help_chat()
    {
        Auth::login($this->mitra);

        Livewire::test(MitraChatIndex::class, ['help' => $this->help->id])
            ->assertSet('active_help_id', $this->help->id)
            ->assertSet('selected_partner_id', $this->customer->id);
    }

    public function test_backend_prevents_duplicate_messages_within_2_seconds()
    {
        Auth::login($this->customer);
        Cache::flush();

        $component = Livewire::test(CustomerChatIndex::class, ['help' => $this->help->id])
            ->set('message', 'Halo Mitra!')
            ->call('sendMessage');

        $this->assertEquals(1, Chat::where('message', 'Halo Mitra!')->count());

        // Attempt second send immediately with same message
        $component->set('message', 'Halo Mitra!')
            ->call('sendMessage');

        // Still only 1 row created
        $this->assertEquals(1, Chat::where('message', 'Halo Mitra!')->count());
    }

    public function test_chat_message_notification_url_points_directly_to_role_chat()
    {
        $notifToMitra = new ChatMessageNotification($this->help->id, 'Halo Mitra', $this->customer->id, 'Customer');
        $dataMitra = $notifToMitra->toArray($this->mitra);
        $this->assertStringContainsString('mitra/chat/' . $this->help->id, $dataMitra['url']);

        $notifToCustomer = new ChatMessageNotification($this->help->id, 'Halo Customer', $this->mitra->id, 'Mitra');
        $dataCustomer = $notifToCustomer->toArray($this->customer);
        $this->assertStringContainsString('customer/chat/' . $this->help->id, $dataCustomer['url']);
    }

    public function test_chat_read_receipt_and_is_read_flag()
    {
        $chat = Chat::create([
            'help_id'     => $this->help->id,
            'mitra_id'    => $this->mitra->id,
            'customer_id' => $this->customer->id,
            'message'     => 'Pesan belum dibaca',
            'sender_type' => 'customer',
            'is_read'     => false,
        ]);

        $this->assertFalse($chat->is_read);

        // Mitra opens the chat
        Auth::login($this->mitra);
        Livewire::test(MitraChatIndex::class, ['help' => $this->help->id]);

        $chat->refresh();
        $this->assertTrue($chat->is_read);
        $this->assertNotNull($chat->read_at);
    }

    public function test_chat_message_does_not_create_database_notification()
    {
        Auth::login($this->customer);

        $countBefore = \Illuminate\Support\Facades\DB::table('notifications')->count();

        Livewire::test(CustomerChatIndex::class, ['help' => $this->help->id])
            ->set('message', 'Pesan tanpa database notif')
            ->call('sendMessage');

        // Chat exists
        $this->assertEquals(1, Chat::where('message', 'Pesan tanpa database notif')->count());

        // Notifications count must not increase
        $this->assertEquals($countBefore, \Illuminate\Support\Facades\DB::table('notifications')->count());

        // No chat_message type notification exists
        $chatNotifs = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('type', 'App\Notifications\ChatMessageNotification')
            ->orWhere('data->type', 'chat_message')
            ->count();
        $this->assertEquals(0, $chatNotifs);
    }

    public function test_livewire_chat_icon_reflects_unread_messages()
    {
        // Customer sends message to Mitra
        Chat::create([
            'help_id'     => $this->help->id,
            'mitra_id'    => $this->mitra->id,
            'customer_id' => $this->customer->id,
            'message'     => 'Hai Mitra',
            'sender_type' => 'customer',
            'is_read'     => false,
        ]);

        Auth::login($this->mitra);

        // Mitra's chat icon should show 1 unread
        Livewire::test(\App\Livewire\Chat\ChatIcon::class, ['role' => 'mitra'])
            ->assertSet('unreadCount', 1);

        // Customer's chat icon should show 0 unread
        Auth::login($this->customer);
        Livewire::test(\App\Livewire\Chat\ChatIcon::class, ['role' => 'customer'])
            ->assertSet('unreadCount', 0);
    }

    public function test_livewire_notification_icon_only_counts_activity_notifications()
    {
        Auth::login($this->customer);

        // Send activity notification (HelpTaken)
        $this->customer->notify(new \App\Notifications\HelpTakenNotification($this->help, $this->mitra));

        // Send ChatMessageNotification (which has via = [])
        $this->customer->notify(new ChatMessageNotification($this->help->id, 'Chat test', $this->mitra->id, 'Mitra'));

        // Notification icon should only count 1 (the HelpTaken activity)
        Livewire::test(\App\Livewire\Notifications\NotificationIcon::class, ['role' => 'customer'])
            ->assertSet('unreadCount', 1);
    }
}
