<?php

namespace Alpixel\Bundle\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('alpixel_media');

        $rootNode
            ->children()
                ->scalarNode('upload_folder')
                    ->defaultValue('%kernel.root_dir%/../web/upload/')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('allowed_mimetypes')
                    ->prototype('scalar')
                    ->defaultValue(['image/*', 'application/pdf'])
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
