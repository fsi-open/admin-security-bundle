<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Page;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;

use function array_filter;
use function array_map;
use function count;

final class UserList extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     * @throws UnexpectedPageException
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);

        if (false === $this->hasElement('table')) {
            throw new UnexpectedPageException('Unable to find users table');
        }
    }

    public function getUsersCount(): int
    {
        $records = $this->getElement('table')->findAll('css', 'tbody > tr');

        return count($records);
    }

    public function getBatchActionsElement(): NodeElement
    {
        return $this->getElement('batch actions');
    }

    /**
     * @return array<string>
     */
    public function getBatchActions(): array
    {
        $options = array_map(
            static fn(NodeElement $item): string => $item->getText(),
            $this->getElement('batch actions')->findAll('css', 'option')
        );

        return array_filter(
            $options,
            static fn(string $item): bool => 'Select action' !== $item
        );
    }

    /**
     * @return array<string, string>
     */
    protected function getDefinedElements(): array
    {
        return [
            'table' => '#admin_security_user',
            'batch actions' => '#batch_action_action',
        ];
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        return $this->getParameter('base_url') . '/admin/en/list/admin_security_user';
    }
}
