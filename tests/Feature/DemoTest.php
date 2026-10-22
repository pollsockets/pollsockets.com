<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Sleep;
use Inertia\Testing\AssertableInertia as Assert;
use Pollsockets\Pollsockets;
use Tests\TestCase;

class DemoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Sleep::fake();
    }

    public function test_the_welcome_page_renders_people_and_the_pollsockets_cursor(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('since', fn (int $since) => $since > 0)
                ->has('people', 10, fn (Assert $person) => $person
                    ->hasAll(['name', 'email', 'title', 'role'])
                )
            );
    }

    public function test_the_people_endpoint_sleeps_unless_it_is_optimized(): void
    {
        $this->getJson('/people')
            ->assertOk()
            ->assertJsonCount(10, 'people')
            ->assertJsonStructure(['people', 'size']);

        Sleep::assertSlept(fn ($duration) => $duration->totalSeconds === 3.0, times: 1);

        $this->getJson('/people?noSleep=1')->assertOk();

        Sleep::assertSleptTimes(1);
    }

    public function test_simulate_changes_the_seed_and_publishes_a_refresh_event(): void
    {
        Cache::forever('faker-seed', 1);
        $since = Pollsockets::now() - 1;

        $this->postJson('/simulate')->assertNoContent();

        $this->assertNotSame(1, Cache::get('faker-seed'));

        $this->getJson("/_pollsockets/pollsockets/poll?since={$since}")
            ->assertOk()
            ->assertJsonStructure(['time', 'events' => ['refresh|']]);
    }
}
