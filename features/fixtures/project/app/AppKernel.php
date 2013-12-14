<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FSi\Bundle\DataSourceBundle\DataSourceBundle(),
            new FSi\Bundle\DataGridBundle\DataGridBundle(),
            new FSi\Bundle\AdminBundle\FSiAdminBundle(),
            new FSi\FixturesBundle\FSiFixturesBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FSi\Bundle\AdminSecurityBundle\FSiAdminSecurityBundle()
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(sprintf('%s/config/config.yml', __DIR__));
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/FSiAdminSecurityBundle/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/FSiAdminSecurityBundle/logs';
    }

    /**
     * @return string
     */
    protected function getContainerBaseClass()
    {
        if ('test' == $this->environment) {
            return '\PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer';
        }

        return parent::getContainerBaseClass();
    }
}