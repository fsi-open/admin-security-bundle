<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordTypeSpec extends ObjectBehavior
{
    function it_is_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    function it_add_fields_during_build(FormBuilder $formBuilder)
    {
        $formBuilder->add('current_password', 'password', array(
            'label' => 'admin.change_password_form.current_password',
            'translation_domain' => 'FSiAdminSecurity',
            'constraints' => array(
                new UserPassword(array('message' => 'admin.invalid_password'))
            )
        ))->shouldBeCalled()->willReturn($formBuilder);

        $formBuilder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'translation_domain' => 'FSiAdminSecurity',
            'first_options' => array(
                'label' => 'admin.change_password_form.password',
                'translation_domain' => 'FSiAdminSecurity',
            ),
            'second_options' => array(
                'label' => 'admin.change_password_form.repeat_password',
                'translation_domain' => 'FSiAdminSecurity'
            )
        ))->shouldBeCalled()->willReturn($formBuilder);

        $this->buildForm($formBuilder, array());
    }
}
