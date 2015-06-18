<?php

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

        $rootNode
            ->children()
                ->arrayNode('model')
                    ->isRequired()
                    ->children()
                        ->scalarNode('user')->cannotBeEmpty()->isRequired()->end()
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
                        ->arrayNode('mailer')
                            ->isRequired()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('from')->cannotBeEmpty()->isRequired()->end()
                                ->scalarNode('template')->defaultValue('@FSiAdminSecurity/PasswordReset/mail.html.twig')->end()
                                ->scalarNode('replay_to')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('login')->defaultValue('FSiAdminSecurityBundle:Security:login.html.twig')->end()
                        ->scalarNode('change_password')->defaultValue('FSiAdminSecurityBundle:Admin:change_password.html.twig')->end()
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
