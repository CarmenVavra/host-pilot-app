<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaItemRequest;
use App\Models\AgendaItem;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The agenda (Ablaufplan) of an event. Create and edit share the agenda page:
 * on desktop the form is the side panel, on phones it is shown on its own.
 */
class AgendaItemController extends Controller
{
    public function index(Event $event): View
    {
        return $this->page($event, null, false);
    }

    public function create(Event $event): View
    {
        return $this->page($event, null, true);
    }

    public function store(AgendaItemRequest $request, Event $event): RedirectResponse
    {
        $event->agendaItems()->create($request->validated());

        return redirect()->route('events.agenda.index', $event)->with('status', 'Programmpunkt hinzugefügt.');
    }

    public function edit(Event $event, AgendaItem $agendaItem): View
    {
        return $this->page($event, $agendaItem, true);
    }

    public function update(AgendaItemRequest $request, Event $event, AgendaItem $agendaItem): RedirectResponse
    {
        $agendaItem->update($request->validated());

        return redirect()->route('events.agenda.index', $event)->with('status', 'Programmpunkt gespeichert.');
    }

    public function destroy(Event $event, AgendaItem $agendaItem): RedirectResponse
    {
        Gate::authorize('update', $event);

        $agendaItem->delete();

        return redirect()->route('events.agenda.index', $event)->with('status', 'Programmpunkt gelöscht.');
    }

    private function page(Event $event, ?AgendaItem $editing, bool $formOpen): View
    {
        Gate::authorize('view', $event);

        $event->load(['agendaItems.person', 'people']);
        $highlightedId = $event->highlightedAgendaItemId();

        // Suggest one hour after the last item for a new entry.
        $last = $event->agendaItems->last();
        $defaultTime = $last
            ? Carbon::createFromFormat('H:i', $last->time)->addHour()->format('H:i')
            : ($event->time ?? '12:00');

        return view('agenda.index', compact('event', 'editing', 'formOpen', 'highlightedId', 'defaultTime'));
    }
}
