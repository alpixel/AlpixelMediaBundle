<?php

namespace Alpixel\Bundle\MediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AlpixelMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('alpixel_media.upload_folder', $config['upload_folder']);
        $container->setParameter('alpixel_media.allowed_mimetypes', $config['allowed_mimetypes']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $parser = new Parser();
        $config = $parser->parse(file_get_contents(__DIR__ . '/../Resources/config/config.yml'));

        foreach ($config as $key => $configuration) {
            $container->prependExtensionConfig($key, $configuration);
        }
    }
}
