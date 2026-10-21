<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecipeRequest;
use App\Models\Event;
use App\Models\Recipe;
use App\Services\RecipeWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Recipes of an event, grouped by course. Index and show share one page:
 * on desktop the selected recipe is shown in the side panel.
 */
class RecipeController extends Controller
{
    public function __construct(private readonly RecipeWriter $writer) {}

    public function index(Event $event): View
    {
        return $this->page($event, null);
    }

    public function show(Event $event, Recipe $recipe): View
    {
        return $this->page($event, $recipe);
    }

    public function create(Event $event): View
    {
        Gate::authorize('update', $event);

        $recipe = new Recipe(['portions' => 4, 'course' => 'Hauptgang']);

        return view('recipes.form', compact('event', 'recipe'));
    }

    public function store(RecipeRequest $request, Event $event): RedirectResponse
    {
        $recipe = $this->writer->create($event, $request->validated(), $request->file('cover'));

        return redirect()->route('events.recipes.show', [$event, $recipe])->with('status', 'Rezept gespeichert.');
    }

    public function edit(Event $event, Recipe $recipe): View
    {
        Gate::authorize('update', $event);

        $recipe->load('ingredients');

        return view('recipes.form', compact('event', 'recipe'));
    }

    public function update(RecipeRequest $request, Event $event, Recipe $recipe): RedirectResponse
    {
        $this->writer->update($recipe, $request->validated(), $request->file('cover'), $request->boolean('remove_cover'));

        return redirect()->route('events.recipes.show', [$event, $recipe])->with('status', 'Rezept gespeichert.');
    }

    public function destroy(Event $event, Recipe $recipe): RedirectResponse
    {
        Gate::authorize('update', $event);

        $this->writer->delete($recipe);

        return redirect()->route('events.recipes.index', $event)->with('status', 'Rezept gelöscht.');
    }

    private function page(Event $event, ?Recipe $selected): View
    {
        Gate::authorize('view', $event);

        $event->load('recipes.ingredients');
        $groups = $event->recipesByCourse();

        $isDetail = $selected !== null;
        $selected = $selected
            ? $event->recipes->firstWhere('id', $selected->id)
            : $groups->first()?->first();

        return view('recipes.index', compact('event', 'groups', 'selected', 'isDetail'));
    }
}
