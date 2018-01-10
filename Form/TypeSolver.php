<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Form;

use Symfony\Component\Form\FormTypeInterface;

/**
 * @internal
 */
final class TypeSolver
{
    public static function getFormType(string $fqcnType, string $shortType): string
    {
        return self::isSymfony3FormNamingConvention() ? $fqcnType : $shortType;
    }

    public static function isChoicesAsValuesOptionTrueByDefault(): bool
    {
        return method_exists(FormTypeInterface::class, 'configureOptions');
    }

    public static function isSymfony3FormNamingConvention(): bool
    {
        return !method_exists(FormTypeInterface::class, 'getName');
    }
}
