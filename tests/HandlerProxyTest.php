<?php

namespace Equip\Dispatch;

use Eloquent\Phony\Phpunit\Phony;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class HandlerProxyTest extends TestCase
{
    public function testWrap()
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse();

        $handler = Phony::mock(RequestHandlerInterface::class);

        $handler->handle->does(function (ServerRequestInterface $request) use ($response) {
            return $response;
        });

        // Run
        $proxy = new HandlerProxy($handler->get());
        $output = $proxy($request);

        // Verify
        Phony::inOrder(
            $handler->handle->calledWith($request)
        );

        $this->assertTrue(is_callable($proxy));
        $this->assertSame($response, $output);
    }
}
