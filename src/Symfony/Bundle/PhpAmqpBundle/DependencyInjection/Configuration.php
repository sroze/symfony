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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * PhpAmqpExtension configuration structure.
 *
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('php_amqp');

        $rootNode
            ->children()
                ->scalarNode('dsn')
                    ->info('The DSN describing the connection to the RabbitMq')
                    ->isRequired()
                ->end()
                ->scalarNode('decoder')
                    ->defaultValue('messenger.transport.default_decoder')
                ->end()
                ->scalarNode('encoder')
                    ->defaultValue('messenger.transport.default_encoder')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
