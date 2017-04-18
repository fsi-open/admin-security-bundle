<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var string
     */
    private $loginActionTemplate;

    /**
     * @param EngineInterface $templating
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
     * @param string $loginActionTemplate
     */
    function __construct(EngineInterface $templating, AuthenticationUtils $authenticationUtils, $loginActionTemplate)
    {
        $this->templating = $templating;
        $this->authenticationUtils = $authenticationUtils;
        $this->loginActionTemplate = $loginActionTemplate;
    }

    public function loginAction()
    {
        return $this->templating->renderResponse($this->loginActionTemplate, [
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
            'last_username' => $this->authenticationUtils->getLastUsername()
        ]);
    }
}
