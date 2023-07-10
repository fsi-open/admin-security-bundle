<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Element;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Element\Element;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;

final class BatchAction extends Element
{
    public function findOptionForAction(string $action): NodeElement
    {
        $option = $this->getElement('batch_actions')->find('css', "option:contains(\"{$action}\")");
        if (null === $option) {
            throw new UnexpectedPageException("Cannot find batch action \"{$action}\".");
        }

        return $option;
    }

    public function getCsrfToken(): string
    {
        $token = $this->getElement('batch_token')->getAttribute('value');
        if (null === $token) {
            throw new UnexpectedPageException('No CSRF token for batch actions form.');
        }

        return $token;
    }

    /**
     * @return array<string, string>
     */
    protected function getDefinedElements(): array
    {
        return [
            'batch_actions' => '#batch_action_action',
            'batch_token' => '#batch_action__token'
        ];
    }
}
