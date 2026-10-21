<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    /**
     * Determine whether the user can view any contents.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the content.
     */
    public function view(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCreator() && $content->user_id === $user->id) {
            return true;
        }

        if ($user->isEditor()) {
            return $content->editors()->where('editor_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create contents.
     */
    public function create(User $user): bool
    {
        return $user->isCreator();
    }

    /**
     * Determine whether the user can update the content.
     */
    public function update(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isCreator() && $content->user_id === $user->id) {
            return true;
        }

        if ($user->isEditor()) {
            return $content->editors()->where('editor_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the content.
     */
    public function delete(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        // Only creator owner can delete content
        return $user->isCreator() && $content->user_id === $user->id;
    }

    /**
     * Determine whether the user can assign editors to the content.
     */
    public function assignEditor(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $user->isCreator() && $content->user_id === $user->id;
    }

    /**
     * Determine whether the user can manage checklists.
     */
    public function manageChecklist(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $this->update($user, $content);
    }

    /**
     * Determine whether the user can update publication data (url, published_date).
     */
    public function managePublication(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $user->isCreator() && $content->user_id === $user->id;
    }

    /**
     * Determine whether the user can record or manage metrics.
     */
    public function manageMetrics(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $user->isCreator() && $content->user_id === $user->id;
    }

    /**
     * Determine whether the user can transition the content status.
     * Editor can only advance assigned content to 'ready' upon completion.
     * Creator cannot advance to 'ready' if editors are assigned to work on it (editor must complete it first).
     * Publication is restricted strictly to creator.
     * Admin is strictly restricted to monitoring only.
     */
    public function updateStatus(User $user, Content $content, string $targetStatus): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if (! $this->update($user, $content)) {
            return false;
        }

        // Editor is strictly limited to completing their work to 'ready'
        if ($user->isEditor() && $targetStatus !== Content::STATUS_READY) {
            return false;
        }

        // Creator cannot advance to 'ready' if the content has assigned editor(s);
        // the assigned editor must do the work and advance it to 'ready'.
        if ($user->isCreator() && $targetStatus === Content::STATUS_READY && $content->editors()->exists()) {
            return false;
        }

        // Validate transition path
        if (! $content->canTransitionTo($targetStatus)) {
            return false;
        }

        // Publishing requires publication rights (creator)
        if ($targetStatus === Content::STATUS_PUBLISHED) {
            return $this->managePublication($user, $content);
        }

        return true;
    }
}
