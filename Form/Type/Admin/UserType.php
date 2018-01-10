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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    /**
     * @var string
     */
    private $dataClass;

    /**
     * @var array
     */
    private $roles;

    public function __construct($dataClass, $roles)
    {
        $this->dataClass = $dataClass;
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $emailType = TypeSolver::getFormType(EmailType::class, 'email');
        $builder->add('email', $emailType, ['label' => 'admin.admin_user.email']);

        $choiceType = TypeSolver::getFormType(ChoiceType::class, 'choice');
        $builder->add('roles', $choiceType, [
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
            'translation_domain' => 'FSiAdminSecurity'
        ]);
    }

    public function getName(): string
    {
        return 'admin_user';
    }

    private function getRoleList(): array
    {
        $roleList = [];

        foreach ($this->roles as $role => $child) {
            $roleList[$role] = sprintf('%s [ %s ]', $role, implode(', ', $child));
        }

        if (TypeSolver::isChoicesAsValuesOptionTrueByDefault()) {
            $roleList = array_flip($roleList);
        }

        return $roleList;
    }
}
