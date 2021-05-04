<?php

use Empress\Application;
use Empress\Context;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Route;

require __DIR__ . '/../vendor/autoload.php';

class ValidationController
{
    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/age/:age')]
    public function index(Context $ctx)
    {
        $age = $ctx['age']
            ->to('int')
            ->check(fn (int $age) => $age > 18, "You're a minor!")
            ->unwrapOr(2137);

        $ctx->html('Your age is: ' . $age);
    }
}

$app = Application::create(8001);
$app->routes(new ValidationController());

return $app;
