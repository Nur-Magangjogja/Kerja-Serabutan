<?php

namespace App\Livewire\Mitra;

use App\Models\Chat as ChatModel;
use App\Models\Help;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ChatMessageNotification;
use Illuminate\Support\Str;

class Chat extends Component
{
    use WithPagination;

    public $selected_help_id = null;
    public $message = '';
    public $search = '';

    protected $rules = [
        'message' => 'required|string|max:1000',
    ];

    public function mount($help = null)
    {
        $this->user = Auth::user();

        if ($help) {
            $this->selected_help_id = $help;
            // Mark customer messages as read
            ChatModel::where('help_id', $help)
                ->where('sender_type', 'customer')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Get all help conversations for the mitra (where they are mitra_id)
    public function getConversations()
    {
        $query = Help::where('mitra_id', Auth::id());

        // Search by customer name or help description
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', '%' . $this->search . '%');
                })
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->with([
            // 'avatar' column doesn't exist; load the user's selfie_photo instead
            'user:id,name,selfie_photo',
            'chatMessages' => function ($q) {
                $q->latest()->limit(1);
            }
        ])
            ->latest('updated_at')
            ->paginate(20);
    }

    // Get all messages for selected help
    public function getMessages()
    {
        if (!$this->selected_help_id) {
            return collect();
        }

        return ChatModel::where('help_id', $this->selected_help_id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Send message
    public function sendMessage()
    {
        // Validate explicitly using the Validator facade and pass an
        // empty messages array to avoid any accidental Collection being
        // interpreted as custom messages by the Validator.
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['message' => $this->message],
            ['message' => 'required|string|max:1000'],
            [] // force empty array for custom messages
        );

        $validator->validate();

        if (!$this->selected_help_id) {
            $this->dispatch('error', 'Pilih percakapan terlebih dahulu');
            return;
        }

        // Get help and customer info
        $help = Help::findOrFail($this->selected_help_id);

        $chat = ChatModel::create([
            'help_id' => $this->selected_help_id,
            'mitra_id' => Auth::id(),
            'customer_id' => $help->user_id,
            'message' => $this->message,
            'sender_type' => 'mitra',
        ]);

        $this->message = '';
        // Dispatch message-sent with helpId so listeners can link to the conversation
        $this->dispatch('message-sent', helpId: $this->selected_help_id);
        // Notify the customer
        $customer = User::find($help->user_id);
        if ($customer) {
            $customer->notify(new ChatMessageNotification($this->selected_help_id, Str::limit($this->message, 150), Auth::id(), optional(Auth::user())->name));
        }
    }

    // Select help for chat
    public function selectHelp($help_id)
    {
        $this->selected_help_id = $help_id;

        // Mark messages as read
        ChatModel::where('help_id', $help_id)
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.mitra.chat.index', [
            'conversations' => $this->getConversations(),
            'messages' => $this->getMessages(),
            'selected_help' => $this->selected_help_id ? Help::find($this->selected_help_id) : null,
        ])->layout('layouts.mitra');
    }
}
