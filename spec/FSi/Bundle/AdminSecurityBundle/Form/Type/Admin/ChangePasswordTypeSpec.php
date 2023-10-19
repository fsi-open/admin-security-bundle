<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordTypeSpec extends ObjectBehavior
{
    public function it_is_form_type(): void
    {
        $this->shouldHaveType(AbstractType::class);
    }

    public function it_add_fields_during_build(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add(
            'currentPassword',
            PasswordType::class,
            [
                'label' => 'admin.change_password_form.current_password',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new UserPassword(['message' => 'admin_user.current_password.invalid']),
                ],
            ]
        )->shouldBeCalled()->willReturn($formBuilder);

        $formBuilder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'invalid_message' => 'admin_user.password.mismatch',
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'admin.change_password_form.password',
                    'required' => true,
                ],
                'second_options' => [
                    'label' => 'admin.change_password_form.repeat_password',
                    'required' => true,
                ],
            ]
        )->shouldBeCalled()->willReturn($formBuilder);

        $this->buildForm($formBuilder, []);
    }
}
