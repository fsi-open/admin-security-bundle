<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordTypeSpec extends ObjectBehavior
{
    function it_is_form_type()
    {
        $this->shouldHaveType(AbstractType::class);
    }

    function it_add_fields_during_build(FormBuilderInterface $formBuilder)
    {
        $passwordType = TypeSolver::getFormType(PasswordType::class, 'password');
        $formBuilder->add('current_password', $passwordType, [
            'label' => 'admin.change_password_form.current_password',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new UserPassword(['message' => 'admin_user.current_password.invalid'])
            ]
        ])->shouldBeCalled()->willReturn($formBuilder);

        $repeatedType = TypeSolver::getFormType(RepeatedType::class, 'repeated');
        $formBuilder->add('plainPassword', $repeatedType, [
            'invalid_message' => 'admin_user.password.mismatch',
            'type' => $passwordType,
            'first_options' => [
                'label' => 'admin.change_password_form.password',
                'required' => true
            ],
            'second_options' => [
                'label' => 'admin.change_password_form.repeat_password',
                'required' => true
            ]
        ])->shouldBeCalled()->willReturn($formBuilder);

        $this->buildForm($formBuilder, []);
    }
}
