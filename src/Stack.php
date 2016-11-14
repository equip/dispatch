<?php

namespace Equip\Dispatch;

use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Stack
{
    /**
     * @var array
     */
    private $middleware;

    /**
     * @param array $middleware
     */
    public function __construct(array $middleware = [])
    {
        $this->middleware = $middleware;
    }

    /**
     * Add a middleware to the end of the stack.
     *
     * @param ServerMiddlewareInterface $middleware
     *
     * @return void
     */
    public function append(ServerMiddlewareInterface $middleware)
    {
        array_push($this->middleware, $middleware);
    }

    /**
     * Add a middleware to the beginning of the stack.
     *
     * @param ServerMiddlewareInterface $middleware
     *
     * @return void
     */
    public function prepend(ServerMiddlewareInterface $middleware)
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
        $delegate = new Delegate($this->middleware, $default);

        return $delegate->process($request);
    }
}
