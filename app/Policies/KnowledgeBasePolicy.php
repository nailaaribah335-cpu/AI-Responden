<?php

namespace App\Policies;

use App\Models\KnowledgeBase;
use App\Models\User;

class KnowledgeBasePolicy
{
    /**
     * Pastikan Seller hanya bisa mengedit/menghapus Knowledge Base miliknya sendiri.
     */
    public function update(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $knowledgeBase->user_id === $user->id;
    }

    public function delete(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $knowledgeBase->user_id === $user->id;
    }
}
