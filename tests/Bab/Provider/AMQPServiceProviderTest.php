<?php

namespace Bab\Provider;

use Bab\Provider\AMQPServiceProvider;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;

class AMQPServiceProviderTest extends ProphecyTestCase
{
    public function test_it_is_initializable()
    {
        $serviceProvider = new AMQPServiceProvider();
        $this->assertInstanceOf('Bab\Provider\AMQPServiceProvider', $serviceProvider);
    }

    public function test_i_get_a_queue()
    {
        $container = $this->getContainer();

        $queue = $container['queue.factory']('queueName');
        $this->assertInstanceOf('\AMQPQueue', $queue);
        $this->assertEquals('queueName', $queue->getName());
    }

    public function test_i_get_an_exchange()
    {
        $container = $this->getContainer();

        $exchange = $container['exchange.factory']('exchangeName');
        $this->assertInstanceOf('\AMQPExchange', $exchange);
        $this->assertEquals('exchangeName', $exchange->getName());
    }

    public function test_i_cant_get_a_queue_from_an_unexisting_connection()
    {
        $container = $this->getContainer();

        $this->setExpectedException('\InvalidArgumentException');
        $container['queue.factory']('queueName', 'unknownConnection');
    }

    public function test_i_cant_get_an_exchange_from_an_unexisting_connection()
    {
        $container = $this->getContainer();

        $this->setExpectedException('\InvalidArgumentException');
        $container['exchange.factory']('exchangeName', 'unknownConnection');
    }

    /**
     * getContainer
     *
     * @return \Pimple\Container
     */
    protected function getContainer()
    {
        $container = new \Pimple\Container();
        $container['amqp.options'] = [
            'connections' => [
                'conn1' => [
                    'host' => '127.0.0.1',
                    'port' => 5672,
                    'login' => 'guest',
                    'password' => 'guest',
                    'vhost' => '/',
                ]
            ]
        ];

        $container->register(new AMQPServiceProvider());

        return $container;
    }
}
