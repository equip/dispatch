<?php

namespace Equip\Dispatch;

use Interop\Http\Server\RequestHandlerInterface;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareCollection implements MiddlewareInterface
{
    /**
     * Create a new dispatcher.
     *
     * @param array $middleware
     *
     * @return static
     */
    public static function make(array $middleware = [])
    {
        return new static($middleware);
    }

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * @param array $middleware
     */
    public function __construct(array $middleware = [])
    {
        array_map([$this, 'append'], $middleware);
    }

    /**
     * Add a middleware to the end of the stack.
     *
     * @param MiddlewareInterface $middleware
     *
     * @return void
     */
    public function append(MiddlewareInterface $middleware)
    {
        array_push($this->middleware, $middleware);
    }

    /**
     * Add a middleware to the beginning of the stack.
     *
     * @param MiddlewareInterface $middleware
     *
     * @return void
     */
    public function prepend(MiddlewareInterface $middleware)
    {
        array_unshift($this->middleware, $middleware);
    }

    /**
     * Dispatch the middleware stack.
     *
     * @param ServerRequestInterface $request
     * @param callable $default to call when no middleware is available
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, callable $default)
    {
        $handler = new Handler($this->middleware, $default);

        return $handler->handle($request);
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $nextContainerHandler): ResponseInterface
    {
        $default = new HandlerProxy($nextContainerHandler);
        $handler = new Handler($this->middleware, $default);

        return $handler->handle($request);
    }
}
