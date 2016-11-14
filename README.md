Equip Dispatch
==============

[![Latest Stable Version](https://img.shields.io/packagist/v/equip/dispatch.svg)](https://packagist.org/packages/equip/dispatch)
[![License](https://img.shields.io/packagist/l/equip/dispatch.svg)](https://github.com/equip/dispatch/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/equip/dispatch.svg)](https://travis-ci.org/equip/dispatch)
[![Code Coverage](https://scrutinizer-ci.com/g/equip/dispatch/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/equip/dispatch/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/equip/dispatch/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/equip/dispatch/?branch=master)

An HTTP Interop compatible middleware dispatcher in [Equip](http://equip.github.io/).
Attempts to be [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/),
[PSR-4](http://www.php-fig.org/psr/psr-4/), and [PSR-7](http://www.php-fig.org/psr/psr-7/) compliant.

Heavily influenced by the design of [Tari by ircmaxwell](https://github.com/ircmaxell/Tari-PHP).

For more information, see [the documentation](http://equipframework.readthedocs.org/en/latest/dispatch).

## Install

```
composer require equip/dispatch
```

## Usage

The `Stack` is a container for middleware that acts as the entry point. It takes
two arguments:

- An array of `$middleware` which must be instances of server middleware.
- A callable `$default` that acts as the terminator for the stack and returns
  an empty response.

Once the stack is prepared it can dispatched with a server request and will return
the response for output.

### Example

```php
use Equip\Dispatch\Stack;

// Any implementation of PSR-15 ServerMiddlewareInterface
$middleware = [
    new FooMiddleware(),
    // ...
];

// Default handler for end of stack
$default = function (RequestInterface $request) {
    // Any implementation of PSR-7 ResponseInterface
    return new Response();
};

$stack = new Stack($middleware, $default);

// Any implementation of PSR-7 ServerRequestInterface
$request = ServerRequest::fromGlobals();
$response = $stack->dispatch($request);
```
