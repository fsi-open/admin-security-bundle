<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Page;

use RuntimeException;

final class Activation extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     */
    public function openWithoutVerification(array $urlParameters): void
    {
        $url = $this->getUrl($urlParameters);
        $this->getSession()->visit($url);
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        $activationToken = $urlParameters['activationToken'] ?? null;
        if (null === $activationToken || '' === $activationToken) {
            throw new RuntimeException(sprintf(
                'No activation token for current page "%s"',
                $this->getDriver()->getCurrentUrl()
            ));
        }

        return $this->getParameter('base_url') . "/admin/activation/activate/{$activationToken}";
    }
}
