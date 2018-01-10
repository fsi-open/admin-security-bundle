<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordType = TypeSolver::getFormType(PasswordType::class, 'password');
        $builder->add('current_password', $passwordType, [
            'label' => 'admin.change_password_form.current_password',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new UserPassword(['message' => 'admin_user.current_password.invalid'])
            ]
        ]);

        $repeatedType = TypeSolver::getFormType(RepeatedType::class, 'repeated');
        $builder->add('plainPassword', $repeatedType, [
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
        return 'admin_change_password';
    }
}
