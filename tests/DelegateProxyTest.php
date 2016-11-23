<?php

namespace Equip\Dispatch;

use Eloquent\Phony\Phpunit\Phony;
use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;

class DelegateProxyTest extends TestCase
{
    public function testWrap()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();

        $delegate = Phony::mock(DelegateInterface::class);

        $delegate->process->does(function (RequestInterface $request) use ($response) {
            return $response;
        });

        // Run
        $proxy = new DelegateProxy($delegate->get());
        $output = $proxy($request);

        // Verify
        Phony::inOrder(
            $delegate->process->calledWith($request)
        );

        $this->assertTrue(is_callable($proxy));
        $this->assertSame($response, $output);
    }
}
