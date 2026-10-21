<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicRequest;
use App\Models\Event;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Topics (Themen) to discuss with the guests. Create and edit share the
 * topics page: on desktop the form is the side panel, on phones it is
 * shown on its own.
 */
class TopicController extends Controller
{
    public function index(Event $event): View
    {
        return $this->page($event, null, false);
    }

    public function create(Event $event): View
    {
        return $this->page($event, null, true);
    }

    public function store(TopicRequest $request, Event $event): RedirectResponse
    {
        $event->topics()->create($request->validated());

        return redirect()->route('events.topics.index', $event)->with('status', 'Thema hinzugefügt.');
    }

    public function edit(Event $event, Topic $topic): View
    {
        return $this->page($event, $topic, true);
    }

    public function update(TopicRequest $request, Event $event, Topic $topic): RedirectResponse
    {
        $topic->update($request->validated());

        return redirect()->route('events.topics.index', $event)->with('status', 'Thema gespeichert.');
    }

    public function destroy(Event $event, Topic $topic): RedirectResponse
    {
        Gate::authorize('update', $event);

        $topic->delete();

        return redirect()->route('events.topics.index', $event)->with('status', 'Thema gelöscht.');
    }

    private function page(Event $event, ?Topic $editing, bool $formOpen): View
    {
        Gate::authorize('view', $event);

        $event->load('topics');

        return view('topics.index', compact('event', 'editing', 'formOpen'));
    }
}
