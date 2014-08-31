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
        $c['amqp.connections.initializer'] = function ($c) {
            $config = $c['amqp.options'];

            $connections = array();
            if (isset($config['connections'])) {
                foreach ($config['connections'] as $name => $options) {
                    $connections[$name] = new \AMQPConnection($options);
                }

                return $connections;
            }

            if (isset($config['connection'])) {
                return array('default' => new \AMQPConnection($config['connection']));
            }

            throw new \LogicException('No connection defined');

        };

        $c['queue.factory'] = function ($c) {
            $connections = $c['amqp.connections.initializer'];

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
            $connections = $c['amqp.connections.initializer'];

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
