<?php

namespace Equip\Dispatch;

use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Delegate implements DelegateInterface
{
    /**
     * @var array
     */
    private $middleware;

    /**
     * @var callable
     */
    private $default;

    /**
     * @var integer
     */
    private $index = 0;

    /**
     * @param array $middleware
     * @param callable $default
     */
    public function __construct(array $middleware, callable $default)
    {
        $this->middleware = $middleware;
        $this->default = $default;
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
}
