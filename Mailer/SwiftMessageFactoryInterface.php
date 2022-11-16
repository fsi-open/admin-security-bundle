<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use Swift_Message;

interface SwiftMessageFactoryInterface
{
    /**
     * @param string $email
     * @param string $template
     * @param array<string, mixed> $data
     * @return Swift_Message
     */
    public function createMessage(string $email, string $template, array $data): Swift_Message;
}
