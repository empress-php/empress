[![Build Status](https://travis-ci.com/empress-php/empress.svg?branch=master)](https://travis-ci.com/empress-php/empress)
[![Coverage Status](https://coveralls.io/repos/github/empress-php/empress/badge.svg)](https://coveralls.io/github/empress-php/empress)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Work in progress ⚡

# Empress
Empress is a flexible microframework for creating async web applications, based on Amp concurrency framework.
Its name is a portmanteau of Express and Amp as Empress's simplicity was first inspired by Express.js. Ultimately it's also the name
of one of the cards from Major Arcana, a part of the tarot deck.

# Examples
## Hello, Empress!
A minimal working Hello World example.

```php
require_once __DIR__ . '/../vendor/autoload.php';

use Amp\Loop;
use Empress\Empress;
use Empress\AbstractApplication;
use Empress\Routing\RouteConfigurator;

Loop::run(function () {
    $empress = new Empress(new class extends AbstractApplication {
        public function configureRoutes(): RouteConfigurator
        {
            $r = new RouteConfigurator();

            $r->get('/', fn ($params, $request) => "Hello, Empress!");

            return $r;
        }
    });

    yield $empress->boot();
});
```
## RequestContext params
Working with request params is very easy. Just substitute the get route from the previous example:
```php
// ...
$r->get('/{name}', fn ($params, $request) => "Hello, ${params['name']}");
// ...
```
