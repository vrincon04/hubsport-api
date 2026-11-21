<?php

namespace Tests\Feature\Demo;

use Tests\TestCase;

class DemoPicsumSeedsTest extends TestCase
{
    public function test_demo_seeders_include_picsum_license_free_urls(): void
    {
        $full = (string) file_get_contents(database_path('seeders/DemoFullShowcaseSeeder.php'));
        $profile = (string) file_get_contents(database_path('seeders/DemoShowcaseProfileSeeder.php'));
        $content = (string) file_get_contents(database_path('seeders/DemoContentSeeder.php'));

        $this->assertStringContainsString('picsum.photos/seed/', $full);
        $this->assertStringContainsString('picsum.photos/seed/', $profile);
        $this->assertStringContainsString('picsum.photos/seed/', $content);
    }
}
