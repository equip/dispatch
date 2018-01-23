<?php

namespace Equip\Dispatch;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HandlerProxy
{
    /**
     * @var RequestHandlerInterface
     */
    private $adaptee;

    /**
     * @param RequestHandlerInterface $adaptee
     */
    public function __construct(RequestHandlerInterface $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    /**
     * Process the request using a handler.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->adaptee->handle($request);
    }
}
