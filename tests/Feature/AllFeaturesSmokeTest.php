<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Placeholder aman: JANGAN pakai RefreshDatabase di sini.
 * Smoke test penuh dijalankan via: php storage/app/smoke_kernel.php
 * (menggunakan DB aplikasi yang ada, tanpa migrate:fresh).
 */
class AllFeaturesSmokeTest extends TestCase
{
    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }
}
