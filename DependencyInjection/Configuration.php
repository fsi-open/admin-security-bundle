<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use FSi\Bundle\AdminSecurityBundle\Form\Type\Admin\ChangePasswordType;
use FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (true === method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('fsi_admin_security');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('fsi_admin_security');
        }

        $supportedStorages = ['orm'];

        $rootNode
            ->beforeNormalization()
                ->always(function ($v) {
                    if (isset($v['mailer']['from'])) {
                        if (false === isset($v['activation']['mailer']['from'])) {
                            $v['activation']['mailer']['from'] = $v['mailer']['from'];
                        }
                        if (false === isset($v['password_reset']['mailer']['from'])) {
                            $v['password_reset']['mailer']['from'] = $v['mailer']['from'];
                        }
                    }

                    if (isset($v['mailer']['reply_to'])) {
                        if (false === isset($v['activation']['mailer']['reply_to'])) {
                            $v['activation']['mailer']['reply_to'] = $v['mailer']['reply_to'];
                        }
                        if (false === isset($v['password_reset']['mailer']['reply_to'])) {
                            $v['password_reset']['mailer']['reply_to'] = $v['mailer']['reply_to'];
                        }
                    }

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('storage')
                    ->validate()
                        ->ifNotInArray($supportedStorages)
                        ->thenInvalid(
                            'The driver %s is not supported. Please choose one of ' . json_encode($supportedStorages)
                        )
                    ->end()
                    ->cannotBeOverwritten()
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('firewall_name')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('model')
                    ->isRequired()
                    ->children()
                        ->scalarNode('user')->cannotBeEmpty()->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('mailer')
                    ->children()
                        ->scalarNode('from')->defaultNull()->end()
                        ->scalarNode('reply_to')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('activation')
                    ->isRequired()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('token_ttl')
                            ->min(0)
                            ->defaultValue(43200) // 12h
                            ->max(172800) // 48h
                        ->end()
                        ->integerNode('token_length')
                            ->min(16)
                            ->defaultValue(32)
                            ->max(64)
                        ->end()
                        ->arrayNode('mailer')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('from')->cannotBeEmpty()->isRequired()->end()
                                ->scalarNode('template')
                                    ->defaultValue('@FSiAdminSecurity/Activation/mail/activation.html.twig')
                                ->end()
                                ->scalarNode('template_new_token')
                                    ->defaultValue('@FSiAdminSecurity/Activation/mail/newToken.html.twig')
                                ->end()
                                ->scalarNode('reply_to')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('change_password_form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(PasswordReset\ChangePasswordType::class)->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['ResetPassword', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('password_reset')
                    ->isRequired()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('token_ttl')
                            ->min(0)
                            ->defaultValue(43200) // 12h
                            ->max(172800) // 48h
                        ->end()
                        ->integerNode('token_length')
                            ->min(16)
                            ->defaultValue(32)
                            ->max(64)
                        ->end()
                        ->arrayNode('mailer')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('from')->cannotBeEmpty()->isRequired()->end()
                                ->scalarNode('template')
                                    ->defaultValue('@FSiAdminSecurity/PasswordReset/mail/passwordReset.html.twig')
                                ->end()
                                ->scalarNode('reply_to')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('change_password_form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(PasswordReset\ChangePasswordType::class)->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['ResetPassword', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('request_form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(PasswordReset\RequestType::class)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('change_password')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(ChangePasswordType::class)->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['ChangePassword', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('login')->defaultValue('@FSiAdminSecurity/Security/login.html.twig')->end()
                        ->scalarNode('change_password')
                            ->defaultValue('@FSiAdminSecurity/Admin/changePassword.html.twig')
                        ->end()
                        ->arrayNode('activation')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('change_password')
                                    ->defaultValue('@FSiAdminSecurity/Activation/changePassword.html.twig')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('password_reset')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('request')
                                    ->defaultValue('@FSiAdminSecurity/PasswordReset/request.html.twig')
                                ->end()
                                ->scalarNode('change_password')
                                    ->defaultValue('@FSiAdminSecurity/PasswordReset/changePassword.html.twig')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
