<?php

namespace KRG\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
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
                        ->scalarNode('user_target_route')->defaultNull()->end()
                    ->end()
                    ->children()
                        ->scalarNode('admin_target_route')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
