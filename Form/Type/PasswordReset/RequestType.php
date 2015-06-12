<?php

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', array());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_password_reset_request';
    }
}
