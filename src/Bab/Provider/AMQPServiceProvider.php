<?php

namespace Bab\Provider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

class AMQPServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $c)
    {
        $c['queue.factory'] = function ($c) {
            $config = $c['amqp.options'];
            $connections = array();
            foreach ($config['connections'] as $name => $options) {
                $connections[$name] = new \AMQPConnection($options);
            }

            return function ($queueName, $connectionName = null) use ($connections) {
                $names = array_keys($connections);

                if (null === $connectionName) {
                    $connectionName = reset($names);
                }
                if (!array_key_exists($connectionName, $connections)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Unknown connection "%s". Available: [%s]',
                        $connectionName,
                        implode(', ', $names)
                    ));
                }

                $connection = $connections[$connectionName];
                if (!$connection->isConnected()) {
                    $connection->connect();
                }

                $channel = new \AMQPChannel($connection);
                $queue = new \AMQPQueue($channel);
                $queue->setName($queueName);

                return $queue;
            };
        };

        $c['exchange.factory'] = function ($c) {
            $config = $c['amqp.options'];
            $connections = array();
            foreach ($config['connections'] as $name => $options) {
                $connections[$name] = new \AMQPConnection($options);
            }

            return function ($exchangeName, $connectionName = null) use ($connections) {
                $names = array_keys($connections);

                if (null === $connectionName) {
                    $connectionName = reset($names);
                }
                if (!array_key_exists($connectionName, $connections)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Unknown connection "%s". Available: [%s]',
                        $connectionName,
                        implode(', ', $names)
                    ));
                }

                $connection = $connections[$connectionName];
                if (!$connection->isConnected()) {
                    $connection->connect();
                }

                $channel = new \AMQPChannel($connection);
                $exchange = new \AMQPExchange($channel);
                $exchange->setName($exchangeName);

                return $exchange;
            };
        };
    }
}
