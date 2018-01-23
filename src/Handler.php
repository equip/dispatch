<?php

namespace Equip\Dispatch;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements RequestHandlerInterface
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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middleware[$this->index])) {
            return call_user_func($this->default, $request);
        }

        return $this->middleware[$this->index]->process($request, $this->nextHandler());
    }

    /**
     * Get a handler pointing to the next middleware.
     *
     * @return static
     */
    private function nextHandler()
    {
        $copy = clone $this;
        $copy->index++;

        return $copy;
    }
}
