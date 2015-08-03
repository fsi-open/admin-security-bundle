<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fsi_admin_security');

        $supportedDrivers = array('orm');

        $rootNode
            ->beforeNormalization()
                ->always(function($v) {
                    if (isset($v['mailer']['from'])) {
                        if (!isset($v['activation']['mailer']['from'])) {
                            $v['activation']['mailer']['from'] = $v['mailer']['from'];
                        }
                        if (!isset($v['password_reset']['mailer']['from'])) {
                            $v['password_reset']['mailer']['from'] = $v['mailer']['from'];
                        }
                    }

                    if (isset($v['mailer']['reply_to'])) {
                        if (!isset($v['activation']['mailer']['reply_to'])) {
                            $v['activation']['mailer']['reply_to'] = $v['mailer']['reply_to'];
                        }
                        if (!isset($v['password_reset']['mailer']['reply_to'])) {
                            $v['password_reset']['mailer']['reply_to'] = $v['mailer']['reply_to'];
                        }
                    }

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($supportedDrivers))
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
                            ->cannotBeEmpty()
                            ->min(0)
                            ->defaultValue(43200) // 12h
                            ->max(172800) // 48h
                        ->end()
                        ->integerNode('token_length')
                            ->cannotBeEmpty()
                            ->min(16)
                            ->defaultValue(32)
                            ->max(64)
                        ->end()
                        ->arrayNode('mailer')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('from')->cannotBeEmpty()->isRequired()->end()
                                ->scalarNode('template')->defaultValue('@FSiAdminSecurity/Activation/mail.html.twig')->end()
                                ->scalarNode('reply_to')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('password_reset')
                    ->isRequired()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('token_ttl')
                            ->cannotBeEmpty()
                            ->min(0)
                            ->defaultValue(43200) // 12h
                            ->max(172800) // 48h
                        ->end()
                        ->integerNode('token_length')
                            ->cannotBeEmpty()
                            ->min(16)
                            ->defaultValue(32)
                            ->max(64)
                        ->end()
                        ->arrayNode('mailer')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('from')->cannotBeEmpty()->isRequired()->end()
                                ->scalarNode('template')->defaultValue('@FSiAdminSecurity/PasswordReset/mail.html.twig')->end()
                                ->scalarNode('reply_to')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('login')->defaultValue('FSiAdminSecurityBundle:Security:login.html.twig')->end()
                        ->scalarNode('change_password')->defaultValue('FSiAdminSecurityBundle:Admin:change_password.html.twig')->end()
                        ->arrayNode('activation')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('change_password')->defaultValue('FSiAdminSecurityBundle:Activation:change_password.html.twig')->end()
                            ->end()
                        ->end()
                        ->arrayNode('password_reset')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('request')->defaultValue('FSiAdminSecurityBundle:PasswordReset:request.html.twig')->end()
                                ->scalarNode('change_password')->defaultValue('FSiAdminSecurityBundle:PasswordReset:change_password.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
