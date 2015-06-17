<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class FSIAdminSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setTemplateParameters($container, 'admin_security.templates', $config['templates']);
        $this->setModelParameters($container, $config['model']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    protected function setTemplateParameters(ContainerBuilder $container, $prefix, $config = array())
    {
        foreach ($config as $key => $value) {
            $parameterName = join('.', array($prefix, $key));
            if (is_array($value)) {
                $this->setTemplateParameters($container, $parameterName, $value);
                continue;
            }

            $container->setParameter($parameterName, $value);
        }
    }

    protected function setModelParameters(ContainerBuilder $container, $model)
    {
        $container->setParameter('admin_security.model.user', $model['user']);
    }
}
