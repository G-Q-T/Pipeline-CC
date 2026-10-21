<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;

class ChannelPolicy
{
    /**
     * Determine whether the user can view any channels.
     */
    public function viewAny(User $user): bool
    {
        return $user->isCreator() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the channel.
     */
    public function view(User $user, Channel $channel): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCreator() && $channel->user_id === $user->id;
    }

    /**
     * Determine whether the user can create channels.
     * Only creator can create channels, admin cannot.
     */
    public function create(User $user): bool
    {
        return $user->isCreator();
    }

    /**
     * Determine whether the user can update the channel.
     * Only creator owner can update the channel, admin cannot.
     */
    public function update(User $user, Channel $channel): bool
    {
        return $user->isCreator() && $channel->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the channel.
     * Creator owner or Admin can delete the channel.
     */
    public function delete(User $user, Channel $channel): bool
    {
        return $user->isAdmin() || ($user->isCreator() && $channel->user_id === $user->id);
    }
}
