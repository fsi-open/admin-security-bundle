<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_change_password';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', 'password', [
            'label' => 'admin.change_password_form.current_password',
            'mapped' => false,
            'required' => true,
            'translation_domain' => 'FSiAdminSecurity',
            'constraints' => [
                new UserPassword(['message' => 'admin_user.current_password.invalid'])
            ]
        ]);

        $builder->add('plainPassword', 'repeated', [
            'invalid_message' => 'admin_user.password.mismatch',
            'type' => 'password',
            'translation_domain' => 'FSiAdminSecurity',
            'first_options' => [
                'label' => 'admin.change_password_form.password',
                'required' => true,
                'translation_domain' => 'FSiAdminSecurity',
            ],
            'second_options' => [
                'label' => 'admin.change_password_form.repeat_password',
                'required' => true,
                'translation_domain' => 'FSiAdminSecurity'
            ]
        ]);
    }
}
