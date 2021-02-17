<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminSecurityBundle\Form\Type\Admin\UserType;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;

class UserElementSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([], User::class, UserType::class);
    }

    public function it_has_id(): void
    {
        $this->getId()->shouldReturn('admin_security_user');
    }

    public function it_return_correct_class_name(): void
    {
        $this->getClassName()->shouldReturn(User::class);
    }
}
