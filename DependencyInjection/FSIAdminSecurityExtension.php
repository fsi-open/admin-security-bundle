<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FSIAdminSecurityExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('admin_security.storage', $config['storage']);
        $container->setParameter('admin_security.firewall_name', $config['firewall_name']);
        $this->setTemplateParameters($container, 'admin_security.templates', $config['templates']);
        $this->setModelParameters($container, $config['model']);
        $this->setActivationParameters($container, $config['activation']);
        $this->setPasswordResetParameters($container, $config['password_reset']);
        $this->setChangePasswordParameters($container, $config['change_password']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load(sprintf('%s.xml', $config['storage']));
        $loader->load(
            TypeSolver::isSymfony3FormNamingConvention() ? 'forms-symfony-3.xml' : 'forms-symfony-2.xml'
        );
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('fsi_admin', [
            'templates' => [
                'datagrid_theme' => '@FSiAdminSecurity/Admin/datagrid.html.twig'
            ]
        ]);
    }

    protected function setTemplateParameters(ContainerBuilder $container, string $prefix, array $config = []): void
    {
        foreach ($config as $key => $value) {
            $parameterName = implode('.', [$prefix, $key]);
            if (is_array($value)) {
                $this->setTemplateParameters($container, $parameterName, $value);
                continue;
            }

            $container->setParameter($parameterName, $value);
        }
    }

    protected function setModelParameters(ContainerBuilder $container, array $model): void
    {
        $container->setParameter('admin_security.model.user', $model['user']);
    }

    private function setActivationParameters(ContainerBuilder $container, array $model): void
    {
        $container->setParameter('admin_security.activation.token_ttl', $model['token_ttl']);
        $container->setParameter('admin_security.activation.token_length', $model['token_length']);
        $container->setParameter('admin_security.activation.mailer.template', $model['mailer']['template']);
        $container->setParameter('admin_security.activation.mailer.from', $model['mailer']['from']);
        $container->setParameter('admin_security.activation.mailer.reply_to', $model['mailer']['reply_to']);
        $container->setParameter(
            'admin_security.activation.change_password_form.type',
            $model['change_password_form']['type']
        );
        $container->setParameter(
            'admin_security.activation.change_password_form.validation_groups',
            $model['change_password_form']['validation_groups']
        );
    }

    private function setPasswordResetParameters(ContainerBuilder $container, array $model): void
    {
        $container->setParameter('admin_security.password_reset.token_ttl', $model['token_ttl']);
        $container->setParameter('admin_security.password_reset.token_length', $model['token_length']);
        $container->setParameter('admin_security.password_reset.mailer.template', $model['mailer']['template']);
        $container->setParameter('admin_security.password_reset.mailer.from', $model['mailer']['from']);
        $container->setParameter('admin_security.password_reset.mailer.reply_to', $model['mailer']['reply_to']);
        $container->setParameter(
            'admin_security.password_reset.request_form.type',
            $model['request_form']['type']
        );
        $container->setParameter(
            'admin_security.password_reset.change_password_form.type',
            $model['change_password_form']['type']
        );
        $container->setParameter(
            'admin_security.password_reset.change_password_form.validation_groups',
            $model['change_password_form']['validation_groups']
        );
    }

    private function setChangePasswordParameters(ContainerBuilder $container, array $model): void
    {
        $container->setParameter(
            'admin_security.change_password.form.type',
            $model['form']['type']
        );
        $container->setParameter(
            'admin_security.change_password.form.validation_groups',
            $model['form']['validation_groups']
        );
    }
}
