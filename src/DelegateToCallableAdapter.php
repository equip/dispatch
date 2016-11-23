<?php
namespace Equip\Dispatch;

use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DelegateToCallableAdapter
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
    public function __invoke(RequestInterface $request)
    {
        return $this->adaptee->process($request);
    }
}
