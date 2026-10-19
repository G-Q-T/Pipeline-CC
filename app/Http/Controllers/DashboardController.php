<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Content;
use App\Models\ContentMetric;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the role-based dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isEditor()) {
            return $this->editorDashboard($user);
        }

        return $this->creatorDashboard($user);
    }

    /**
     * Creator Dashboard data (PRD Section 17.1).
     */
    private function creatorDashboard(User $user): View
    {
        $contentsQuery = Content::where('user_id', $user->id);

        $totalContents = (clone $contentsQuery)->count();
        $totalChannels = Channel::where('user_id', $user->id)->count();

        // Count per status
        $statusCounts = [
            'idea' => (clone $contentsQuery)->where('status', Content::STATUS_IDEA)->count(),
            'scripting' => (clone $contentsQuery)->where('status', Content::STATUS_SCRIPTING)->count(),
            'recording' => (clone $contentsQuery)->where('status', Content::STATUS_RECORDING)->count(),
            'editing' => (clone $contentsQuery)->where('status', Content::STATUS_EDITING)->count(),
            'ready' => (clone $contentsQuery)->where('status', Content::STATUS_READY)->count(),
            'published' => (clone $contentsQuery)->where('status', Content::STATUS_PUBLISHED)->count(),
        ];

        // Upcoming scheduled contents
        $upcomingContents = (clone $contentsQuery)
            ->whereNotNull('scheduled_date')
            ->where('status', '!=', Content::STATUS_PUBLISHED)
            ->orderBy('scheduled_date', 'asc')
            ->take(5)
            ->with(['channel', 'editors'])
            ->get();

        // Recent contents
        $recentContents = (clone $contentsQuery)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->with(['channel', 'checklists'])
            ->get();

        // Aggregated performance metrics for published contents
        $publishedContentIds = (clone $contentsQuery)
            ->where('status', Content::STATUS_PUBLISHED)
            ->pluck('id');

        $metricsSummary = ContentMetric::whereIn('content_id', $publishedContentIds)
            ->selectRaw('SUM(views_count) as total_views, SUM(likes_count) as total_likes, SUM(comments_count) as total_comments, SUM(shares_count) as total_shares')
            ->first();

        return view('dashboard.creator', compact(
            'user',
            'totalContents',
            'totalChannels',
            'statusCounts',
            'upcomingContents',
            'recentContents',
            'metricsSummary'
        ));
    }

    /**
     * Editor Dashboard data (PRD Section 17.2).
     */
    private function editorDashboard(User $user): View
    {
        $assignedContentsQuery = $user->assignedContents();

        $totalAssigned = (clone $assignedContentsQuery)->count();

        $activeAssignments = (clone $assignedContentsQuery)
            ->where('status', '!=', Content::STATUS_PUBLISHED)
            ->with(['channel', 'user', 'checklists'])
            ->orderBy('scheduled_date', 'asc')
            ->get();

        $upcomingDeadlines = (clone $assignedContentsQuery)
            ->whereNotNull('scheduled_date')
            ->where('status', '!=', Content::STATUS_PUBLISHED)
            ->orderBy('scheduled_date', 'asc')
            ->take(5)
            ->with(['channel'])
            ->get();

        return view('dashboard.editor', compact(
            'user',
            'totalAssigned',
            'activeAssignments',
            'upcomingDeadlines'
        ));
    }

    /**
     * Admin Dashboard data (PRD Section 17.3).
     */
    private function adminDashboard(): View
    {
        $totalUsers = User::count();
        $totalCreators = User::where('role', User::ROLE_CREATOR)->count();
        $totalEditors = User::where('role', User::ROLE_EDITOR)->count();
        $totalAdmins = User::where('role', User::ROLE_ADMIN)->count();

        $totalContents = Content::count();
        $totalChannels = Channel::count();

        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentContents = Content::with(['user', 'channel'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('dashboard.admin', compact(
            'totalUsers',
            'totalCreators',
            'totalEditors',
            'totalAdmins',
            'totalContents',
            'totalChannels',
            'recentUsers',
            'recentContents'
        ));
    }
}
