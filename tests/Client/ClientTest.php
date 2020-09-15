<?php
declare(strict_types=1);

namespace Tests\Client;

use Bigcommerce\ORM\Cache\FileCache\FileCachePool;
use Bigcommerce\ORM\Client\Client;
use Bigcommerce\ORM\Client\Connection;
use Monolog\Logger;
use Tests\BaseTestCase;

class ClientTest extends BaseTestCase
{
    /** @var \Bigcommerce\ORM\Client\Client */
    protected $client;

    /** @var \Bigcommerce\ORM\Client\Connection|\Prophecy\Prophecy\ProphecySubjectInterface */
    protected $connection;

    /** @var \Bigcommerce\ORM\Cache\FileCache\FileCachePool|\Prophecy\Prophecy\ProphecySubjectInterface */
    protected $cache;

    /** @var \Monolog\Logger|\Prophecy\Prophecy\ProphecySubjectInterface */
    protected $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->getConnection();
        $this->logger = $this->getLogger();
        $this->cache = $this->getCache();
        $this->client = new Client($this->connection, $this->cache, $this->logger);
    }

    /**
     * @covers \Bigcommerce\ORM\Client\Client::__construct
     * @covers \Bigcommerce\ORM\Client\Client::setConnection
     * @covers \Bigcommerce\ORM\Client\Client::setLogger
     * @covers \Bigcommerce\ORM\Client\Client::setCachePool
     * @covers \Bigcommerce\ORM\Client\Client::getConnection
     * @covers \Bigcommerce\ORM\Client\Client::getLogger
     * @covers \Bigcommerce\ORM\Client\Client::getCachePool
     */
    public function testSettersAndGetters()
    {
        $this->client = new Client($this->connection, $this->cache, $this->logger);
        $this->client
            ->setLogger($this->logger)
            ->setCachePool($this->cache)
            ->setConnection($this->connection);

        $this->assertEquals($this->logger, $this->client->getLogger());
        $this->assertEquals($this->cache, $this->client->getCachePool());
        $this->assertEquals($this->connection, $this->client->getConnection());
    }

    private function getConnection()
    {
        $connection = $this->prophet->prophesize(Connection::class);

        return $connection->reveal();
    }

    private function getCache()
    {
        $cache = $this->prophet->prophesize(FileCachePool::class);

        return $cache->reveal();
    }

    private function getLogger()
    {
        $logger = $this->prophet->prophesize(Logger::class);

        return $logger->reveal();
    }
}