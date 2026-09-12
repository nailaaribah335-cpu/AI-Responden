<?php

namespace App\Policies;

use App\Models\ConnectedStore;
use App\Models\User;

/**
 * Policy untuk ConnectedStore — memastikan seller hanya bisa
 * mengelola toko miliknya sendiri, bukan toko seller lain.
 */
class ConnectedStorePolicy
{
    /** Seller hanya boleh update toko miliknya */
    public function update(User $user, ConnectedStore $store): bool
    {
        return $user->id === $store->user_id;
    }

    /** Seller hanya boleh hapus toko miliknya */
    public function delete(User $user, ConnectedStore $store): bool
    {
        return $user->id === $store->user_id;
    }
}
