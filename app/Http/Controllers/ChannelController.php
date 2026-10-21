<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChannelController extends Controller
{
    /**
     * Display a listing of channels.
     */
    public function index(): View
    {
        $user = Auth::user();

        $channels = $user->isAdmin()
            ? Channel::with('user')->withCount('contents')->latest()->get()
            : Channel::where('user_id', $user->id)->withCount('contents')->latest()->get();

        return view('channels.index', compact('channels'));
    }

    /**
     * Show the form for creating a new channel.
     */
    public function create(): View
    {
        Gate::authorize('create', Channel::class);

        return view('channels.create');
    }

    /**
     * Store a newly created channel.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Channel::class);

        $validated = $request->validate([
            'platform' => ['required', 'string', Rule::in(Channel::ALLOWED_PLATFORMS)],
            'handle_name' => ['required', 'string', 'max:100'],
        ]);

        $channel = Channel::create([
            'user_id' => Auth::id(),
            'platform' => $validated['platform'],
            'handle_name' => $validated['handle_name'],
        ]);

        return redirect()->route('channels.index')->with('success', 'Channel ' . $channel->handle_name . ' berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified channel.
     */
    public function edit(Channel $channel): View
    {
        Gate::authorize('view', $channel);

        $channel->load('user')->loadCount('contents');

        return view('channels.edit', compact('channel'));
    }

    /**
     * Update the specified channel.
     */
    public function update(Request $request, Channel $channel): RedirectResponse
    {
        Gate::authorize('update', $channel);

        $validated = $request->validate([
            'platform' => ['required', 'string', Rule::in(Channel::ALLOWED_PLATFORMS)],
            'handle_name' => ['required', 'string', 'max:100'],
        ]);

        $channel->update($validated);

        return redirect()->route('channels.index')->with('success', 'Channel ' . $channel->handle_name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified channel and its contents.
     */
    public function destroy(Channel $channel): RedirectResponse
    {
        Gate::authorize('delete', $channel);

        $channelName = $channel->handle_name;
        $contentCount = $channel->contents()->count();

        DB::transaction(function () use ($channel) {
            // Delete associated contents first (triggering cascades for checklists, metrics, assignments)
            foreach ($channel->contents as $content) {
                $content->delete();
            }

            $channel->delete();
        });

        $message = $contentCount > 0
            ? "Channel {$channelName} beserta {$contentCount} konten di dalamnya berhasil dihapus."
            : "Channel {$channelName} berhasil dihapus.";

        return redirect()->route('channels.index')->with('success', $message);
    }
}
