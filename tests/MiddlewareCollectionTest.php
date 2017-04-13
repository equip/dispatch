<?php

namespace Equip\Dispatch;

use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;

class MiddlewareCollectionTest extends TestCase
{
    public function testMake()
    {
        $collection = MiddlewareCollection::make();

        $this->assertInstanceOf(MiddlewareCollection::class, $collection);
    }

    public function testDefault()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        // No middleware should only execute the default
        $middleware = [];

        // Run
        $collection = new MiddlewareCollection($middleware);
        $output = $collection->dispatch($request, $default);

        // Verify
        $this->assertSame($response, $output);
    }

    public function testConstructorAppends()
    {
        $middleware = $this->realizeMocks([
            $this->mockMiddleware(),
            $this->mockMiddleware(),
        ]);

        // Run
        $collection = new MiddlewareCollection($middleware);
        $accessibleCollection = Liberator::liberate($collection);

        // Verify
        $this->assertSame($middleware, $accessibleCollection->middleware);
    }

    public function testAppend()
    {
        list($first, $second) = $this->realizeMocks([
            $this->mockMiddleware(),
            $this->mockMiddleware(),
        ]);

        // Run
        $collection = new MiddlewareCollection([$first]);
        $accessibleCollection = Liberator::liberate($collection);

        // Verify
        $this->assertSame([$first], $accessibleCollection->middleware);

        // Modify
        $collection->append($second);

        // Verify
        $this->assertSame([$first, $second], $accessibleCollection->middleware);
    }

    public function testPrepend()
    {
        list($first, $second) = $this->realizeMocks([
            $this->mockMiddleware(),
            $this->mockMiddleware(),
        ]);

        // Run
        $collection = new MiddlewareCollection([$first]);
        $accessibleCollection = Liberator::liberate($collection);

        // Verify
        $this->assertSame([$first], $accessibleCollection->middleware);

        // Modify
        $collection->prepend($second);

        // Verify
        $this->assertSame([$second, $first], $accessibleCollection->middleware);
    }

    public function testDispatch()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        $mocks = [
            $this->mockMiddleware(),
            $this->mockMiddleware(),
            $this->mockMiddleware(),
        ];

        $middleware = $this->realizeMocks($mocks);

        // Run
        $collection = new MiddlewareCollection([
            $middleware[0],
            // Collections can be nested
            new MiddlewareCollection([
                $middleware[1],
            ]),
            $middleware[2],
        ]);

        $output = $collection->dispatch($request, $default);

        // Verify
        Phony::inOrder(
            $mocks[0]->process->calledWith($request, '~'),
            $mocks[1]->process->calledWith($request, '~'),
            $mocks[2]->process->calledWith($request, '~')
        );

        $this->assertSame($response, $output);
    }
}
