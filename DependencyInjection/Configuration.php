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
