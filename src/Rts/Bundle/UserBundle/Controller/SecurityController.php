<?php

namespace Rts\Bundle\UserBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class SecurityController extends BaseController
{
    public function loginAction()
    {
        $request = $this->container->get('request');
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /** @var $session \Symfony\Component\HttpFoundation\Session */

        /**
         * Replaces the original authentication error with a new exception object with
         * Always the same error message, this for security reasons.
         */
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
            $exception = new BadCredentialsException('Invalid username or password', 0, $error);
            $request->attributes->set(SecurityContext::AUTHENTICATION_ERROR, $exception);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $exception = new BadCredentialsException('Invalid username or password', 0, $error);
            $session->set(SecurityContext::AUTHENTICATION_ERROR, $exception);
        }

        return parent::loginAction();
    }

}
