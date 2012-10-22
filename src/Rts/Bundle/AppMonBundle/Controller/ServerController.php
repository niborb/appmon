<?php

namespace Rts\Bundle\AppMonBundle\Controller;

use Rts\Bundle\AppMonBundle\Entity\App;
use Rts\Bundle\AppMonBundle\Entity\Server;
use Rts\Bundle\AppMonBundle\Controller;

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
            $this->getEntityManager()->persist($server);
            $this->getEntityManager()->flush();

            $message = sprintf($this->trans('"%s" has been updated'), $server);
            $this->setFlash('info', $message);

            return $this->redirect($this->generateUrl('rts_appmon_server_list'));
        }

        return array(
            'form'   => $form->createView(),
            'server' => $server
        );
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
        $this->getEntityManager()->remove($server);
        $this->getEntityManager()->flush();

        $message = sprintf($this->trans('"%s" has been deleted'), $server);
        $this->setFlash('info', $message);

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

        return array(
            'form'   => $form->createView(),
            'server' => $server
        );
    }

    /**
     * @Route("/server/list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function listAction()
    {
        $servers = $this->getRepository('RtsAppMonBundle:Server')->findAll();

        return array(
            'servers' => $servers
        );
    }

}
