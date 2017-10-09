<?php

namespace FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminBundle\Doctrine\Admin\CRUDElement;
use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use FSi\Component\DataGrid\DataGridFactoryInterface;
use FSi\Component\DataGrid\DataGridInterface;
use FSi\Component\DataSource\DataSourceFactoryInterface;
use FSi\Component\DataSource\DataSourceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use FSi\Bundle\AdminSecurityBundle\Form\Type\Admin\UserType;

class UserElement extends CRUDElement
{
    /**
     * @var string
     */
    private $userModel;

    public function __construct($options, $userModel)
    {
        parent::__construct($options);
        $this->userModel = $userModel;
    }

    /**
     * {@inheritdoc}
     */
    protected function initDataGrid(DataGridFactoryInterface $factory): DataGridInterface
    {
        return $factory->createDataGrid('admin_security_user');
    }

    /**
     * {@inheritdoc}
     */
    protected function initDataSource(DataSourceFactoryInterface $factory): DataSourceInterface
    {
        return $factory->createDataSource('doctrine-orm', ['entity' => $this->getClassName()])->setMaxResults(20);
    }

    /**
     * {@inheritdoc}
     */
    protected function initForm(FormFactoryInterface $factory, $data = null): FormInterface
    {
        $formType = TypeSolver::getFormType(UserType::class, 'admin_user');
        return $factory->create($formType, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return 'admin_security_user';
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->userModel;
    }
}
