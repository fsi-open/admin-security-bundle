<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function sprintf;

class UserType extends AbstractType
{
    private bool $displayRolesField;
    /**
     * @var class-string<UserInterface>|null
     */
    private ?string $dataClass;
    /**
     * @var array<array<string>>|null
     */
    private ?array $roles;

    /**
     * @param class-string<UserInterface>|null $dataClass
     * @param array<array<string>>|null $roles
     */
    public function __construct(bool $displayRolesField, ?string $dataClass, ?array $roles)
    {
        $this->displayRolesField = $displayRolesField;
        $this->dataClass = $dataClass;
        $this->roles = $roles;
    }

    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed> $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, ['label' => 'admin.admin_user.email']);

        if (true === $this->displayRolesField) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'admin.admin_user.roles',
                'choices' => $this->getRoleList(),
                'expanded' => true,
                'multiple' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'translation_domain' => 'FSiAdminSecurity',
            'validation_groups' => static fn(FormInterface $form): array
                => ['Default', null !== $form->getData() ? 'Edit' : 'Create']
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getRoleList(): array
    {
        $roleList = [];
        if (null === $this->roles) {
            return $roleList;
        }

        foreach ($this->roles as $role => $child) {
            $label = sprintf('%s [ %s ]', $role, implode(', ', $child));
            $roleList[$label] = $role;
        }

        return $roleList;
    }
}
