<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Deletes a user account together with everything that belongs to it
 * (right to erasure, Art. 17 DSGVO): events with agenda, recipes, shopping
 * list, topics, helpers, uploaded cover images, sessions and reset tokens.
 */
class AccountDeleter
{
    public function __construct(private readonly EventPlanner $events) {}

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Goes through EventPlanner so uploaded images are removed from disk as well.
            $user->events()->get()->each(fn ($event) => $this->events->delete($event));

            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            if (config('session.driver') === 'database') {
                DB::table((string) config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }

            $user->delete();
        });
    }
}
