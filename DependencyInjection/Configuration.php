<?php

namespace KRG\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('krg_user');

        $rootNode
            ->children()
                ->arrayNode('registration')
                    ->children()
                        ->scalarNode('confirmed_target_route')->end()
                    ->end()
                ->end()
                ->arrayNode('login')
                    ->children()
                        ->scalarNode('admin_redirect_route')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
