<?php

namespace Rts\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rts_user');
        $rootNode
            ->children()
                ->variableNode('map_ldap_roles')
                    ->defaultValue(array())
                    ->end()
                ->variableNode('ip_whitelist')
                    ->defaultValue(array())
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
