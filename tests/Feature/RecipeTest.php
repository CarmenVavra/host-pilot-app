<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->event = Event::factory()->for($this->user)->create(['guests' => 12]);
    }

    public function test_recipes_are_grouped_by_course_in_order_of_the_day(): void
    {
        Recipe::factory()->for($this->event)->create(['title' => 'Zitronen-Tarte', 'course' => 'Nachspeise']);
        Recipe::factory()->for($this->event)->create(['title' => 'Birchermüesli', 'course' => 'Frühstück']);
        Recipe::factory()->for($this->event)->create(['title' => 'Shakshuka', 'course' => 'Brunch']);

        $this->actingAs($this->user)->get(route('events.recipes.index', $this->event))
            ->assertStatus(200)
            ->assertSeeInOrder(['Frühstück', 'Birchermüesli', 'Brunch', 'Shakshuka', 'Nachspeise', 'Zitronen-Tarte'])
            ->assertSee('4 Portionen')
            ->assertDontSee('Hauptgang');
    }

    public function test_recipe_detail_shows_original_amounts_independent_of_guests(): void
    {
        $recipe = Recipe::factory()->for($this->event)->create(['title' => 'Shakshuka', 'course' => 'Brunch', 'portions' => 6]);
        $recipe->ingredients()->createMany([
            ['name' => 'Eier', 'amount' => 6, 'unit' => 'Stk', 'category' => 'Milch & Eier', 'position' => 0],
            ['name' => 'Pelati', 'amount' => 800, 'unit' => 'g', 'category' => 'Gewürze & Vorrat', 'position' => 1],
        ]);

        $this->actingAs($this->user)->get(route('events.recipes.show', [$this->event, $recipe]))
            ->assertStatus(200)
            ->assertSee('für 6 Portionen')
            ->assertSee('6 Stk')
            ->assertSee('800 g')
            ->assertDontSee('12 Stk')
            ->assertDontSee('skaliert');
    }

    public function test_recipe_can_be_created_with_ingredients_and_steps(): void
    {
        $response = $this->actingAs($this->user)->post(route('events.recipes.store', $this->event), [
            'title' => 'Shakshuka',
            'course' => 'Brunch',
            'portions' => 4,
            'duration_minutes' => 35,
            'diet' => 'vegetarisch',
            'steps' => "1. Zwiebeln dünsten.\n\n2) Pelati zugeben.\nEier stocken lassen.",
            'ingredients' => [
                ['name' => 'Eier', 'amount' => '4', 'unit' => 'Stk', 'category' => 'Milch & Eier'],
                ['name' => 'Olivenöl', 'amount' => '1,5', 'unit' => 'EL', 'category' => 'Gewürze & Vorrat'],
                ['name' => '', 'amount' => '', 'unit' => 'Stk', 'category' => 'Sonstiges'],
            ],
        ]);

        $recipe = Recipe::firstOrFail();
        $response->assertRedirect(route('events.recipes.show', [$this->event, $recipe]));

        $this->assertSame(['Zwiebeln dünsten.', 'Pelati zugeben.', 'Eier stocken lassen.'], $recipe->steps);
        $this->assertSame(['Eier', 'Olivenöl'], $recipe->ingredients()->pluck('name')->all());
        $this->assertSame(1.5, $recipe->ingredients()->where('name', 'Olivenöl')->first()->amount);
    }

    public function test_recipe_validation(): void
    {
        $this->actingAs($this->user)->post(route('events.recipes.store', $this->event), [
            'title' => '',
            'course' => 'Mitternachtssnack',
            'portions' => 0,
            'ingredients' => [['name' => 'Eier', 'amount' => 'viele', 'unit' => 'Kiste', 'category' => 'Milch & Eier']],
        ])->assertSessionHasErrors(['title', 'course', 'portions', 'ingredients.0.amount', 'ingredients.0.unit']);

        $this->assertDatabaseCount('recipes', 0);
    }

    public function test_recipe_can_be_updated_and_ingredients_replaced(): void
    {
        $recipe = Recipe::factory()->for($this->event)->create();
        $recipe->ingredients()->create(['name' => 'Alt', 'amount' => 1, 'unit' => 'Stk', 'category' => 'Sonstiges']);

        $this->actingAs($this->user)->get(route('events.recipes.edit', [$this->event, $recipe]))
            ->assertStatus(200)
            ->assertSee('Rezept bearbeiten');

        $this->actingAs($this->user)->put(route('events.recipes.update', [$this->event, $recipe]), [
            'title' => 'Neu',
            'course' => 'Hauptgang',
            'portions' => 8,
            'ingredients' => [['name' => 'Reis', 'amount' => '500', 'unit' => 'g', 'category' => 'Gewürze & Vorrat']],
        ])->assertRedirect(route('events.recipes.show', [$this->event, $recipe]));

        $recipe->refresh();
        $this->assertSame('Neu', $recipe->title);
        $this->assertSame(['Reis'], $recipe->ingredients()->pluck('name')->all());
    }

    public function test_recipe_can_be_deleted(): void
    {
        $recipe = Recipe::factory()->for($this->event)->create();

        $this->actingAs($this->user)->delete(route('events.recipes.destroy', [$this->event, $recipe]))
            ->assertRedirect(route('events.recipes.index', $this->event));

        $this->assertModelMissing($recipe);
    }

    public function test_create_form_renders(): void
    {
        $this->actingAs($this->user)->get(route('events.recipes.create', $this->event))
            ->assertStatus(200)
            ->assertSee('Neues Rezept')
            ->assertSee('Zutat hinzufügen');
    }

    public function test_recipe_of_other_event_returns_404(): void
    {
        $recipe = Recipe::factory()->for(Event::factory()->for($this->user))->create();

        $this->actingAs($this->user)->get(route('events.recipes.show', [$this->event, $recipe]))->assertNotFound();
    }
}
