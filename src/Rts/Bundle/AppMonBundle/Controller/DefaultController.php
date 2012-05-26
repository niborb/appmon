<?php

namespace Rts\Bundle\AppMonBundle\Controller;

use Rts\Bundle\AppMonBundle\Entity\App;
use Rts\Bundle\AppMonBundle\Entity\Server;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DefaultController extends Controller
{
    /**
     * @Route("/app/post/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Method({"POST"})
     * @Template("RtsAppMonBundle:Default:edit.html.twig")
     */
    public function postAction(App $app = NULL)
    {
        if (!$app instanceof App) {
            $app = new App();
        }

        $form = $this->createForm(
            new \Rts\Bundle\AppMonBundle\Form\AppType(), $app
        );

        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            // save app to db
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($app);
            $em->flush();

            // get info from app server, and return to list
            $this->updateAction($app);

            $this->get('session')->setFlash('info',
                $this->get('translator')->trans(sprintf('"%s" has been saved', $app)));

            return $this->redirect($this->generateUrl('rts_appmon_default_list'));
        }

        return array('form' => $form->createView(), 'application' => $app);
    }

    /**
     * delete the app record
     * @Route("/app/delete/{id}", requirements={"id" = "\d+"})
     * @param \Rts\Bundle\AppMonBundle\Entity\App $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(App $app)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($app);
        $em->flush();

        $this->get('session')->setFlash('info',
            $this->get('translator')->trans(sprintf('"%s" has been deleted', $app)));


        return $this->redirect($this->generateUrl('rts_appmon_default_list'));
    }

    /**
     * @Route("/app/add", defaults={"id" = NULL})
     * @Template("RtsAppMonBundle:Default:edit.html.twig")
     * @Method({"GET"})
     * @return array
     */
    public function addAction()
    {
        return $this->editAction(new App());
    }

    /**
     * @Route("/app/edit/{id}", requirements={"id" = "\d+"})
     * @Template()
     * @Method({"GET"})
     * @param \Rts\Bundle\AppMonBundle\Entity\App $app
     * @return array
     */
    public function editAction(App $app)
    {
        $form = $this->createForm(
            new \Rts\Bundle\AppMonBundle\Form\AppType(), $app
        );

        return array('form' => $form->createView(), 'application' => $app);
    }

    /**
     * @Route("/app/update_all")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAllAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('RtsAppMonBundle:App');
        $apps = $repository->findAll();

        foreach ($apps as $app) {
            $this->updateAction($app);
        }

        return $this->redirect($this->generateUrl('rts_appmon_default_list'));
    }

    /**
     * @Route("/app/update/{id}")
     * @Method({"GET"})
     * @param \Rts\Bundle\AppMonBundle\Entity\App $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(\Rts\Bundle\AppMonBundle\Entity\App $app)
    {
        try {
            $app->updateFromServer();
        } catch (\Exception $e) {
            $this->getLogger()->notice('Could not update App data from server because ' . $e->getMessage());
            $this->getLogger()->notice($e);
        }


        // check if the app is located on a new server
        $serverIpAddress = $app->getServerIpAddressByUrl($app->getApiUrl());

        $server = $this->getDoctrine()
            ->getRepository('RtsAppMonBundle:Server')
            ->findOneBy(array('ip_address' => $serverIpAddress));
        if (!$server instanceof Server) {
            $server = new Server();
            $serverHostname = $app->getServerHostnameByUrl($app->getApiUrl());
            $server->setHostname($serverHostname);
            $server->setIpAddress($serverIpAddress);
        }

        $app->setServer($server);

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($app);
        $em->persist($server);
        $em->flush();

        // if called with ajax, return json response
        if ($this->getRequest()->isXmlHttpRequest()) {
            $response = new Response(
                json_encode(
                    array(
                        'success' => true,
                        'record' => $app->toArray()
                    ))
            );
            $response->headers->set('Content-type', 'text/json');
            return $response;
        }

        $this->get('session')->setFlash('info', '"' . $app . '" has been updated');

        return $this->redirect($this->generateUrl('rts_appmon_default_list'));
    }

    /**
     * @return object|\Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->get('logger');
    }

    /**
     * @Route("/app/search")
     * @Template("RtsAppMonBundle:Default:list.html.twig")
     * @param $search
     * @return array
     */
    public function searchAction()
    {
        $search = $this->getRequest()->get('search');
        $em = $this->getDoctrine()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('a, s')
            ->from('RtsAppMonBundle:App', 'a')
            ->join('a.server', 's')
            ->where(
            $qb->expr()->like('s.hostname', ':search')
        )->orWhere(
            $qb->expr()->like('s.ip_address', ':search')
        )->orWhere(
            $qb->expr()->like('s.description', ':search')
        )->orWhere(
            $qb->expr()->like('a.name', ':search')
        )->orWhere(
            $qb->expr()->like('a.meta_data_json', ':search')
        )
            ->orderBy('s.hostname', 'ASC')
            ->setParameter('search', "%$search%");

        $apps = $qb->getQuery()->getResult();

        $this->get('session')->setFlash('info', sprintf(
            $this->get('translator')->trans('You searched for "%s". %s application(s) found'),
            $search, count($apps)));

        return array('apps' => $apps);
    }

    /**
     * @Route("/appmon/version/")
     */
    public function versionAction()
    {
        $response = new Response(
            json_encode(
                array(
                    'name' => 'AppMon',
                    'description' => 'AppMon - monitors application versioning information',
                    'version' => '0.0.1',
                    'meta_data_json' => array(
                        "Symfony" => "2.0.14-DEV",
                        "PHP" => "5.3.9-ZS5.6.0",
                        "MySQL" => "5.1.54"
                    )
                ))
        );
        $response->headers->set('Content-type', 'text/json');
        return $response;

    }

    /**
     * @Route("/")
     * @Template()
     */
    public function helpAction()
    {
        return array();
    }

    /**
     * @Route("/app/list/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Route("/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Template()
     */
    public function listAction(Server $server = NULL)
    {
        if ($server instanceof Server) {
            $serverId = $server->getId();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select('a, s')
            ->from('RtsAppMonBundle:App', 'a')
            ->join('a.server', 's')
            ->orderBy('s.hostname', 'ASC');

        if (isset($serverId)) {
            $qb->where('s.id = :serverId')
                ->setParameter('serverId', $serverId);
        }

        $apps = $qb->getQuery()->getResult();

        return array('apps' => $apps);
    }
}

