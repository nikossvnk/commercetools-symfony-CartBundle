<?php
/**
 * @author: Ylambers <yaron.lambers@commercetools.de>
 */

namespace Commercetools\Symfony\ShoppingListBundle\DependencyInjection;

use Commercetools\Symfony\CtpBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ShoppingListExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->getParameter('kernel.root_dir');
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);
    }
}
