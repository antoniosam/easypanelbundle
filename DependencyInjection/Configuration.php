<?php

namespace Ast\EasyPanelBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('easy_panel');

        $rootNode
            ->children()
            ->scalarNode('layoutpanel')->defaultNull()->end()
            ->scalarNode('viewpanel') ->defaultNull()->end()
            ->scalarNode('viewmenu') ->defaultNull()->end()
            ->scalarNode('nombreproyecto') ->defaultNull()->end()
            ->scalarNode('rutalogout') ->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
