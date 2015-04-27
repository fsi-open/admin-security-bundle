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
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('login')->defaultValue('FSiAdminSecurityBundle:Security:login.html.twig')->end()
                        ->scalarNode('change_password')->defaultValue('FSiAdminSecurityBundle:Admin:change_password.html.twig')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}