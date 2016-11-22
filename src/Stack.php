<?php

namespace Equip\Dispatch;

use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Stack
{
    /**
     * @var callable
     */
    private $default;

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * @param array $middleware
     * @param callable $default to call when no middleware is available
     */
    public function __construct(array $middleware, callable $default)
    {
        array_map([$this, 'append'], $middleware);
        $this->default = $default;
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
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        $delegate = new Delegate($this->middleware, $this->default);

        return $delegate->process($request);
    }
}
