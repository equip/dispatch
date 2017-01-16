<?php

namespace Equip\Dispatch;

use Eloquent\Phony\Phpunit\Phony;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

class DelegateProxyTest extends TestCase
{
    public function testWrap()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();

        $delegate = Phony::mock(DelegateInterface::class);

        $delegate->process->does(function (ServerRequestInterface $request) use ($response) {
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
