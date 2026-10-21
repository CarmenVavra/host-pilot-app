<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

/**
 * Events (and everything nested below them) are only accessible to their host.
 */
class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        return $event->user_id === $user->id;
    }

    public function update(User $user, Event $event): bool
    {
        return $event->user_id === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $event->user_id === $user->id;
    }
}
