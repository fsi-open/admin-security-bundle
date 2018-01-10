<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\FixturesBundle\Admin;

use FSi\Bundle\AdminBundle\Doctrine\Admin\CRUDElement;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use FSi\Component\DataGrid\DataGridFactoryInterface;
use FSi\Component\DataGrid\DataGridInterface;
use FSi\Component\DataSource\DataSourceFactoryInterface;
use FSi\Component\DataSource\DataSourceInterface;
use FSi\FixturesBundle\Entity\PageSettings as PageSettingsEntity;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PageSettings extends CRUDElement implements SecuredElementInterface
{
    public function isAllowed(AuthorizationCheckerInterface $authorizationChecker)
    {
        return $authorizationChecker->isGranted('ROLE_ADMIN');
    }

    public function getClassName(): string
    {
        return PageSettingsEntity::class;
    }

    public function getId(): string
    {
        return 'page_settings';
    }

    protected function initDataGrid(DataGridFactoryInterface $factory): DataGridInterface
    {
        return $factory->createDataGrid($this->getId());
    }

    protected function initDataSource(DataSourceFactoryInterface $factory): DataSourceInterface
    {
        return $factory->createDataSource(
            'doctrine-orm',
            ['entity' => $this->getClassName()],
            $this->getId()
        );
    }

    protected function initForm(FormFactoryInterface $factory, $data = null): FormInterface
    {
        return $factory->create(TypeSolver::getFormType(FormType::class, 'form'), $data);
    }
}
