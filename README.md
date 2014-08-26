# AMQP Service provider for Pimple

[![Build Status](https://travis-ci.org/odolbeau/amqp-service-provider.png)](https://travis-ci.org/odolbeau/amqp-service-provider)

## Installation

The recommended way to install this bundle is through
[Composer](http://getcomposer.org/). Require the
`odolbeau/amqp-service-provider` package into your `composer.json` file:

```json
{
    "require": {
        "odolbeau/amqp-service-provider": "@stable"
    }
}
```

**Protip:** you should browse the
[`odolbeau/amqp-service-provider`](https://packagist.org/packages/odolbeau/amqp-service-provider)
page to choose a stable version to use, avoid the `@stable` meta constraint.

## Usage

Register the service provider ([see doc for more
informations](http://pimple.sensiolabs.org/#extending-a-container)).

```php
use Pimple\Container;
use Bab\Provider\AMQPServiceProvider;

// Create a new container
$container = new Container();

// Add some configuration
$container['amqp.options'] = [
    'connections' => [
        'conn1' => [
            'host' => '127.0.0.1',
            'port' => 5672,
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
        ],
        'conn2' => [
            'host' => '127.0.0.1',
            'port' => 5672,
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => 'another_vhost',
        ]
    ]
];

// Register the service provider
$container->register(new AMQPServiceProvider());
```

You can now retrieve queues and / or exchanges like this :

```php
// To get a queue
$container['queue.factory']('queueName', 'conn1');
// To get an exchange
$container['exchange.factory']('queueName', 'conn2');
```
