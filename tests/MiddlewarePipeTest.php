<?php

namespace Equip\Dispatch;

use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewarePipeTest extends TestCase
{
    public function testDefault()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        // No middleware should only execute the default
        $middleware = [];

        // Run
        $pipe = new MiddlewarePipe($middleware);
        $output = $pipe->dispatch($request, $default);

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
        $pipe = new MiddlewarePipe($middleware);
        $accessiblePipe = Liberator::liberate($pipe);

        // Verify
        $this->assertSame($middleware, $accessiblePipe->middleware);
    }

    public function testAppend()
    {
        list($first, $second) = $this->realizeMocks([
            $this->mockMiddleware(),
            $this->mockMiddleware(),
        ]);

        // Run
        $pipe = new MiddlewarePipe([$first]);
        $accessiblePipe = Liberator::liberate($pipe);

        // Verify
        $this->assertSame([$first], $accessiblePipe->middleware);

        // Modify
        $pipe->append($second);

        // Verify
        $this->assertSame([$first, $second], $accessiblePipe->middleware);
    }

    public function testPrepend()
    {
        list($first, $second) = $this->realizeMocks([
            $this->mockMiddleware(),
            $this->mockMiddleware(),
        ]);

        // Run
        $pipe = new MiddlewarePipe([$first]);
        $accessiblePipe = Liberator::liberate($pipe);

        // Verify
        $this->assertSame([$first], $accessiblePipe->middleware);

        // Modify
        $pipe->prepend($second);

        // Verify
        $this->assertSame([$second, $first], $accessiblePipe->middleware);
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
        $pipe = new MiddlewarePipe([
            $middleware[0],
            // Pipes can be nested
            new MiddlewarePipe([
                $middleware[1],
            ]),
            $middleware[2],
        ]);

        $output = $pipe->dispatch($request, $default);

        // Verify
        Phony::inOrder(
            $mocks[0]->process->calledWith($request, '~'),
            $mocks[1]->process->calledWith($request, '~'),
            $mocks[2]->process->calledWith($request, '~')
        );

        $this->assertSame($response, $output);
    }
}
