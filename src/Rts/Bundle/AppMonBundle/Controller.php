<?php

namespace Rts\Bundle\AppMonBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class Controller extends BaseController
{

    /**
     * Checks if passed role is granted, if not throws an
     * AccessDeniedException
     *
     * @param string $role
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return void
     */
    protected function isGranted($role)
    {
        $format = $this->getRequest()->getRequestFormat();

        if ($format == 'html') {
            $securityContext = $this->get('security.context');
            if (false === $securityContext->isGranted($role)) {
                throw new AccessDeniedException();
            }
        }
    }

    /**
     * Gets a named entity manager.
     *
     * @param string $name The entity manager name (null for the default one)
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($name = null)
    {
        return $this->getDoctrine()->getEntityManager($name);
    }

    /**
     * Gets the repository for an entity class.
     *
     * @param string $entityName The name of the entity.
     * @return \Doctrine\ORM\EntityRepository The repository class.
     */
    protected function getRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
    }

    /**
     * Sets a flash message.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    protected function setFlash($name, $value)
    {
        if ($this->container->has('session')) {
            $this->get('session')->setFlash($name, $value);
        }
    }

    protected function trans($value)
    {
        if ($this->container->has('translator')) {
            $value = $this->get('translator')->trans($value);
        }

        return $value;
    }

    /**
     * @return object|\Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->get('logger');
    }

}
