<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Empress\Configuration\ApplicationConfiguration;
use Empress\Routing\RouteConfigurator;

interface ApplicationInterface extends ServerObserver
{
    /**
     * Defines routes for the application.
     *
     * @param RouteConfigurator $routes
     * @return void
     */
    public function configureRoutes(RouteConfigurator $routes): void;

    /**
     * Configures the application instance before the server starts.
     *
     * @param ApplicationConfiguration $configuration
     * @return void
     */
    public function configureApplication(ApplicationConfiguration $configuration);

    /**
     * @inheritDoc
     */
    public function onStart(Server $server): Promise;

    /**
     * @inheritDoc
     */
    public function onStop(Server $server): Promise;
}
