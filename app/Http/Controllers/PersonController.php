<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonRequest;
use App\Models\Event;
use App\Services\EventPlanner;
use Illuminate\Http\RedirectResponse;

class PersonController extends Controller
{
    public function store(PersonRequest $request, Event $event, EventPlanner $planner): RedirectResponse
    {
        $person = $planner->addPerson($event, $request->validated('name'));

        return back()->with('status', "{$person->name} hinzugefügt.");
    }
}
