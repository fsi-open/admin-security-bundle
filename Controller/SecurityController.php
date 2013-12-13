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

class SecurityController
{
    private $csrfProvider;
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param EngineInterface $templating
     * @param \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface $csrfProvider
     */
    function __construct(EngineInterface $templating, CsrfProviderInterface $csrfProvider = null)
    {
        $this->csrfProvider = $csrfProvider;
        $this->templating = $templating;
    }

    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        $error = null;
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error instanceof \Exception ) {
            $error = $error->getMessage();
        }

        $csrfToken = isset($this->csrfProvider)
            ? $this->csrfProvider->generateCsrfToken('authenticate')
            : null;

        return $this->templating->renderResponse('FSiAdminSecurityBundle:Security:login.html.twig', array(
            'error' => $error,
            'csrf_token' => $csrfToken
        ));
    }
}
