<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Form\Type\Admin;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emailType = TypeSolver::getFormType('Symfony\Component\Form\Extension\Core\Type\EmailType', 'email');
        $builder->add('email', $emailType, ['label' => 'admin.admin_user.email']);

        $choiceType = TypeSolver::getFormType('Symfony\Component\Form\Extension\Core\Type\ChoiceType', 'choice');
        $builder->add('roles', $choiceType, [
            'label' => 'admin.admin_user.roles',
            'choices' => $this->getRoleList(),
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'translation_domain' => 'FSiAdminSecurity'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_user';
    }

    /**
     * @return array
     */
    private function getRoleList()
    {
        $roleList = [];

        foreach ($this->roles as $role => $child) {
            $roleList[$role] = sprintf('%s [ %s ]', $role, join(', ', $child));
        }

        if (TypeSolver::isChoicesAsValuesOptionTrueByDefault()) {
            $roleList = array_flip($roleList);
        }

        return $roleList;
    }
}
