<?php
declare(strict_types=1);

namespace Tests\Client;

use Bigcommerce\ORM\Client\AuthConfig;
use Bigcommerce\ORM\Client\BasicConfig;
use Bigcommerce\ORM\Client\Connection;
use GuzzleHttp\Client;
use Tests\BaseTestCase;

class ConnectionTest extends BaseTestCase
{
    /** @var \Bigcommerce\ORM\Client\Connection */
    protected $connection;

    /** @var  \GuzzleHttp\Client|\Prophecy\Prophecy\ProphecySubjectInterface */
    protected $client;

    /** @var \Bigcommerce\ORM\Client\AbstractConfig */
    protected $config;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->client = $this->getClient();
    }

    /**
     * @covers \Bigcommerce\ORM\Client\Connection::__construct
     * @covers \Bigcommerce\ORM\Client\Connection::setConfig
     * @covers \Bigcommerce\ORM\Client\Connection::setClient
     * @covers \Bigcommerce\ORM\Client\Connection::getConfig
     * @covers \Bigcommerce\ORM\Client\Connection::getClient
     */
    public function testSettersAndGetters()
    {
        $this->config = $this->getBasicConfig();
        $this->connection = new Connection($this->config, $this->client);

        $this->connection
            ->setClient($this->client)
            ->setConfig($this->config);

        $this->assertEquals($this->config, $this->connection->getConfig());
        $this->assertEquals($this->client, $this->connection->getClient());

        $this->config = $this->getAuthConfig();
        $this->connection = new Connection($this->config, $this->client);

        $this->connection
            ->setClient($this->client)
            ->setConfig($this->config);

        $this->assertEquals($this->config, $this->connection->getConfig());
        $this->assertEquals($this->client, $this->connection->getClient());
    }

    private function getBasicConfig()
    {
        $basicCredentials = [
            'storeUrl' => 'storeUrl',
            'username' => 'username',
            'apiKey' => 'apiKey'
        ];

        return new BasicConfig($basicCredentials);
    }

    private function getAuthConfig()
    {
        $authCredentials = [
            'clientId' => '*clientId',
            'authToken' => 'authToken',
            'storeHash' => 'storeHash',
            'baseUrl' => 'baseUrl'
        ];

        return new AuthConfig($authCredentials);
    }

    private function getClient()
    {
        $client = $this->prophet->prophesize(Client::class);

        return $client->reveal();
    }
}