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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    public function getName()
    {
        return 'admin_user';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', [
            'label' => 'admin.admin_user.email',
            'translation_domain' => 'FSiAdminSecurity',
        ]);

        $builder->add('roles', 'choice', [
            'label' => 'admin.admin_user.roles',
            'translation_domain' => 'FSiAdminSecurity',
            'choices' => $this->getRoleList(),
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', $this->dataClass);
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

        return $roleList;
    }
}
