<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Recipe;
use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->event = Event::factory()->for($this->user)->create(['guests' => 12]);

        $shakshuka = Recipe::factory()->for($this->event)->create(['title' => 'Shakshuka', 'portions' => 12]);
        $shakshuka->ingredients()->createMany([
            ['name' => 'Eier', 'amount' => 12, 'unit' => 'Stk', 'category' => 'Milch & Eier'],
            ['name' => 'Pelati', 'amount' => 1600, 'unit' => 'g', 'category' => 'Gewürze & Vorrat'],
        ]);

        $ruehrei = Recipe::factory()->for($this->event)->create(['title' => 'Rührei', 'portions' => 6]);
        $ruehrei->ingredients()->createMany([
            ['name' => 'Eier', 'amount' => 12, 'unit' => 'Stk', 'category' => 'Milch & Eier'],
            ['name' => 'Butter', 'amount' => 0.05, 'unit' => 'kg', 'category' => 'Milch & Eier'],
        ]);
    }

    private function item(string $name): ShoppingItem
    {
        return $this->event->shoppingItems()->where('name', $name)->firstOrFail();
    }

    public function test_generation_sums_and_scales_ingredients(): void
    {
        $this->actingAs($this->user)->post(route('events.shopping.generate', $this->event))
            ->assertRedirect(route('events.shopping.index', $this->event))
            ->assertSessionHas('status', '3 Zutaten aus 2 Rezepten für 12 Gäste');

        // 12 (Shakshuka, 12 portions) + 12 × 12/6 (Rührei) = 36 eggs
        $this->assertSame(36.0, $this->item('Eier')->amount);
        // 0.05 kg × 2 = 100 g, normalised to grams
        $this->assertSame(100.0, $this->item('Butter')->amount);
        $this->assertSame('g', $this->item('Butter')->unit);
        $this->assertSame(3, $this->event->shoppingItems()->count());
    }

    public function test_regeneration_keeps_manual_items_and_check_marks(): void
    {
        $this->actingAs($this->user)->post(route('events.shopping.generate', $this->event));
        $this->item('Eier')->update(['done' => true]);
        ShoppingItem::factory()->for($this->event)->create(['name' => 'Servietten']);

        $this->actingAs($this->user)->post(route('events.shopping.generate', $this->event));

        $this->assertTrue($this->item('Eier')->done);
        $this->assertSame(36.0, $this->item('Eier')->amount);
        $this->assertSame(ShoppingItem::SOURCE_MANUAL, $this->item('Servietten')->source);
        $this->assertSame(4, $this->event->shoppingItems()->count());
    }

    public function test_removed_ingredients_disappear_unless_already_checked(): void
    {
        $this->actingAs($this->user)->post(route('events.shopping.generate', $this->event));
        $this->item('Butter')->update(['done' => true]);

        Recipe::where('title', 'Shakshuka')->firstOrFail()->ingredients()->where('name', 'Pelati')->delete();
        Recipe::where('title', 'Rührei')->firstOrFail()->ingredients()->where('name', 'Butter')->delete();

        $this->actingAs($this->user)->post(route('events.shopping.generate', $this->event));

        $this->assertDatabaseMissing('shopping_items', ['name' => 'Pelati']);
        $this->assertDatabaseHas('shopping_items', ['name' => 'Butter', 'done' => true]);
    }

    public function test_list_shows_categories_progress_and_formatted_quantities(): void
    {
        $this->actingAs($this->user)->post(route('events.shopping.generate', $this->event));
        $this->item('Eier')->update(['done' => true]);

        $this->actingAs($this->user)->get(route('events.shopping.index', $this->event))
            ->assertStatus(200)
            ->assertSeeInOrder(['Milch &amp; Eier', 'Butter', 'Eier', 'Gewürze &amp; Vorrat', 'Pelati'], false)
            ->assertSee('36 Stk')
            ->assertSee('1.6 kg')
            ->assertSee('100 g')
            ->assertSee('Aus Rezepten generieren')
            ->assertSee('1/2');
    }

    public function test_item_can_be_toggled_via_json_and_form(): void
    {
        $item = ShoppingItem::factory()->for($this->event)->create(['done' => false]);

        $this->actingAs($this->user)
            ->patchJson(route('events.shopping.toggle', [$this->event, $item]))
            ->assertOk()
            ->assertJson(['id' => $item->id, 'done' => true]);

        $this->actingAs($this->user)
            ->patch(route('events.shopping.toggle', [$this->event, $item]))
            ->assertRedirect(route('events.shopping.index', $this->event));

        $this->assertFalse($item->fresh()->done);
    }

    public function test_manual_item_can_be_added_and_deleted(): void
    {
        $this->actingAs($this->user)->post(route('events.shopping.store', $this->event), [
            'name' => 'Servietten',
            'amount' => '2',
            'unit' => 'Pck.',
            'category' => 'Sonstiges',
        ])->assertRedirect(route('events.shopping.index', $this->event));

        $item = $this->item('Servietten');
        $this->assertSame(ShoppingItem::SOURCE_MANUAL, $item->source);
        $this->assertSame('2 Pck.', $item->quantity_label);

        $this->actingAs($this->user)->delete(route('events.shopping.destroy', [$this->event, $item]))
            ->assertRedirect(route('events.shopping.index', $this->event));

        $this->assertModelMissing($item);
    }

    public function test_manual_item_validation(): void
    {
        $this->actingAs($this->user)->post(route('events.shopping.store', $this->event), [
            'name' => '',
            'category' => 'Weltall',
        ])->assertSessionHasErrors(['name', 'category']);
    }

    public function test_changing_guests_rescales_generated_list(): void
    {
        $this->actingAs($this->user)->post(route('events.shopping.generate', $this->event));

        $this->actingAs($this->user)->patch(route('events.guests.update', $this->event), ['guests' => 6])
            ->assertRedirect(route('events.shopping.index', $this->event));

        $this->assertSame(6, $this->event->fresh()->guests);
        // 12 × 6/12 + 12 × 6/6 = 18 eggs
        $this->assertSame(18.0, $this->item('Eier')->amount);
    }

    public function test_foreign_hosts_cannot_change_the_list(): void
    {
        $other = User::factory()->create();
        $item = ShoppingItem::factory()->for($this->event)->create();

        $this->actingAs($other)->post(route('events.shopping.generate', $this->event))->assertForbidden();
        $this->actingAs($other)->patchJson(route('events.shopping.toggle', [$this->event, $item]))->assertForbidden();
        $this->actingAs($other)->patch(route('events.guests.update', $this->event), ['guests' => 2])->assertForbidden();

        $this->assertFalse($item->fresh()->done);
    }
}
