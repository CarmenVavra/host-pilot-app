<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShoppingItemRequest;
use App\Models\Event;
use App\Models\ShoppingItem;
use App\Services\ShoppingListGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The shopping list (Einkauf) of an event.
 */
class ShoppingItemController extends Controller
{
    public function index(Event $event): View
    {
        Gate::authorize('view', $event);

        $event->load('shoppingItems')->loadCount('recipes');

        $categories = $event->shoppingByCategory();
        $doneCount = $event->shoppingItems->where('done', true)->count();
        $totalCount = $event->shoppingItems->count();

        return view('shopping.index', compact('event', 'categories', 'doneCount', 'totalCount'));
    }

    public function create(Event $event): View
    {
        Gate::authorize('update', $event);

        return view('shopping.create', compact('event'));
    }

    public function store(ShoppingItemRequest $request, Event $event): RedirectResponse
    {
        $event->shoppingItems()->create($request->validated() + [
            'done' => false,
            'source' => ShoppingItem::SOURCE_MANUAL,
        ]);

        return redirect()->route('events.shopping.index', $event)->with('status', 'Eintrag hinzugefügt.');
    }

    /**
     * Toggles «erledigt». Answers JSON for the live list, otherwise redirects.
     */
    public function toggle(Request $request, Event $event, ShoppingItem $shoppingItem): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $event);

        $shoppingItem->update(['done' => ! $shoppingItem->done]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $shoppingItem->id, 'done' => $shoppingItem->done]);
        }

        return redirect()->route('events.shopping.index', $event);
    }

    public function destroy(Event $event, ShoppingItem $shoppingItem): RedirectResponse
    {
        Gate::authorize('update', $event);

        $shoppingItem->delete();

        return redirect()->route('events.shopping.index', $event)->with('status', 'Eintrag gelöscht.');
    }

    /**
     * «Aus Rezepten generieren»
     */
    public function generate(Event $event, ShoppingListGenerator $generator): RedirectResponse
    {
        Gate::authorize('update', $event);

        $result = $generator->generate($event);

        return redirect()->route('events.shopping.index', $event)->with('status', $result->message());
    }
}
