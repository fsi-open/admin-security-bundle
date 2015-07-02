<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Doctrine;

use Doctrine\ORM\EntityRepository;
use FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($confirmationToken)
    {
        return $this->findOneBy(['confirmationToken' => $confirmationToken]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        // FIXME: there is no email in any of model interfaces supplied by AdminSecurityBundle
        return $this->findOneBy(['email' => $email]);
    }

    public function save(UserInterface $user, $flush = true)
    {
        $this->_em->persist($user);

        if ($flush) {
            $this->_em->flush($user);
        }
    }
}
