<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emailType = TypeSolver::getFormType('Symfony\Component\Form\Extension\Core\Type\EmailType', 'email');
        $builder->add('email', $emailType, [
            'translation_domain' => 'FSiAdminSecurity',
            'label' => 'admin.password_reset.request.form.email'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_password_reset_request';
    }
}
