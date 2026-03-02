<?php

namespace App\Livewire\Customer;

use App\Models\Chat as ChatModel;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use App\Models\Help;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

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

        // If help is provided via route parameter, open that conversation immediately.
        if ($help) {
            $this->selected_help_id = $help;
            // mark mitra messages as read for that help
            ChatModel::where('help_id', $help)
                ->where('sender_type', 'mitra')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getConversations()
    {
        $query = Help::where('user_id', Auth::id());

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('mitra', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->with([
            'mitra:id,name,selfie_photo',
            'chatMessages' => function ($q) {
                $q->latest()->limit(1);
            }
        ])->latest('updated_at')->paginate(20);
    }

    public function getMessages()
    {
        if (!$this->selected_help_id) {
            return collect();
        }

        return ChatModel::where('help_id', $this->selected_help_id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage()
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['message' => $this->message],
            ['message' => 'required|string|max:1000'],
            []
        );

        $validator->validate();

        if (!$this->selected_help_id) {
            $this->dispatch('error', 'Pilih percakapan terlebih dahulu');
            return;
        }

        $help = Help::findOrFail($this->selected_help_id);

        $chat = ChatModel::create([
            'help_id' => $this->selected_help_id,
            'mitra_id' => $help->mitra_id,
            'customer_id' => Auth::id(),
            'message' => $this->message,
            'sender_type' => 'customer',
        ]);

        // Notify the mitra (if exists) about the new message
        if ($help->mitra_id) {
            $mitra = User::find($help->mitra_id);
            if ($mitra) {
                $mitra->notify(new ChatMessageNotification($this->selected_help_id, Str::limit($this->message, 150), Auth::id(), optional(Auth::user())->name));
            }
        }

        $this->message = '';
        // Dispatch message-sent with helpId to allow redirecting to specific conversation
        $this->dispatch('message-sent', helpId: $this->selected_help_id);
    }

    public function selectHelp($help_id)
    {
        $this->selected_help_id = $help_id;

        ChatModel::where('help_id', $help_id)
            ->where('sender_type', 'mitra')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.customer.chat.index', [
            'conversations' => $this->getConversations(),
            'messages' => $this->getMessages(),
            'selected_help' => $this->selected_help_id ? Help::find($this->selected_help_id) : null,
        ])->layout('layouts.app');
    }
}
