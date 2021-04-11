[![Build Status](https://travis-ci.com/empress-php/empress.svg?branch=master)](https://travis-ci.com/empress-php/empress)
[![Coverage Status](https://coveralls.io/repos/github/empress-php/empress/badge.svg)](https://coveralls.io/github/empress-php/empress)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Work in progress ⚡

# Empress
Empress is a flexible PHP 8 microframework for creating async web applications. It's based on [amphp/http-server](https://github.com/amphp/http-server).
The name is a portmanteau of Express and Amp as Empress's simplicity was first inspired by Express.js. Later, many useful ideas were incorporated from [Spark](http://sparkjava.com/) and [Javalin](https://javalin.io/). Ultimately it's also the name of one of the cards from major arcana, part of the tarot deck.

# Taste it

```php
<?php

use Amp\Loop;
use Empress\Application;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\RouteCollector\RouteCollectorInterface;

#[Group('/index')]
class IndexController implements RouteCollectorInterface
{
    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/')]
    public function index(Context $ctx)
    {
        $ctx->response('<h1>Hello!</h1>');
    }
}

Loop::run(function () {
    $app = Application::create(9010);
    $app->routes(new IndexController());

    $empress = new Empress($app);

    yield $empress->boot();
});
```
