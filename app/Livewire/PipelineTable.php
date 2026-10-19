<?php

namespace App\Livewire;

use App\Models\Channel;
use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class PipelineTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = 'all';
    public string $channelFilter = 'all';
    public string $priorityFilter = 'all';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingChannelFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function changeStatus(int $contentId, string $targetStatus): void
    {
        $content = Content::findOrFail($contentId);
        $user = Auth::user();

        if ($user->isEditor() && $targetStatus !== Content::STATUS_READY) {
            session()->flash('error', 'Sebagai Editor, Anda hanya dapat memindahkan status ke Ready setelah pekerjaan selesai.');
            return;
        }

        if ($user->isCreator() && $targetStatus === Content::STATUS_READY && $content->editors()->exists()) {
            session()->flash('error', 'Konten ini sedang ditugaskan kepada Editor. Tahap Ready harus diselesaikan oleh Editor terlebih dahulu.');
            return;
        }

        if (! Gate::allows('updateStatus', [$content, $targetStatus])) {
            session()->flash('error', 'Perubahan status ke "' . ucfirst($targetStatus) . '" ditolak sesuai aturan transisi.');
            return;
        }

        if ($targetStatus === Content::STATUS_PUBLISHED && (empty($content->published_url) || empty($content->published_date))) {
            session()->flash('error', 'Status tidak dapat diubah menjadi Published tanpa URL dan Tanggal publikasi.');
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

        session()->flash('success', 'Status konten "' . $content->title . '" berhasil diubah ke ' . ucfirst($targetStatus) . '.');
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
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->channelFilter !== 'all') {
            $query->where('channel_id', $this->channelFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        // Sorting
        $contents = $query->with(['channel', 'user', 'editors', 'checklists'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.pipeline-table', [
            'contents' => $contents,
            'channels' => $channels,
        ]);
    }
}
