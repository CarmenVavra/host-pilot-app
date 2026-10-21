<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Services\EventPlanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(private readonly EventPlanner $planner) {}

    /**
     * Event overview (dashboard) with «Anstehend» / «Vergangen» filter.
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter') === 'vergangen' ? 'vergangen' : 'anstehend';
        $query = $request->user()->events();

        $events = ($filter === 'vergangen' ? $query->past() : $query->upcoming())->get();
        $upcomingCount = $request->user()->events()->upcoming()->count();

        return view('events.index', compact('events', 'filter', 'upcomingCount'));
    }

    public function create(): View
    {
        $event = new Event(['guests' => 8, 'time' => '18:00']);

        return view('events.create', compact('event'));
    }

    public function store(EventRequest $request): RedirectResponse
    {
        $event = $this->planner->create($request->user(), $request->validated(), $request->file('cover'));

        return redirect()->route('events.show', $event)->with('status', 'Event erstellt.');
    }

    /**
     * Event hub with entry points to agenda, recipes and shopping list.
     */
    public function show(Event $event): View
    {
        Gate::authorize('view', $event);

        $event->load(['agendaItems', 'recipes', 'shoppingItems', 'topics']);

        $nextItem = $event->nextAgendaItem();
        $doneCount = $event->shoppingItems->where('done', true)->count();
        $totalCount = $event->shoppingItems->count();

        return view('events.show', compact('event', 'nextItem', 'doneCount', 'totalCount'));
    }

    public function edit(Event $event): View
    {
        Gate::authorize('update', $event);

        return view('events.edit', compact('event'));
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $this->planner->update($event, $request->validated(), $request->file('cover'), $request->boolean('remove_cover'));

        return redirect()->route('events.show', $event)->with('status', 'Event gespeichert.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        Gate::authorize('delete', $event);

        $this->planner->delete($event);

        return redirect()->route('events.index')->with('status', 'Event gelöscht.');
    }
}
