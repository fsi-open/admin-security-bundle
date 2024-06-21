<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\FixturesBundle\Admin;

use FSi\Bundle\AdminBundle\Doctrine\Admin\ResourceElement;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use FSi\FixturesBundle\Entity\Resource;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @extends ResourceElement<Resource>
 */
class News extends ResourceElement implements SecuredElementInterface
{
    public function isAllowed(AuthorizationCheckerInterface $authorizationChecker): bool
    {
        return $authorizationChecker->isGranted('ROLE_REDACTOR');
    }

    public function getClassName(): string
    {
        return Resource::class;
    }

    public function getId(): string
    {
        return 'news';
    }

    public function getKey(): string
    {
        return 'news';
    }
}
