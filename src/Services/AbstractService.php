<?php

namespace Empress\Services;

use Pimple\Container;

abstract class AbstractService
{
  /** @var \Pimple\Container */
  protected $container;

  /** @var array */
  protected $config;

  final public function __construct(Container $container, array $config = [])
  {
    $this->container = $container;
    $this->config = $config;
  }

  final public function getContainer(): Container
  {
    return $this->container;
  }

  final public function getConfig(): array
  {
    return $this->config;
  }

  public abstract function getServiceObject();
}