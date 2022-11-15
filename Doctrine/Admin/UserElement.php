<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminBundle\Doctrine\Admin\CRUDElement;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Component\DataGrid\DataGridFactoryInterface;
use FSi\Component\DataGrid\DataGridInterface;
use FSi\Component\DataSource\DataSourceFactoryInterface;
use FSi\Component\DataSource\DataSourceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @extends CRUDElement<UserInterface>
 */
class UserElement extends CRUDElement
{
    /**
     * @var class-string<UserInterface>
     */
    private $userModel;

    /**
     * @var class-string<FormInterface<FormInterface>>
     */
    private $formClass;

    /**
     * @param array<string, mixed> $options
     * @param class-string<UserInterface> $userModel
     * @param class-string<FormInterface<FormInterface>> $formClass
     */
    public function __construct(array $options, string $userModel, string $formClass)
    {
        parent::__construct($options);

        $this->userModel = $userModel;
        $this->formClass = $formClass;
    }

    public function getId(): string
    {
        return 'admin_security_user';
    }

    public function getClassName(): string
    {
        return $this->userModel;
    }

    protected function initDataGrid(DataGridFactoryInterface $factory): DataGridInterface
    {
        return $factory->createDataGrid('admin_security_user');
    }

    protected function initDataSource(DataSourceFactoryInterface $factory): DataSourceInterface
    {
        return $factory->createDataSource(
            'doctrine-orm',
            ['entity' => $this->getClassName()],
            $this->getId()
        )->setMaxResults(20);
    }

    protected function initForm(FormFactoryInterface $factory, $data = null): FormInterface
    {
        return $factory->create($this->formClass, $data);
    }
}
