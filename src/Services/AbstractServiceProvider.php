<?php

namespace Empress\Services;

use Amp\coroutine;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Empress\Empress;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
  protected $app;

  final public function __construct(Empress $app)
  {
    $this->app = $app;
  }

  final public function addService(Container $container, string $name, string $class, array $config = [])
  {
    $container[$name] = function () use ($class, $container, $config) {
      return (new $class($container, $config))->getServiceObject();
    };
  }

  public abstract function register(Container $container);
}
