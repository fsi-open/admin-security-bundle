<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordTypeSpec extends ObjectBehavior
{
    function it_is_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    function it_add_fields_during_build(FormBuilderInterface $formBuilder)
    {
        $passwordType = TypeSolver::getFormType('Symfony\Component\Form\Extension\Core\Type\PasswordType', 'password');
        $formBuilder->add('current_password', $passwordType, [
            'label' => 'admin.change_password_form.current_password',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new UserPassword(['message' => 'admin_user.current_password.invalid'])
            ]
        ])->shouldBeCalled()->willReturn($formBuilder);

        $repeatedType = TypeSolver::getFormType('Symfony\Component\Form\Extension\Core\Type\RepeatedType', 'repeated');
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
