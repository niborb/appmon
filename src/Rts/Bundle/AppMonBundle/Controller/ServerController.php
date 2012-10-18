<?php

namespace Rts\Bundle\AppMonBundle\Controller;

use Rts\Bundle\AppMonBundle\Entity\App;
use Rts\Bundle\AppMonBundle\Entity\Server;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;

class ServerController extends Controller
{
    /**
     * @Route("/server/post/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template("RtsAppMonBundle:Server:edit.html.twig")
     */
    public function postAction(Server $server = NULL)
    {
        if (!$server instanceof Server) {
            $server = new Server();
        }

        $form = $this->createForm(
            new \Rts\Bundle\AppMonBundle\Form\ServerType(), $server
        );

        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            // save app to db
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($server);
            $em->flush();
            $this->get('session')->setFlash('info', '"' . $server . '" has been updated');
            return $this->redirect($this->generateUrl('rts_appmon_server_list'));
        }

        return array('form' => $form->createView(), 'server' => $server);
    }

    /**
     * delete the server record
     * @Route("/server/delete/{id}", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @param \Rts\Bundle\AppMonBundle\Entity\Server $server
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Server $server)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($server);
        $em->flush();

        $this->get('session')->setFlash('info', '"' . $server . '" has been deleted');

        return $this->redirect($this->generateUrl('rts_appmon_server_list'));
    }

    /**
     * @Route("/server/add", defaults={"id" = NULL})
     * @Secure(roles="ROLE_ADMIN")
     * @Template("RtsAppMonBundle:Server:edit.html.twig")
     * @Method({"GET"})
     */
    public function addAction()
    {
        return $this->editAction(new Server());
    }

    /**
     * @Route("/server/edit/{id}", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     * @Method({"GET"})
     */
    public function editAction(Server $server)
    {
        $form = $this->createForm(
            new \Rts\Bundle\AppMonBundle\Form\ServerType(), $server
        );

        return array('form' => $form->createView(), 'server' => $server);
    }

    /**
     * @return object|\Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->get('logger');
    }

    /**
     * @Route("/server/list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $servers = $em->getRepository('RtsAppMonBundle:Server')->findAll();
        return array('servers' => $servers);
    }
}
