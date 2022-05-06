<?php

namespace Illuminate\Tests\Foundation\Testing\Concerns;

use Illuminate\Foundation\Mix;
use Illuminate\Foundation\Vite;
use Orchestra\Testbench\TestCase;
use stdClass;

class InteractsWithContainerTest extends TestCase
{
    public function testWithoutViteBindsEmptyHandlerAndReturnsInstance()
    {
        $instance = $this->withoutVite();

        $this->assertSame('', vite(['resources/js/app.js']));
        $this->assertSame($this, $instance);
    }

    public function testWithViteRestoresOriginalHandlerAndReturnsInstance()
    {
        $handler = new stdClass;
        $this->app->instance(Vite::class, $handler);

        $this->withoutVite();
        $instance = $this->withVite();

        $this->assertSame($handler, resolve(Vite::class));
        $this->assertSame($this, $instance);
    }
    public function testWithoutMixBindsEmptyHandlerAndReturnsInstance()
    {
        $instance = $this->withoutMix();

        $this->assertSame('', mix('path/to/asset.png'));
        $this->assertSame($this, $instance);
    }

    public function testWithMixRestoresOriginalHandlerAndReturnsInstance()
    {
        $handler = new stdClass;
        $this->app->instance(Mix::class, $handler);

        $this->withoutMix();
        $instance = $this->withMix();

        $this->assertSame($handler, resolve(Mix::class));
        $this->assertSame($this, $instance);
    }

    public function testForgetMock()
    {
        $this->mock(IntanceStub::class)
            ->shouldReceive('execute')
            ->once()
            ->andReturn('bar');

        $this->assertSame('bar', $this->app->make(IntanceStub::class)->execute());

        $this->forgetMock(IntanceStub::class);
        $this->assertSame('foo', $this->app->make(IntanceStub::class)->execute());
    }
}

class IntanceStub
{
    public function execute()
    {
        return 'foo';
    }
}
