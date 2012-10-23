<?php

namespace Rts\Bundle\UserBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listener class which will be registered as service and
 * will hook up on the kernel.event_listener and the event
 * kernel.request, will check if the client IP is white listed
 */
class RequestListener
{

    /**
     * @var array The list of white listed IP Addresses
     */
    protected $whitelistIps;

    /**
     * C'tor
     *
     * @param array $whiteListIps [optional] The list of white listed IP Addresses
     */
    public function __construct(array $whiteListIps = array())
    {
        $this->whitelistIps = $whiteListIps;
    }

    /**
     * When the binded event will be triggerd this method
     * will be called and the GetResponseEvent will be injected.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $event->getRequest();

        $urlPath = $request->getPathInfo();
        if ($urlPath == '/login') {
            return;
        }

        $clientIP = $this->getClientIpAddress($request);
        if (!$this->isValidIpAddress($clientIP)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * If no whitelist is defined every IP will be
     * considered as valid.
     *
     * Check if IP is white listed.
     *
     * @param string $ipAddress
     * @return bool
     */
    protected function isValidIpAddress($ipAddress)
    {
        if (empty($this->whitelistIps)) {
            return true;
        }
        return (in_array($ipAddress, $this->whitelistIps));
    }

    /**
     * Returns the client IP address, try to find
     *
     * @param Request $request
     * @return string
     */
    protected function getClientIpAddress(Request $request)
    {
        $clientIP = $request->getClientIp(true);
        if (empty($clientIP)) {
            $clientIP = $request->getClientIp(false);
        }

        return $clientIP;
    }

}
