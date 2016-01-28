<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordTypeSpec extends ObjectBehavior
{
    function it_is_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     */
    function it_add_fields_during_build($formBuilder)
    {
        $formBuilder->add('current_password', 'password', array(
            'label' => 'admin.change_password_form.current_password',
            'mapped' => false,
            'required' => true,
            'translation_domain' => 'FSiAdminSecurity',
            'constraints' => array(
                new UserPassword(array('message' => 'admin_user.current_password.invalid'))
            )
        ))->shouldBeCalled()->willReturn($formBuilder);

        $formBuilder->add('plainPassword', 'repeated', array(
            'invalid_message' => 'admin_user.password.mismatch',
            'type' => 'password',
            'translation_domain' => 'FSiAdminSecurity',
            'first_options' => array(
                'label' => 'admin.change_password_form.password',
                'required' => true,
                'translation_domain' => 'FSiAdminSecurity',
            ),
            'second_options' => array(
                'label' => 'admin.change_password_form.repeat_password',
                'required' => true,
                'translation_domain' => 'FSiAdminSecurity'
            )
        ))->shouldBeCalled()->willReturn($formBuilder);

        $this->buildForm($formBuilder, array());
    }
}
