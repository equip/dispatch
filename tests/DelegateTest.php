<?php

namespace Equip\Dispatch;

use Eloquent\Phony\Phpunit\Phony;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DelegateTest extends TestCase
{
    public function testDefault()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        // No middleware should only execute the default
        $middleware = [];

        // Run
        $delegate = new Delegate($middleware, $default);
        $output = $delegate->process($request);

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
        $delegate = new Delegate($middleware, $default);
        $output = $delegate->process($request);

        // Verify
        Phony::inOrder(
            $mocks[0]->process->calledWith($request, '~'),
            $mocks[1]->process->calledWith($request, '~')
        );

        $this->assertSame($output, $response);
    }
}
