<?php

namespace Empress\Test\Configuration;

use Empress\Configuration\ApplicationConfiguration;
use Empress\Configuration\ImmutableApplicationConfiguration;
use PHPUnit\Framework\TestCase;

class ImmutableApplicationConfigurationTest extends TestCase
{

    /**
     * @var ApplicationConfiguration
     */
    private $configuration;

    /**
     * @var ImmutableApplicationConfiguration
     */
    private $immutable;

    public function setUp(): void
    {
        $this->configuration = new ApplicationConfiguration();
        $this->immutable = new ImmutableApplicationConfiguration($this->configuration);
    }

    public function testGetTlsContext()
    {
    }

    public function testGetMiddlewares()
    {
    }

    public function testGetStaticContentPath()
    {
    }

    public function testGetLogger()
    {
    }

    public function testGetTlsPort()
    {
    }

    public function testGetDocumentRootHandler()
    {
    }

    public function testGetSessionStorage()
    {
    }

    public function testGetServerOptions()
    {
    }
}
