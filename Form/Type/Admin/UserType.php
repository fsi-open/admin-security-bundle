<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @var string
     */
    private $dataClass;

    /**
     * @var array<array<string>>
     */
    private $roles;

    /**
     * @param string|null $dataClass
     * @param array<array<string>>|null $roles
     */
    public function __construct(?string $dataClass, ?array $roles)
    {
        $this->dataClass = $dataClass;
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, ['label' => 'admin.admin_user.email']);

        $builder->add('roles', ChoiceType::class, [
            'label' => 'admin.admin_user.roles',
            'choices' => $this->getRoleList(),
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'translation_domain' => 'FSiAdminSecurity',
            'validation_groups' => function (FormInterface $form): array {
                return ['Default', null !== $form->getData() ? 'Edit' : 'Create'];
            }
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getRoleList(): array
    {
        $roleList = [];

        foreach ($this->roles as $role => $child) {
            $label = sprintf('%s [ %s ]', $role, implode(', ', $child));
            $roleList[$label] = $role;
        }

        return $roleList;
    }
}
