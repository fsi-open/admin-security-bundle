<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use Twig_Template;

class Template extends Twig_Template
{
    protected function doDisplay(array $context, array $blocks = [])
    {
    }

    public function getTemplateName()
    {
    }

    public function getDebugInfo()
    {
    }
}
