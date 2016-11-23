<?php

namespace Equip\Dispatch;

use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewarePipeTest extends TestCase
{
    public function testDefault()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();
        $default = $this->defaultReturnsResponse($response);

        // Run
        $pipe = new MiddlewarePipe();
        $output = $pipe->dispatch($request, $default);

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
        $pipe = new MiddlewarePipe($middleware);
        $output = $pipe->dispatch($request, $default);

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
        $pipe = new MiddlewarePipe([$one]);
        $accessible_pipe = Liberator::liberate($pipe);

        // Verify
        $this->assertCount(1, $accessible_pipe->middleware);
        $this->assertSame([$one], $accessible_pipe->middleware);

        // Add another middleware to the end
        $pipe->append($two);

        // Verify
        $this->assertSame([$one, $two], $accessible_pipe->middleware);

        // Add another middleware to the beginning
        $pipe->prepend($three);

        // Verify
        $this->assertSame([$three, $one, $two], $accessible_pipe->middleware);
    }

    public function testMiddlewarePipeAsMiddleware()
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
            Phony::mock(ServerMiddlewareInterface::class),
            Phony::mock(ServerMiddlewareInterface::class),
            Phony::mock(ServerMiddlewareInterface::class),
        ]);

        // Realize middleware mocks
        $middleware = array_map(function ($middleware) {
            return $middleware->get();
        }, $mocks);

        $pipe = new MiddlewarePipe([
            $middleware[0],
            $middleware[1],
            new MiddlewarePipe([
                $middleware[2],
                $middleware[3],
            ]),
            $middleware[4],
            $middleware[5],
        ]);

        // Run
        $output = $pipe->dispatch($request, $default);

        // Verify
        Phony::inOrder(
            $mocks[0]->process->calledWith($request, '~'),
            $mocks[1]->process->calledWith($request, '~'),
            $mocks[2]->process->calledWith($request, '~'),
            $mocks[3]->process->calledWith($request, '~'),
            $mocks[4]->process->calledWith($request, '~'),
            $mocks[5]->process->calledWith($request, '~')
        );
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
