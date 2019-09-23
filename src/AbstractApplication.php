<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
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
     * @param \Empress\Routing\RouteConfigurator $configurator
     * @return void
     */
    abstract public function configureRoutes(RouteConfigurator $configurator): void;

    /**
     * Configures the application instance before the server starts.
     *
     * @param \Empress\ApplicationConfigurator $configurator
     * @return void
     */
    abstract public function configureApplication(ApplicationConfigurator $configurator): void;

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
