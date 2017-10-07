<?php

namespace Equip\Dispatch;

use Eloquent\Phony\Phpunit\Phony;

class HandlerTest extends TestCase
{
    public function testDefault()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        // No middleware should only execute the default
        $middleware = [];

        // Run
        $handler = new Handler($middleware, $default);
        $output = $handler->handle($request);

        // Verify
        $this->assertSame($response, $output);
    }

    public function testPiping()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        $mocks = [
            $this->mockMiddleware(),
            $this->mockMiddleware(),
        ];

        $middleware = $this->realizeMocks($mocks);

        // Run
        $handler = new Handler($middleware, $default);
        $output = $handler->handle($request);

        // Verify
        Phony::inOrder(
            $mocks[0]->process->calledWith($request, '~'),
            $mocks[1]->process->calledWith($request, '~')
        );

        $this->assertSame($output, $response);
    }
}
