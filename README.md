[![Build Status](https://travis-ci.com/jakobmats/empress.svg?branch=master)](https://travis-ci.com/jakobmats/empress)

# EVERYTHING'S STILL VERY MUCH IN EARLY PROGRESS

# Empress
Empress is a flexible microframework for creating async web applications, based on Amp concurrency framework.
Its name is a portmanteau of Express and Amp as Empress's simplicity was first inspired by Express.js. Ultimately it's also the name
of one of the cards from Major Arcana, a part of the tarot deck.

It's probably valid to say that Empress is something you would have eventually come up with anyway, just in a cohesive package.

## Hello World

```PHP
use Empress\Empress;
use function Empress\Routing\route;
use function Empress\Routing\controller;

use Amp\Loop;

// Building the container
$container = ...;

$app = new Empress($container);

$app->router(
  controller('',
    route('get', '/{name:\w+}', function ($params) {
      return "Hello, {$params['name']}\n";
    })
  )
);

Loop::run(function () use ($app) {
  yield $app->run();
});
```

## Features

1. Simplicity

The core framework is purposefully kept simple. It lacks things like templating or ORM and instead focuses on separating
the application from its dependencies which can live in any PSR-11 compliant container.

2. Easy Routing

Routing is performed by creating controller and route definitions that are registered in the application object. Internally amphp/http-server uses nikic/fastroute
so for further information on how to create routes see the documentation there.

```PHP
$app->router(
  controller('',
    route('get', '/', 'index'),
    route('get', '/{name:\w+}', 'greet')
  )
);
```

3. Response Transformers

Response transformers let you map return values from request handlers to an arbitrary Response object. The most basic
transformer is the JSON transformer:

```PHP
$app->router(
  controller(BooksController::class,
    route('get', '/', 'getBooks', new JsonTransformer())
  )
 );
 ```
 
 Response transformers are a concept borrowed from the JVM Spark framework.

 4. Static File Serving

 Naturally as Empress is based on amphp/http-server it also employs amphp/http-server-static-content which allows for easy static
 content serving.

 ```PHP
 $app->serveStaticContent('/public');
 ```

5. Dependency Injection

Empress does not require you to use any specific DI container as long as it's a PSR-11
compliant implementation. The container is the first argument passed to the Empress
constructor:

```PHP
// ...
$app = new Empress($container);
```

Dependencies are injected to controllers via constructors:

```PHP
class IndexController
{
  private $logger;

  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  public function index()
  {
    $this->logger->info('Hello, Empress!');
  }
}
```

Mind that all controller arguments must be resolved beforehand. You can also use
autowiring that is present in some PSR-11 implementations like symfony/dependency-injection.