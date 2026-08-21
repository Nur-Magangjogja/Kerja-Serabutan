<?php

namespace App\Livewire\Mitra\Chat;

use App\Models\Chat as ChatModel;
use App\Models\Help;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public $selected_partner_id = null; // ID Customer yang sedang diajak chat
    public $selected_partner = null;    // Objek User Customer
    public $active_help_id = null;      // ID Help terkini/terkait
    public $active_help = null;         // Objek Help terkini/terkait
    public $message = '';
    public $search = '';

    protected $rules = [
        'message' => 'required|string|max:1000',
    ];

    public function mount($help = null)
    {
        // Jika route dibuka dengan parameter bantuan (misal /mitra/chat/{help})
        if ($help) {
            $helpModel = Help::with(['user', 'mitra'])->find($help);

            if ($helpModel && $helpModel->user_id) {
                $this->selectPartner($helpModel->user_id, $helpModel->id);
            }
        }
    }

    public function getConversations()
    {
        $mitraId = Auth::id();

        // Ambil seluruh ID Customer yang pernah bertukar pesan dengan mitra ini
        $chatCustomerIds = ChatModel::where('mitra_id', $mitraId)
            ->whereNotNull('customer_id')
            ->pluck('customer_id');

        // Dan ID Customer dari bantuan yang pernah/sedang diambil mitra ini
        $helpCustomerIds = Help::where('mitra_id', $mitraId)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $customerIds = $chatCustomerIds->merge($helpCustomerIds)->unique()->values();

        if ($customerIds->isEmpty()) {
            return collect();
        }

        $customersQuery = User::whereIn('id', $customerIds);

        if ($this->search) {
            $customersQuery->where('name', 'like', '%' . $this->search . '%');
        }

        $customers = $customersQuery->get();

        // Transformasi menjadi 1 baris percakapan per Customer (Unique Partner)
        $conversations = $customers->map(function ($customer) use ($mitraId) {
            $lastMessage = ChatModel::where('mitra_id', $mitraId)
                ->where('customer_id', $customer->id)
                ->latest('created_at')
                ->first();

            $unreadCount = ChatModel::where('mitra_id', $mitraId)
                ->where('customer_id', $customer->id)
                ->where('sender_type', 'customer')
                ->whereNull('read_at')
                ->count();

            // Cari bantuan aktif atau terakhir antara customer dan mitra ini
            $latestHelp = Help::where('user_id', $customer->id)
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();

            return (object) [
                'partner'      => $customer,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'latest_help'  => $latestHelp,
                'updated_at'   => $lastMessage?->created_at ?? $latestHelp?->updated_at ?? $customer->updated_at,
            ];
        })
        ->filter(fn($c) => $c->last_message !== null || $c->latest_help !== null)
        ->sortByDesc('updated_at')
        ->values();

        return $conversations;
    }

    public function getMessages()
    {
        if (!$this->selected_partner_id) {
            return collect();
        }

        $mitraId    = Auth::id();
        $customerId = $this->selected_partner_id;

        return ChatModel::where('mitra_id', $mitraId)
            ->where('customer_id', $customerId)
            ->with('help')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function selectPartner($customerId, $helpId = null)
    {
        $this->selected_partner_id = (int) $customerId;
        $this->selected_partner    = User::find($customerId);

        if ($helpId) {
            $this->active_help_id = (int) $helpId;
            $this->active_help    = Help::find($helpId);
        } else {
            $latestHelp = Help::where('user_id', $customerId)
                ->where('mitra_id', Auth::id())
                ->latest('updated_at')
                ->first();

            $this->active_help_id = $latestHelp?->id;
            $this->active_help    = $latestHelp;
        }

        // Tandai pesan dari customer sebagai sudah dibaca
        ChatModel::where('mitra_id', Auth::id())
            ->where('customer_id', $customerId)
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->dispatch('scroll-chat-bottom');
    }

    public function closeChat()
    {
        $this->selected_partner_id = null;
        $this->selected_partner    = null;
        $this->active_help_id      = null;
        $this->active_help         = null;
        $this->message             = '';
    }

    public function sendMessage()
    {
        $this->validate();

        if (!$this->selected_partner_id) {
            $this->dispatch('error', 'Pilih percakapan terlebih dahulu');
            return;
        }

        $mitraId    = Auth::id();
        $customerId = $this->selected_partner_id;

        // Pastikan ada help_id yang valid
        $helpId = $this->active_help_id;
        if (!$helpId) {
            $latestHelp = Help::where('user_id', $customerId)
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();
            $helpId = $latestHelp?->id;
        }

        if (!$helpId) {
            $anyChat = ChatModel::where('mitra_id', $mitraId)
                ->where('customer_id', $customerId)
                ->whereNotNull('help_id')
                ->latest('created_at')
                ->first();
            $helpId = $anyChat?->help_id;
        }

        if (!$helpId) {
            $this->dispatch('error', 'Tidak dapat mengirim pesan tanpa konteks bantuan.');
            return;
        }

        $chat = ChatModel::create([
            'help_id'     => $helpId,
            'mitra_id'    => $mitraId,
            'customer_id' => $customerId,
            'message'     => $this->message,
            'sender_type' => 'mitra',
            'is_read'     => false,
        ]);

        // Notifikasi ke Customer
        if ($this->selected_partner) {
            try {
                $this->selected_partner->notify(
                    new ChatMessageNotification($helpId, Str::limit($this->message, 150), $mitraId, Auth::user()->name)
                );
            } catch (\Throwable $e) {
                \Log::warning('[MitraChat] Failed to notify customer: ' . $e->getMessage());
            }
        }

        $this->message = '';
        $this->dispatch('message-sent');
    }

    public function render()
    {
        return view('livewire.mitra.chat.index', [
            'conversations' => $this->getConversations(),
            'messages'      => $this->getMessages(),
        ])->layout('layouts.mitra');
    }
}
