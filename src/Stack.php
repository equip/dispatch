<?php

namespace Equip\Dispatch;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class Stack implements DelegateInterface
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
     * @var integer
     */
    private $index = 0;

    /**
     * @param callable $default to call when no middleware is available
     * @param array $middleware
     */
    public function __construct(callable $default, ...$middleware)
    {
        $this->default = $default;
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
     * Process the request using the current middleware.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request)
    {
        if (empty($this->middleware[$this->index])) {
            return call_user_func($this->default, $request);
        }

        return $this->middleware[$this->index]->process($request, $this->nextDelegate());
    }

    /**
     * Get a delegate pointing to the next middleware.
     *
     * @return static
     */
    private function nextDelegate()
    {
        $copy = clone $this;
        $copy->index++;

        return $copy;
    }

    /**
     * Dispatch the middleware stack.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        return $this->process($request);
    }
}
