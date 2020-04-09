<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Empress\Configuration\ApplicationConfiguration;
use Empress\Routing\Routes;

interface ApplicationInterface extends ServerObserver
{

    /**
     * Defines routes for the application.
     *
     * @param Routes $routes
     * @return void
     */
    public function configureRoutes(Routes $routes): void;

    /**
     * Configures the application instance before the server starts.
     *
     * @param ApplicationConfiguration $configuration
     * @return void
     */
    public function configureApplication(ApplicationConfiguration $configuration);
}
