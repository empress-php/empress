<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
use Empress\Configuration\ApplicationConfiguration;
use Empress\Routing\RouteConfigurator;

/**
 * Defines an application object that will be run against http-server.
 * Since it implements the ServerObserver interface it has two
 * lifecycle methods - onStart() and onStop() that can be used
 * when the application is booted and shut down respectively.
 */
abstract class AbstractApplication implements ServerObserver
{

    /**
     * Defines routes for the application.
     *
     * @param RouteConfigurator $routeConfigurator
     * @return void
     */
    abstract public function configureRoutes(RouteConfigurator $routeConfigurator): void;

    /**
     * Configures the application instance before the server starts.
     *
     * @param ApplicationConfiguration $applicationConfiguration
     * @return void
     */
    abstract public function configureApplication(ApplicationConfiguration $applicationConfiguration): void;

    /** @inheritDoc */
    public function onStart(Server $server): Promise
    {
        return new Success();
    }

    /** @inheritDoc */
    public function onStop(Server $server): Promise
    {
        return new Success();
    }
}
