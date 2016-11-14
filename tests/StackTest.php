<?php

namespace Equip\Dispatch;

use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StackTest extends TestCase
{
    public function testDefault()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        // Run
        $stack = new Stack($default);
        $output = $stack->dispatch($request);

        // Verify
        $this->assertSame($response, $output);
    }

    public function testMiddleware()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        // Add process() implementation to middleware mocks
        $process = function ($request, $delegate) {
            return $delegate->process($request);
        };

        $mocks = array_map(function ($mock) use ($process) {
            $mock->process->does($process);
            return $mock;
        }, [
            Phony::mock(ServerMiddlewareInterface::class),
            Phony::mock(ServerMiddlewareInterface::class),
            Phony::mock(ServerMiddlewareInterface::class),
        ]);

        // Realize middleware mocks
        $middleware = array_map(function ($middleware) {
            return $middleware->get();
        }, $mocks);

        // Run
        $stack = new Stack($default, ...$middleware);
        $output = $stack->dispatch($request, $default);

        // Verify
        Phony::inOrder(
            $mocks[0]->process->calledWith($request, '~'),
            $mocks[1]->process->calledWith($request, '~'),
            $mocks[2]->process->calledWith($request, '~')
        );
    }

    public function testAppendAndPrepend()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        $one = Phony::mock(ServerMiddlewareInterface::class)->get();
        $two = Phony::mock(ServerMiddlewareInterface::class)->get();
        $three = Phony::mock(ServerMiddlewareInterface::class)->get();

        // Run
        $stack = new Stack($default, $one);
        $accessible_stack = Liberator::liberate($stack);

        // Verify
        $this->assertCount(1, $accessible_stack->middleware);
        $this->assertSame([$one], $accessible_stack->middleware);

        // Add another middleware to the end
        $stack->append($two);

        // Verify
        $this->assertSame([$one, $two], $accessible_stack->middleware);

        // Add another middleware to the beginning
        $stack->prepend($three);

        // Verify
        $this->assertSame([$three, $one, $two], $accessible_stack->middleware);
    }

    /**
     * @return ServerRequestInterface
     */
    private function mockRequest()
    {
        return Phony::mock(ServerRequestInterface::class)->get();
    }

    /**
     * @return ResponseInterface
     */
    private function mockResponse()
    {
        return Phony::mock(ResponseInterface::class)->get();
    }

    /**
     * @param ResponseInterface $response
     *
     * @return callable
     */
    private function defaultReturnsResponse(ResponseInterface $response)
    {
        return function (ServerRequestInterface $request) use ($response) {
            return $response;
        };
    }
}
