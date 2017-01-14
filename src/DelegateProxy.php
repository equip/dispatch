<?php

namespace Equip\Dispatch;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DelegateProxy
{
    /**
     * @var DelegateInterface
     */
    private $adaptee;

    /**
     * @param DelegateInterface $adaptee
     */
    public function __construct(DelegateInterface $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    /**
     * Process the request using a delegate.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->adaptee->process($request);
    }
}
