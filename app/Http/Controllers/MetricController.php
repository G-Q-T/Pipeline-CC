<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Content;
use App\Models\ContentMetric;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MetricController extends Controller
{
    /**
     * Display performance analytics for published contents.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Base query for published contents within user scope
        if ($user->isAdmin()) {
            $query = Content::where('status', Content::STATUS_PUBLISHED);
            $channels = Channel::all();
        } elseif ($user->isEditor()) {
            $query = $user->assignedContents()->where('status', Content::STATUS_PUBLISHED);
            $channels = Channel::whereIn('id', (clone $query)->pluck('channel_id'))->get();
        } else {
            $query = Content::where('user_id', $user->id)->where('status', Content::STATUS_PUBLISHED);
            $channels = Channel::where('user_id', $user->id)->get();
        }

        // Search title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        // Channel filter
        if ($request->filled('channel') && $request->input('channel') !== 'all') {
            $query->where('channel_id', $request->input('channel'));
        }

        $publishedContents = $query->with(['channel', 'user', 'latestMetric', 'metrics'])
            ->orderBy('published_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Aggregate totals based on latest metrics of published contents
        $totalViews = 0;
        $totalLikes = 0;
        $totalComments = 0;
        $totalShares = 0;

        foreach ($publishedContents as $item) {
            if ($item->latestMetric) {
                $totalViews += $item->latestMetric->views_count;
                $totalLikes += $item->latestMetric->likes_count;
                $totalComments += $item->latestMetric->comments_count;
                $totalShares += $item->latestMetric->shares_count;
            }
        }

        return view('metrics.index', compact(
            'publishedContents',
            'channels',
            'totalViews',
            'totalLikes',
            'totalComments',
            'totalShares'
        ));
    }

    /**
     * Store or update content metric with warning on lower cumulative count.
     */
    public function store(Request $request, Content $content): RedirectResponse
    {
        Gate::authorize('manageMetrics', $content);

        if ($content->status !== Content::STATUS_PUBLISHED) {
            return back()->with('error', 'Metrik hanya dapat dicatat untuk konten yang telah berstatus Published.');
        }

        $validated = $request->validate([
            'recorded_at' => ['required', 'date'],
            'views_count' => ['required', 'integer', 'min:0'],
            'likes_count' => ['required', 'integer', 'min:0'],
            'comments_count' => ['required', 'integer', 'min:0'],
            'shares_count' => ['required', 'integer', 'min:0'],
        ]);

        // Check if there is an existing previous metric with higher numbers
        $warning = null;
        $latestExisting = $content->metrics()
            ->where('recorded_at', '<', $validated['recorded_at'])
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($latestExisting && $validated['views_count'] < $latestExisting->views_count) {
            $warning = 'Perhatian: Angka views (' . number_format($validated['views_count']) . ') lebih rendah dari catatan sebelumnya (' . number_format($latestExisting->views_count) . '). Metrik kumulatif platform biasanya bertambah.';
        }

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

        $response = redirect()->route('contents.show', $content)->with('success', 'Catatan metrik berhasil disimpan.');
        if ($warning) {
            $response->with('warning', $warning);
        }

        return $response;
    }

    /**
     * Delete metric record.
     */
    public function destroy(ContentMetric $metric): RedirectResponse
    {
        Gate::authorize('manageMetrics', $metric->content);

        $metric->delete();

        return back()->with('success', 'Catatan metrik berhasil dihapus.');
    }
}
