<?php

namespace FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminBundle\Doctrine\Admin\CRUDElement;
use FSi\Component\DataGrid\DataGridFactoryInterface;
use FSi\Component\DataSource\DataSourceFactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;

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
    protected function initDataGrid(DataGridFactoryInterface $factory)
    {
        return $factory->createDataGrid('admin_security_user');
    }

    /**
     * {@inheritdoc}
     */
    protected function initDataSource(DataSourceFactoryInterface $factory)
    {
        return $factory->createDataSource('doctrine', array('entity' => $this->getClassName()))->setMaxResults(20);
    }

    /**
     * {@inheritdoc}
     */
    protected function initForm(FormFactoryInterface $factory, $data = null)
    {
        return $factory->create('admin_user', $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'admin_security_user';
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->userModel;
    }
}
