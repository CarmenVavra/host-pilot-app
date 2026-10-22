<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\LegalInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, string|null>  $overrides
     */
    private function configureLegal(array $overrides = []): void
    {
        config(['legal' => array_merge([
            'country' => 'AT',
            'name' => 'Carmen Muster',
            'street' => 'Musterweg 1',
            'city' => '1010 Wien',
            'email' => 'kontakt@example.com',
            'phone' => null,
            'vat_id' => null,
            'host' => 'netcup GmbH, Daumstraße 16, 76185 Karlsruhe, Deutschland',
        ], $overrides)]);
    }

    public function test_legal_pages_are_public(): void
    {
        $this->configureLegal();

        $this->get(route('legal.imprint'))->assertStatus(200)->assertSee('Impressum');
        $this->get(route('legal.privacy'))->assertStatus(200)->assertSee('Datenschutzerklärung');
    }

    public function test_imprint_shows_configured_provider_details(): void
    {
        $this->configureLegal(['phone' => '+43 1 234567', 'vat_id' => 'ATU12345678']);

        $this->get(route('legal.imprint'))
            ->assertSee('Carmen Muster')
            ->assertSee('Musterweg 1')
            ->assertSee('1010 Wien')
            ->assertSee('mailto:kontakt@example.com', false)
            ->assertSee('+43 1 234567')
            ->assertSee('ATU12345678')
            ->assertDontSee('Platzhalter ausfüllen');
    }

    public function test_missing_details_are_shown_as_placeholders_with_a_warning(): void
    {
        $this->configureLegal(['name' => null, 'email' => '']);

        $this->get(route('legal.imprint'))
            ->assertSee('Platzhalter ausfüllen')
            ->assertSee('[LEGAL_NAME eintragen]')
            ->assertSee('[LEGAL_EMAIL eintragen]')
            ->assertSee('LEGAL_NAME, LEGAL_EMAIL');

        $this->get(route('legal.privacy'))->assertSee('Platzhalter ausfüllen');
    }

    public function test_legal_basis_depends_on_the_country(): void
    {
        $this->configureLegal(['country' => 'AT']);
        $this->get(route('legal.imprint'))->assertSee('E-Commerce-Gesetz')->assertSee('Österreich');
        $this->get(route('legal.privacy'))->assertSee('dsb.gv.at');

        $this->configureLegal(['country' => 'DE']);
        $this->get(route('legal.imprint'))->assertSee('Digitale-Dienste-Gesetz')->assertSee('Deutschland');
        $this->get(route('legal.privacy'))->assertSee('bfdi.bund.de');

        $this->configureLegal(['country' => 'XX']);
        $this->get(route('legal.imprint'))->assertSee('E-Commerce-Gesetz');
    }

    public function test_privacy_policy_describes_the_actual_data_processing(): void
    {
        $this->configureLegal();

        $this->get(route('legal.privacy'))
            ->assertSee('technisch notwendige Cookies')
            ->assertSee('Sitzungs-Cookie')
            ->assertSee('Konto löschen')
            ->assertSee('netcup GmbH')
            ->assertSee('Auftragsverarbeitung')
            ->assertSee('keine Verbindung zu Google');
    }

    public function test_footer_links_are_on_guest_and_app_pages(): void
    {
        $this->configureLegal();
        $links = [route('legal.imprint'), route('legal.privacy')];

        foreach ([route('login'), route('register'), route('password.request'), route('legal.imprint')] as $url) {
            $response = $this->get($url)->assertStatus(200);

            foreach ($links as $link) {
                $response->assertSee('href="'.$link.'"', false);
            }
        }

        $this->actingAs(User::factory()->create())->get(route('events.index'))
            ->assertSee('href="'.$links[0].'"', false)
            ->assertSee('href="'.$links[1].'"', false)
            ->assertSee('aria-label="Rechtliches"', false);
    }

    public function test_legal_pages_link_back_depending_on_login_state(): void
    {
        $this->configureLegal();

        $this->get(route('legal.imprint'))->assertSee('Zur Anmeldung');

        $this->actingAs(User::factory()->create())->get(route('legal.imprint'))->assertSee('Zurück zur App');
    }

    public function test_registration_page_points_to_the_privacy_policy(): void
    {
        $this->get(route('register'))
            ->assertStatus(200)
            ->assertSee('Mit der Registrierung bestätigst du')
            ->assertSee('href="'.route('legal.privacy').'"', false);
    }

    public function test_pages_load_no_resources_from_google(): void
    {
        $this->get(route('login'))
            ->assertDontSee('fonts.googleapis.com')
            ->assertDontSee('fonts.gstatic.com');
    }

    public function test_legal_info_reports_missing_required_fields(): void
    {
        $info = new LegalInfo(['country' => 'de', 'name' => 'A', 'street' => '', 'city' => 'B', 'email' => null]);

        $this->assertSame(['street', 'email'], $info->missing());
        $this->assertFalse($info->isComplete());
        $this->assertSame('DE', $info->country());
        $this->assertSame('Deutschland', $info->countryName());
        $this->assertSame('[LEGAL_STREET eintragen]', $info->get('street'));
    }
}
