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
     * @return string
     */
    public function getName()
    {
        return 'admin_change_password';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', 'password', array(
            'label' => 'admin.change_password_form.current_password',
            'translation_domain' => 'FSiAdminSecurity',
            'constraints' => array(
                new UserPassword(array('message' => 'admin.invalid_password'))
            )
        ));

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
}
