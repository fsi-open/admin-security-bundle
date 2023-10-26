<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Command;

use Symfony\Component\Console\Helper;
use Symfony\Component\Console\Helper\HelperInterface;

/**
 * @method HelperInterface getHelper(string $name)
 */
trait QuestionHelper
{
    private function getQuestionHelper(): Helper\QuestionHelper
    {
        /** @var Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');
        return $helper;
    }
}
