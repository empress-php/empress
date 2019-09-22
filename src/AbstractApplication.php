<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
use Empress\Routing\RouteConfigurator;

abstract class AbstractApplication implements ServerObserver
{

    /**
     * Define routes for the application.
     *
     * @param \Empress\Routing\RouteConfigurator $configurator
     * @return void
     */
    abstract public function configureRoutes(RouteConfigurator $configurator): void;

    /**
     * Configure the application instance before the server starts.
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
