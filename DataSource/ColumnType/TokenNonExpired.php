<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\DataSource\ColumnType;

use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Component\DataGrid\Column\ColumnInterface;
use FSi\Component\DataGrid\ColumnType\Boolean;

/**
 * This column should be removed after symfony/property-access allows for safe
 * access for null values.
 */
final class TokenNonExpired extends Boolean
{
    public function getId(): string
    {
        return 'token_non_expired';
    }

    /**
     * @param TokenInterface $value
     * @return bool
     */
    protected function filterValue(ColumnInterface $column, $value)
    {
        if (true === is_array($value)) {
            $value = reset($value);
            if (false === $value) {
                $value = null;
            }
        }

        return parent::filterValue(
            $column,
            null !== $value ? $value->isNonExpired() : false
        );
    }
}
