<?php

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'repeated', array(
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
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_password_reset_change_password';
    }
}
