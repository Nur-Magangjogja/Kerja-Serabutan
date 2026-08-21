<?php

namespace App\Livewire\Customer\Chat;

use App\Models\Chat as ChatModel;
use App\Models\Help;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public $selected_partner_id = null; // ID Mitra yang sedang diajak chat
    public $selected_partner = null;    // Objek User Mitra
    public $active_help_id = null;      // ID Help terkini/terkait
    public $active_help = null;         // Objek Help terkini/terkait
    public $unassigned_help = null;     // Jika help belum diambil mitra
    public $message = '';
    public $search = '';

    protected $rules = [
        'message' => 'required|string|max:1000',
    ];

    public function mount($help = null)
    {
        // Jika route dibuka dengan parameter bantuan (misal /customer/chat/{help})
        if ($help) {
            $helpModel = Help::with(['mitra', 'user'])->find($help);

            if ($helpModel) {
                if ($helpModel->mitra_id) {
                    $this->selectPartner($helpModel->mitra_id, $helpModel->id);
                } else {
                    // Cek riwayat chat terakhir untuk menemukan mitra yang pernah terhubung
                    $lastChat = ChatModel::where('help_id', $helpModel->id)->latest('created_at')->first();
                    if ($lastChat && $lastChat->mitra_id) {
                        $this->selectPartner($lastChat->mitra_id, $helpModel->id);
                    } else {
                        $this->unassigned_help = $helpModel;
                    }
                }
            }
        }
    }

    public function getConversations()
    {
        $userId = Auth::id();

        // Ambil seluruh ID Mitra yang pernah berinteraksi chat dengan customer ini
        $chatMitraIds = ChatModel::where('customer_id', $userId)
            ->whereNotNull('mitra_id')
            ->pluck('mitra_id');

        // Dan ID Mitra dari bantuan aktif/lama milik customer
        $helpMitraIds = Help::where('user_id', $userId)
            ->whereNotNull('mitra_id')
            ->pluck('mitra_id');

        $mitraIds = $chatMitraIds->merge($helpMitraIds)->unique()->values();

        if ($mitraIds->isEmpty()) {
            return collect();
        }

        $mitrasQuery = User::whereIn('id', $mitraIds);

        if ($this->search) {
            $mitrasQuery->where('name', 'like', '%' . $this->search . '%');
        }

        $mitras = $mitrasQuery->get();

        // Transformasi menjadi 1 baris percakapan per Mitra (Unique Partner)
        $conversations = $mitras->map(function ($mitra) use ($userId) {
            $lastMessage = ChatModel::where('customer_id', $userId)
                ->where('mitra_id', $mitra->id)
                ->latest('created_at')
                ->first();

            $unreadCount = ChatModel::where('customer_id', $userId)
                ->where('mitra_id', $mitra->id)
                ->where('sender_type', 'mitra')
                ->whereNull('read_at')
                ->count();

            // Cari bantuan aktif atau terakhir antara customer dan mitra ini
            $latestHelp = Help::where('user_id', $userId)
                ->where('mitra_id', $mitra->id)
                ->latest('updated_at')
                ->first();

            return (object) [
                'partner'      => $mitra,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'latest_help'  => $latestHelp,
                'updated_at'   => $lastMessage?->created_at ?? $latestHelp?->updated_at ?? $mitra->updated_at,
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

        $customerId = Auth::id();
        $mitraId    = $this->selected_partner_id;

        return ChatModel::where('customer_id', $customerId)
            ->where('mitra_id', $mitraId)
            ->with('help')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function selectPartner($mitraId, $helpId = null)
    {
        $this->selected_partner_id = (int) $mitraId;
        $this->selected_partner    = User::find($mitraId);
        $this->unassigned_help     = null;

        if ($helpId) {
            $this->active_help_id = (int) $helpId;
            $this->active_help    = Help::find($helpId);
        } else {
            // Dapatkan bantuan aktif atau terbaru antara customer dan mitra ini
            $latestHelp = Help::where('user_id', Auth::id())
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();

            $this->active_help_id = $latestHelp?->id;
            $this->active_help    = $latestHelp;
        }

        // Tandai pesan dari mitra sebagai sudah dibaca
        ChatModel::where('customer_id', Auth::id())
            ->where('mitra_id', $mitraId)
            ->where('sender_type', 'mitra')
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
        $this->unassigned_help     = null;
        $this->message             = '';
    }

    public function sendMessage()
    {
        $this->validate();

        if (!$this->selected_partner_id) {
            $this->dispatch('error', 'Pilih percakapan terlebih dahulu');
            return;
        }

        $customerId = Auth::id();
        $mitraId    = $this->selected_partner_id;

        // Pastikan ada help_id yang valid
        $helpId = $this->active_help_id;
        if (!$helpId) {
            $latestHelp = Help::where('user_id', $customerId)
                ->where('mitra_id', $mitraId)
                ->latest('updated_at')
                ->first();
            $helpId = $latestHelp?->id;
        }

        // Jika tidak ada help_id sama sekali, cari help terakhir manapun antara mereka
        if (!$helpId) {
            $anyChat = ChatModel::where('customer_id', $customerId)
                ->where('mitra_id', $mitraId)
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
            'sender_type' => 'customer',
            'is_read'     => false,
        ]);

        // Kirim notifikasi ke Mitra
        if ($this->selected_partner) {
            try {
                $this->selected_partner->notify(
                    new ChatMessageNotification($helpId, Str::limit($this->message, 150), $customerId, Auth::user()->name)
                );
            } catch (\Throwable $e) {
                \Log::warning('[CustomerChat] Failed to notify mitra: ' . $e->getMessage());
            }
        }

        $this->message = '';
        $this->dispatch('message-sent');
    }

    public function render()
    {
        return view('livewire.customer.chat.index', [
            'conversations' => $this->getConversations(),
            'messages'      => $this->getMessages(),
        ])->layout('layouts.app');
    }
}
