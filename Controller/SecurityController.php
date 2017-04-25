<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
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
     * @var FlashMessages
     */
    private $flashMessages;

    /**
     * @var string
     */
    private $loginActionTemplate;

    /**
     * @param EngineInterface $templating
     * @param AuthenticationUtils $authenticationUtils
     * @param FlashMessages $flashMessages
     * @param string $loginActionTemplate
     */
    public function __construct(
        EngineInterface $templating,
        AuthenticationUtils $authenticationUtils,
        FlashMessages $flashMessages,
        $loginActionTemplate
    ) {
        $this->templating = $templating;
        $this->authenticationUtils = $authenticationUtils;
        $this->flashMessages = $flashMessages;
        $this->loginActionTemplate = $loginActionTemplate;
    }

    public function loginAction()
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->flashMessages->error(
                $error->getMessageKey(),
                'security',
                $error->getMessageData()
            );
        }

        return $this->templating->renderResponse(
            $this->loginActionTemplate,
            ['last_username' => $this->authenticationUtils->getLastUsername()]
        );
    }
}
