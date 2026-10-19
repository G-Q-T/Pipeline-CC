<?php

namespace App\Livewire;

use App\Models\Channel;
use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PipelineKanban extends Component
{
    public string $search = '';
    public string $channelFilter = 'all';
    public string $priorityFilter = 'all';

    public function moveStatus(int $contentId, string $targetStatus): void
    {
        $content = Content::findOrFail($contentId);
        $user = Auth::user();

        // Editor restriction: can only move completed work to Ready
        if ($user->isEditor() && $targetStatus !== Content::STATUS_READY) {
            session()->flash('error', 'Sebagai Editor, Anda hanya memiliki hak memindahkan pekerjaan ke status Ready setelah selesai. Tahap Published hanya untuk Creator.');
            return;
        }

        // Creator restriction: cannot move to Ready if assigned to an editor
        if ($user->isCreator() && $targetStatus === Content::STATUS_READY && $content->editors()->exists()) {
            session()->flash('error', 'Konten ini sedang ditugaskan kepada Editor. Tahap Ready harus diselesaikan oleh Editor terlebih dahulu.');
            return;
        }

        if (! Gate::allows('updateStatus', [$content, $targetStatus])) {
            session()->flash('error', 'Perpindahan ke "' . ucfirst($targetStatus) . '" ditolak sesuai aturan transisi.');
            return;
        }

        if ($targetStatus === Content::STATUS_PUBLISHED && (empty($content->published_url) || empty($content->published_date))) {
            session()->flash('error', 'Status tidak dapat diubah menjadi Published tanpa URL dan Tanggal publikasi. Buka detail konten untuk menerbitkan.');
            return;
        }

        $warning = null;
        if ($targetStatus === Content::STATUS_READY) {
            $incompleteChecklists = $content->checklists()->where('is_completed', false)->count();
            if ($incompleteChecklists > 0) {
                $warning = 'Perhatian: Ada ' . $incompleteChecklists . ' tugas checklist yang belum selesai.';
            }
        }

        $content->status = $targetStatus;
        $content->save();

        session()->flash('success', 'Konten "' . $content->title . '" berhasil dipindahkan ke ' . ucfirst($targetStatus) . '.');
        if ($warning) {
            session()->flash('warning', $warning);
        }
    }

    public function render()
    {
        $user = Auth::user();

        // Base query with role-based scoping
        if ($user->isAdmin()) {
            $query = Content::query();
            $channels = Channel::all();
        } elseif ($user->isEditor()) {
            $query = $user->assignedContents();
            $channels = Channel::whereIn('id', (clone $query)->pluck('channel_id'))->get();
        } else {
            $query = Content::where('user_id', $user->id);
            $channels = Channel::where('user_id', $user->id)->get();
        }

        // Search
        if (! empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Filters
        if ($this->channelFilter !== 'all') {
            $query->where('channel_id', $this->channelFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        $allContents = $query->with(['channel', 'user', 'editors', 'checklists'])
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_date', 'asc')
            ->get();

        // Group by status
        $columns = [
            'idea' => $allContents->where('status', Content::STATUS_IDEA),
            'scripting' => $allContents->where('status', Content::STATUS_SCRIPTING),
            'recording' => $allContents->where('status', Content::STATUS_RECORDING),
            'editing' => $allContents->where('status', Content::STATUS_EDITING),
            'ready' => $allContents->where('status', Content::STATUS_READY),
            'published' => $allContents->where('status', Content::STATUS_PUBLISHED),
        ];

        return view('livewire.pipeline-kanban', [
            'columns' => $columns,
            'channels' => $channels,
        ]);
    }
}
