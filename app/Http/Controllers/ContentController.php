<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Content;
use App\Models\ContentAssignment;
use App\Models\ContentChecklist;
use App\Models\ContentMetric;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentController extends Controller
{
    /**
     * Display a listing of contents (Table View).
     */
    public function index(Request $request): View
    {
        return view('contents.index');
    }

    /**
     * Display the Kanban pipeline view.
     */
    public function kanban(Request $request): View
    {
        return view('contents.kanban');
    }

    /**
     * Show the form for creating a new content.
     */
    public function create(): View|RedirectResponse
    {
        Gate::authorize('create', Content::class);

        $user = Auth::user();
        $channels = Channel::where('user_id', $user->id)->get();

        if ($channels->isEmpty()) {
            return redirect()->route('channels.create')->with('warning', 'Harap buat minimal satu channel media sosial terlebih dahulu sebelum membuat konten.');
        }

        $editors = User::where('role', User::ROLE_EDITOR)->orderBy('name')->get();

        return view('contents.create', compact('channels', 'editors'));
    }

    /**
     * Store a newly created content.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Content::class);

        $user = Auth::user();

        // Validate channel ownership
        $channelRule = Rule::exists('channels', 'id')->where('user_id', $user->id);

        $validated = $request->validate([
            'channel_id' => ['required', 'integer', $channelRule],
            'title' => ['required', 'string', 'max:255'],
            'format_type' => ['required', 'string', Rule::in(Content::ALLOWED_FORMATS)],
            'priority' => ['required', 'string', Rule::in(Content::ALLOWED_PRIORITIES)],
            'scheduled_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'script_draft' => ['nullable', 'string'],
            'editor_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_EDITOR)],
        ]);

        $content = Content::create([
            'user_id' => $user->id,
            'channel_id' => $validated['channel_id'],
            'title' => $validated['title'],
            'format_type' => $validated['format_type'],
            'priority' => $validated['priority'],
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'script_draft' => $validated['script_draft'] ?? null,
            'status' => Content::STATUS_IDEA, // Default MVP starting status
        ]);

        if (! empty($validated['editor_id'])) {
            ContentAssignment::create([
                'content_id' => $content->id,
                'editor_id' => $validated['editor_id'],
                'assigned_by' => $user->id,
                'assigned_at' => now(),
            ]);
        }

        return redirect()->route('contents.show', $content)->with('success', 'Konten "' . $content->title . '" berhasil dibuat dan dimasukkan ke status Idea.');
    }

    /**
     * Display the specified content detail.
     */
    public function show(Content $content): View
    {
        Gate::authorize('view', $content);

        $content->load(['channel', 'user', 'editors', 'checklists', 'metrics']);

        $availableEditors = User::where('role', User::ROLE_EDITOR)
            ->whereNotIn('id', $content->editors->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('contents.show', compact('content', 'availableEditors'));
    }

    /**
     * Show the form for editing the content.
     */
    public function edit(Content $content): View
    {
        Gate::authorize('update', $content);

        $channels = Channel::where('user_id', $content->user_id)->get();

        return view('contents.edit', compact('content', 'channels'));
    }

    /**
     * Update the specified content.
     */
    public function update(Request $request, Content $content): RedirectResponse
    {
        Gate::authorize('update', $content);

        $user = Auth::user();

        // Editor permissions: editors can update title, script_draft, and description
        if ($user->isEditor()) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'script_draft' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ]);

            $content->update($validated);

            return redirect()->route('contents.show', $content)->with('success', 'Judul, naskah, dan deskripsi berhasil diperbarui.');
        }

        // Creator full update
        $channelRule = Rule::exists('channels', 'id')->where('user_id', $content->user_id);

        $validated = $request->validate([
            'channel_id' => ['required', 'integer', $channelRule],
            'title' => ['required', 'string', 'max:255'],
            'format_type' => ['required', 'string', Rule::in(Content::ALLOWED_FORMATS)],
            'priority' => ['required', 'string', Rule::in(Content::ALLOWED_PRIORITIES)],
            'scheduled_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'script_draft' => ['nullable', 'string'],
        ]);

        $content->update($validated);

        return redirect()->route('contents.show', $content)->with('success', 'Informasi konten berhasil diperbarui.');
    }

    /**
     * Remove the specified content.
     */
    public function destroy(Content $content): RedirectResponse
    {
        Gate::authorize('delete', $content);

        $title = $content->title;
        $content->delete();

        return redirect()->route('contents.index')->with('success', 'Konten "' . $title . '" berhasil dihapus.');
    }

    /**
     * Transition content status.
     */
    public function updateStatus(Request $request, Content $content): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return back()->with('error', 'Sebagai Admin, Anda hanya dapat memonitor proses produksi dan tidak dapat mengubah status konten.');
        }
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(Content::ALLOWED_STATUSES)],
        ]);

        $targetStatus = $validated['status'];
        $user = Auth::user();

        if ($user->isCreator() && $targetStatus === Content::STATUS_READY && $content->editors()->exists()) {
            return back()->with('error', 'Konten ini sedang ditugaskan kepada Editor. Tahap Ready harus diselesaikan oleh Editor terlebih dahulu.');
        }

        if (! Gate::allows('updateStatus', [$content, $targetStatus])) {
            return back()->with('error', 'Perubahan status ke "' . ucfirst($targetStatus) . '" tidak diizinkan sesuai aturan transisi.');
        }

        // Check if moving to ready while checklist is incomplete
        $warning = null;
        if ($targetStatus === Content::STATUS_READY) {
            $incompleteChecklists = $content->checklists()->where('is_completed', false)->count();
            if ($incompleteChecklists > 0) {
                $warning = 'Perhatian: Terdapat ' . $incompleteChecklists . ' tugas checklist yang belum selesai.';
            }
        }

        // Target status published requires publication data
        if ($targetStatus === Content::STATUS_PUBLISHED && (empty($content->published_url) || empty($content->published_date))) {
            return back()->with('error', 'Status tidak dapat diubah menjadi Published tanpa URL publikasi dan Tanggal publikasi yang sah.');
        }

        $content->status = $targetStatus;
        $content->save();

        $response = redirect()->route('contents.show', $content)->with('success', 'Status konten berhasil diubah menjadi ' . ucfirst($targetStatus) . '.');

        if ($warning) {
            $response->with('warning', $warning);
        }

        return $response;
    }

    /**
     * Publish content with manual URL and date.
     */
    public function publish(Request $request, Content $content): RedirectResponse
    {
        Gate::authorize('managePublication', $content);

        if (! $content->canTransitionTo(Content::STATUS_PUBLISHED) && $content->status !== Content::STATUS_PUBLISHED) {
            return back()->with('error', 'Konten tidak dapat dipublikasikan dari status saat ini (' . ucfirst($content->status) . ').');
        }

        $validated = $request->validate([
            'published_url' => ['required', 'url', 'max:2048'],
            'published_date' => ['required', 'date'],
        ]);

        $content->update([
            'published_url' => $validated['published_url'],
            'published_date' => $validated['published_date'],
            'status' => Content::STATUS_PUBLISHED,
        ]);

        return redirect()->route('contents.show', $content)->with('success', 'Konten berhasil dipublikasikan!');
    }

    /**
     * Assign editor to content.
     */
    public function assignEditor(Request $request, Content $content): RedirectResponse
    {
        Gate::authorize('assignEditor', $content);

        $validated = $request->validate([
            'editor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', User::ROLE_EDITOR),
            ],
        ]);

        $alreadyAssigned = ContentAssignment::where('content_id', $content->id)
            ->where('editor_id', $validated['editor_id'])
            ->exists();

        if ($alreadyAssigned) {
            return back()->with('error', 'Editor tersebut sudah ditugaskan pada konten ini.');
        }

        ContentAssignment::create([
            'content_id' => $content->id,
            'editor_id' => $validated['editor_id'],
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ]);

        $editor = User::find($validated['editor_id']);

        return redirect()->route('contents.show', $content)->with('success', 'Editor ' . $editor->name . ' berhasil ditugaskan.');
    }

    /**
     * Remove editor assignment.
     */
    public function removeEditor(Content $content, User $editor): RedirectResponse
    {
        Gate::authorize('assignEditor', $content);

        ContentAssignment::where('content_id', $content->id)
            ->where('editor_id', $editor->id)
            ->delete();

        return redirect()->route('contents.show', $content)->with('success', 'Penugasan editor ' . $editor->name . ' telah dicabut.');
    }

    /**
     * Store a checklist item.
     */
    public function storeChecklist(Request $request, Content $content): RedirectResponse
    {
        Gate::authorize('manageChecklist', $content);

        $validated = $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
        ]);

        ContentChecklist::create([
            'content_id' => $content->id,
            'task_name' => $validated['task_name'],
            'is_completed' => false,
        ]);

        return redirect()->route('contents.show', $content)->with('success', 'Tugas checklist berhasil ditambahkan.');
    }

    /**
     * Toggle checklist completion.
     */
    public function toggleChecklist(Request $request, ContentChecklist $checklist): RedirectResponse|JsonResponse
    {
        Gate::authorize('manageChecklist', $checklist->content);

        $checklist->is_completed = ! $checklist->is_completed;
        $checklist->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $checklist->is_completed,
                'progress' => $checklist->content->checklist_percentage,
            ]);
        }

        return back()->with('success', 'Status checklist berhasil diperbarui.');
    }

    /**
     * Delete checklist item.
     */
    public function destroyChecklist(ContentChecklist $checklist): RedirectResponse
    {
        Gate::authorize('manageChecklist', $checklist->content);

        $checklist->delete();

        return back()->with('success', 'Item checklist berhasil dihapus.');
    }

    /**
     * Store or update manual content metrics.
     */
    public function storeMetric(Request $request, Content $content): RedirectResponse
    {
        Gate::authorize('manageMetrics', $content);

        if ($content->status !== Content::STATUS_PUBLISHED) {
            return back()->with('error', 'Metrik hanya dapat dicatat untuk konten yang sudah terbit (Published).');
        }

        $validated = $request->validate([
            'recorded_at' => ['required', 'date'],
            'views_count' => ['required', 'integer', 'min:0'],
            'likes_count' => ['required', 'integer', 'min:0'],
            'comments_count' => ['required', 'integer', 'min:0'],
            'shares_count' => ['required', 'integer', 'min:0'],
        ]);

        ContentMetric::updateOrCreate(
            [
                'content_id' => $content->id,
                'recorded_at' => $validated['recorded_at'],
            ],
            [
                'views_count' => $validated['views_count'],
                'likes_count' => $validated['likes_count'],
                'comments_count' => $validated['comments_count'],
                'shares_count' => $validated['shares_count'],
            ]
        );

        return redirect()->route('contents.show', $content)->with('success', 'Catatan metrik performa berhasil disimpan.');
    }
}
