<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Controller\Activation;

use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @property UserRepositoryInterface $userRepository
 * @property ClockInterface $clock
 */
trait ActivableUser
{
    /**
     * @return ActivableInterface&ChangeablePasswordInterface
     */
    private function tryFindUserByActivationToken(string $token): ActivableInterface
    {
        $user = $this->userRepository->findUserByActivationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException();
        }

        if (true === $user->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $activationToken = $user->getActivationToken();
        if (null === $activationToken) {
            throw new NotFoundHttpException();
        }

        if (false === $activationToken->isNonExpired($this->clock)) {
            throw new NotFoundHttpException();
        }

        return $user;
    }

    private function isUserEnforcedToChangePassword(ActivableInterface $user): bool
    {
        return true === $user instanceof EnforceablePasswordChangeInterface
            && true === $user->isForcedToChangePassword()
        ;
    }
}
