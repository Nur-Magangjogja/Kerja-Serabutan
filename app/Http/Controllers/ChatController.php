<?php

namespace App\Http\Controllers;

use App\Models\Help;
use Illuminate\Http\Request;

class ChatController
{
    /**
     * Redirect to the appropriate Livewire chat route for the given help/conversation id.
     */
    public function show(Request $request, $helpId)
    {
        $help = Help::findOrFail($helpId);

        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Only allow participants (help owner or assigned mitra) or allow mitra/customer to open
        if ($help->user_id !== $user->id && $help->mitra_id !== $user->id) {
            // Not a participant: prevent access
            abort(403, 'Unauthorized to access this conversation');
        }

        // Redirect to the correct prefixed chat route and include query string to open detail
        if ($user->role === 'mitra') {
            return redirect()->route('mitra.chat', ['help' => $help->id]);
        }

        // Default: customer
        return redirect()->route('customer.chat', ['help' => $help->id]);
    }

    /**
     * Start or find a conversation between the current user and a target user.
     * Expects query params: help_id (optional) or user_id (target user).
     * If help_id provided -> redirect to /chat/{help_id}.
     * If user_id provided -> try to find existing help between users; if not found, redirect to help creation page.
     */
    public function start(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $helpId = $request->query('help_id');
        $targetUserId = $request->query('user_id');

        if ($helpId) {
            return redirect()->route('chat.show', ['help' => $helpId]);
        }

        if (!$targetUserId) {
            return redirect()->back();
        }

        // Try to find existing help where one is mitra and the other is customer
        $found = Help::where(function ($q) use ($user, $targetUserId) {
            $q->where('user_id', $user->id)->where('mitra_id', $targetUserId);
        })->orWhere(function ($q) use ($user, $targetUserId) {
            $q->where('user_id', $targetUserId)->where('mitra_id', $user->id);
        })->first();

        if ($found) {
            return redirect()->route('chat.show', ['help' => $found->id]);
        }

        // No existing help => redirect to help creation (customer) page with a flash message.
        // If current user is mitra, redirect to customer's helps listing? Simpler: redirect to help creation.
        return redirect()->route('customer.helps.create')->with('status', 'Belum ada percakapan. Silakan buat permintaan bantuan untuk memulai chat.');
    }
}
