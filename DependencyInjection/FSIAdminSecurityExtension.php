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

        $container->setParameter('admin_security.storage', $config['storage']);
        $container->setParameter('admin_security.firewall_name', $config['firewall_name']);
        $this->setTemplateParameters($container, 'admin_security.templates', $config['templates']);
        $this->setModelParameters($container, $config['model']);
        $this->setActivationParameters($container, $config['activation']);
        $this->setPasswordResetParameters($container, $config['password_reset']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load(sprintf('%s.xml', $config['storage']));
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

    private function setActivationParameters(ContainerBuilder $container, $model)
    {
        $container->setParameter('admin_security.activation.token_ttl', $model['token_ttl']);
        $container->setParameter('admin_security.activation.token_length', $model['token_length']);
        $container->setParameter('admin_security.activation.mailer.template', $model['mailer']['template']);
        $container->setParameter('admin_security.activation.mailer.from', $model['mailer']['from']);
        $container->setParameter('admin_security.activation.mailer.reply_to', $model['mailer']['reply_to']);
    }

    private function setPasswordResetParameters(ContainerBuilder $container, $model)
    {
        $container->setParameter('admin_security.password_reset.token_ttl', $model['token_ttl']);
        $container->setParameter('admin_security.password_reset.token_length', $model['token_length']);
        $container->setParameter('admin_security.password_reset.mailer.template', $model['mailer']['template']);
        $container->setParameter('admin_security.password_reset.mailer.from', $model['mailer']['from']);
        $container->setParameter('admin_security.password_reset.mailer.reply_to', $model['mailer']['reply_to']);
    }
}
