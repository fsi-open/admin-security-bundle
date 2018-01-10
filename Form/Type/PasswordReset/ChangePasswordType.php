<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordType = TypeSolver::getFormType(PasswordType::class, 'password');
        $repeatedType = TypeSolver::getFormType(RepeatedType::class, 'repeated');
        $builder->add('plainPassword', $repeatedType, [
            'invalid_message' => 'admin_user.password.mismatch',
            'type' => $passwordType,
            'first_options' => [
                'label' => 'admin.password_reset.change_password.form.password'
            ],
            'second_options' => [
                'label' => 'admin.password_reset.change_password.form.repeat_password'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'FSiAdminSecurity'
        ]);
    }

    public function getName(): string
    {
        return 'admin_password_reset_change_password';
    }
}
