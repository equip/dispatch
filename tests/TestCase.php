<?php

namespace Equip\Dispatch;

use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;
use Psr\Http\Server\MiddlewareInterface;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @return ServerRequestInterface
     */
    protected function mockRequest()
    {
        return Phony::mock(ServerRequestInterface::class)->get();
    }

    /**
     * @return ResponseInterface
     */
    protected function mockResponse()
    {
        return Phony::mock(ResponseInterface::class)->get();
    }

    /**
     * Get a mock middleware.
     *
     * @return InstanceHandle
     */
    protected function mockMiddleware()
    {
        $mock = Phony::mock(MiddlewareInterface::class);

        $mock->process->does(static function ($request, $handler) {
            return $handler->handle($request);
        });

        return $mock;
    }

    /**
     * @param InstanceHandle[] $mocks
     *
     * @return object[]
     */
    protected function realizeMocks(array $mocks)
    {
        return array_map(static function (InstanceHandle $mock) {
            return $mock->get();
        }, $mocks);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return callable
     */
    protected function defaultReturnsResponse(ResponseInterface $response)
    {
        return function (ServerRequestInterface $request) use ($response) {
            return $response;
        };
    }
}
