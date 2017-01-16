<?php

namespace Equip\Dispatch;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
    public function process(ServerRequestInterface $request)
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
