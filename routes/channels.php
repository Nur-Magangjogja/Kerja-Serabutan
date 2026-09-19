<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('mitra.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id && $user->role === 'mitra';
});

Broadcast::channel('chat.help.{helpId}', function ($user, $helpId) {
    $help = \App\Models\Help::find($helpId);
    if (!$help) {
        return false;
    }

    return (int) $user->id === (int) $help->user_id
        || (int) $user->id === (int) $help->mitra_id
        || in_array($user->role, ['admin', 'superadmin', 'super_admin']);
});
