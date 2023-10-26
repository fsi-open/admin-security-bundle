<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler;

use FSi\Bundle\AdminBundle\Admin\Element;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;
use function array_walk;
use function sprintf;

final class EnforceSecuredElementsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var bool $enforceSecuredElements */
        $enforceSecuredElements = $container->getParameter('admin_security.enforce_secured_elements');
        if (false === $enforceSecuredElements) {
            return;
        }

        $elementsIds = array_keys($container->findTaggedServiceIds('admin.element'));
        array_walk(
            $elementsIds,
            function (string $serviceId) use ($container): void {
                $definition = $container->getDefinition($serviceId);
                /** @var class-string<Element> $definitionClass */
                $definitionClass = $definition->getClass();
                if (null === $definitionClass) {
                    return;
                }

                if (false === $definitionClass instanceof SecuredElementInterface) {
                    throw new RuntimeException(sprintf(
                        'Admin element\'s class "%s" does not implement the "%s" interface.',
                        $definitionClass,
                        SecuredElementInterface::class
                    ));
                }
            }
        );
    }
}
