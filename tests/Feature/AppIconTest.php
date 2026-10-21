<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppIconTest extends TestCase
{
    public function test_pages_link_manifest_and_app_icons(): void
    {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertSee('manifest.webmanifest')
            ->assertSee('app-icons/apple-touch-icon.png')
            ->assertSee('app-icons/icon.svg')
            ->assertSee('aria-hidden="true">HP</span>HostPilot', false);
    }

    public function test_manifest_references_existing_icons_for_installation(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('standalone', $manifest['display']);
        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));

        foreach ($manifest['icons'] as $icon) {
            $this->assertStringStartsWith('/app-icons/', $icon['src']);
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }

        $this->assertFileExists(public_path('app-icons/apple-touch-icon.png'));
        $this->assertSame([180, 180], array_slice(getimagesize(public_path('app-icons/apple-touch-icon.png')) ?: [], 0, 2));
    }
}
