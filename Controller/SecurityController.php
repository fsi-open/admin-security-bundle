<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
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
     * @param string $loginActionTemplate
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
     */
    function __construct(EngineInterface $templating, $loginActionTemplate, AuthenticationUtils $authenticationUtils)
    {
        $this->templating = $templating;
        $this->loginActionTemplate = $loginActionTemplate;
        $this->authenticationUtils = $authenticationUtils;
    }

    public function loginAction()
    {
        return $this->templating->renderResponse($this->loginActionTemplate, array(
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
            'last_username' => $this->authenticationUtils->getLastUsername()
        ));
    }
}
