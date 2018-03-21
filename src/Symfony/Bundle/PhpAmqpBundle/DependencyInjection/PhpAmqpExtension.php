<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\PhpAmqpBundle\DependencyInjection;

use Symfony\Bridge\PhpAmqp\AmqpReceiver;
use Symfony\Bridge\PhpAmqp\AmqpSender;
use Symfony\Bridge\PhpAmqp\Connection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class PhpAmqpExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $connectionDefinition = (new Definition(Connection::class, [
            $config['dsn'],
            $container->getParameter('kernel.debug')
        ]))->setFactory([Connection::class, 'fromDsn']);

        $container->setDefinitions([
            'messenger.amqp.connection' => $connectionDefinition,
            'messenger.amqp.receiver' => (new Definition(AmqpReceiver::class, [
                new Reference($config['decoder']),
                new Reference('messenger.amqp.connection')
            ]))->addTag('messenger.receiver'),
            'messenger.amqp.sender' => (new Definition(AmqpSender::class, [
                new Reference($config['encoder']),
                new Reference('messenger.amqp.connection')
            ]))->addTag('messenger.sender')
        ]);
    }
}
