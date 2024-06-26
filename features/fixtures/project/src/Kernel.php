<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\FixturesBundle;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

use function interface_exists;
use function sprintf;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        /** @var array<class-string<BundleInterface>, array<string, bool>> $contents */
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache';
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/logs';
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $configDirectory = $this->getProjectDir() . '/config';
        $container->addResource(new FileResource($configDirectory . '/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', $this->debug);
        $container->setParameter('container.dumper.inline_factories', true);

        $loader->load($configDirectory . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($configDirectory . '/{packages}/' . $this->environment . '/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($configDirectory . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($configDirectory . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
        if (true === interface_exists(PasswordHasherFactoryInterface::class)) {
            $loader->load($configDirectory . '/{conditional}/security_5' . self::CONFIG_EXTS, 'glob');
        } else {
            $loader->load($configDirectory . '/{conditional}/security_4' . self::CONFIG_EXTS, 'glob');
        }

        if (true === interface_exists(SessionFactoryInterface::class)) {
            $loader->load($configDirectory . '/{conditional}/framework_5' . self::CONFIG_EXTS, 'glob');
        } else {
            $loader->load($configDirectory . '/{conditional}/framework_4' . self::CONFIG_EXTS, 'glob');
        }

        $loader->load(sprintf('%s/../src/Resources/config/services.xml', __DIR__));
    }

    /**
     * @param RouteCollectionBuilder|RoutingConfigurator $routes
     */
    protected function configureRoutes($routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        if (true === $routes instanceof RouteCollectionBuilder) {
            $routes->import($confDir . '/{routes}/' . $this->environment . '/*' . self::CONFIG_EXTS, '/', 'glob');
            $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
            $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
        } else {
            $routes->import($confDir . '/{routes}/' . $this->environment . '/*' . self::CONFIG_EXTS, 'glob');
            $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, 'glob');
            $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, 'glob');
        }
    }
}
