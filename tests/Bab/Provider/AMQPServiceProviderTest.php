<?php

namespace Bab\Provider;

use Bab\Provider\AMQPServiceProvider;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;

class AMQPServiceProviderTest extends ProphecyTestCase
{
    protected function setUp()
    {
        if (!class_exists('AMQPConnection')) {
            $this->markTestSkipped('The AMQP extension is not available');
        }

        parent::setUp();
    }

    public function test_it_is_initializable()
    {
        $serviceProvider = new AMQPServiceProvider();
        $this->assertInstanceOf('Bab\Provider\AMQPServiceProvider', $serviceProvider);
    }

    public function test_i_get_a_queue_with_single_connection()
    {
        $container = $this->getSingleConnectionContainer();

        $queue = $container['queue.factory']('queueName');
        $this->assertInstanceOf('\AMQPQueue', $queue);
        $this->assertEquals('queueName', $queue->getName());
    }

    public function test_i_get_an_exchange_with_single_connection()
    {
        $container = $this->getSingleConnectionContainer();

        $exchange = $container['exchange.factory']('exchangeName');
        $this->assertInstanceOf('\AMQPExchange', $exchange);
        $this->assertEquals('exchangeName', $exchange->getName());
    }

    public function test_i_get_a_queue_with_multiple_connection()
    {
        $container = $this->getMultipleConnectionsContainer();

        $queue = $container['queue.factory']('queueName', 'conn2');
        $this->assertInstanceOf('\AMQPQueue', $queue);
        $this->assertEquals('queueName', $queue->getName());
    }

    public function test_i_get_an_exchange_with_multiple_connection()
    {
        $container = $this->getMultipleConnectionsContainer();

        $exchange = $container['exchange.factory']('exchangeName', 'conn2');
        $this->assertInstanceOf('\AMQPExchange', $exchange);
        $this->assertEquals('exchangeName', $exchange->getName());
    }

    public function test_i_cant_get_a_queue_from_an_unexisting_connection()
    {
        $container = $this->getSingleConnectionContainer();

        $this->setExpectedException('\InvalidArgumentException');
        $container['queue.factory']('queueName', 'unknownConnection');
    }

    public function test_i_cant_get_an_exchange_from_an_unexisting_connection()
    {
        $container = $this->getSingleConnectionContainer();

        $this->setExpectedException('\InvalidArgumentException');
        $container['exchange.factory']('exchangeName', 'unknownConnection');
    }

    public function test_i_cant_get_an_queue_when_no_connection_configured()
    {
        $container = new \Pimple\Container();
        $container->register(new AMQPServiceProvider());

        $this->setExpectedException('\LogicException');
        $container['queue.factory']('queueName');
    }

    public function test_i_cant_get_an_exchange_when_no_connection_configured()
    {
        $container = new \Pimple\Container();
        $container->register(new AMQPServiceProvider());

        $this->setExpectedException('\LogicException');
        $container['exchange.factory']('exchangeName');
    }

    /**
     * getSingleConnectionContainer
     *
     * @return \Pimple\Container
     */
    protected function getSingleConnectionContainer()
    {
        $container = new \Pimple\Container();
        $container['amqp.options'] = [
            'connection' => [
                'host' => '127.0.0.1',
                'port' => 5672,
                'login' => 'guest',
                'password' => 'guest',
                'vhost' => '/',
            ]
        ];

        $container->register(new AMQPServiceProvider());

        return $container;
    }

    /**
     * getMultipleConnectionsContainer
     *
     * @return \Pimple\Container
     */
    protected function getMultipleConnectionsContainer()
    {
        $container = new \Pimple\Container();
        $container['amqp.options'] = [
            'connections' => [
                'conn1' => array(
                    'host' => '127.0.0.1',
                    'port' => 5672,
                    'login' => 'guest',
                    'password' => 'guest',
                    'vhost' => '/',
                ),
                'conn2' => array(
                    'host' => '127.0.0.1',
                    'port' => 5672,
                    'login' => 'guest',
                    'password' => 'guest',
                    'vhost' => '/',
                ),
            ]
        ];

        $container->register(new AMQPServiceProvider());

        return $container;
    }
}
