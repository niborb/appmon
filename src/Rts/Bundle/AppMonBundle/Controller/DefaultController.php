<?php

namespace Rts\Bundle\AppMonBundle\Controller;

use Rts\Bundle\AppMonBundle\Entity\App;
use Rts\Bundle\AppMonBundle\Entity\AppCategory;
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

        $validator = $this->get('validator');
        $errors = $validator->validate($app);

        if (count($errors) > 0) {
            $this->get('session')->setFlash('error', '"' . $app . '" could not be updated. Data is not valid');
            $this->getLogger()->notice('Could not update App, because data is not valid', $errors);
            $this->redirect(
                $this->generateUrl('rts_appmon_default_list')
            );
        }

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
     * @Route("/app/search.{_format}", defaults={"_format" = "html"}, requirements={"_format" = "html|xml|rdf"})
     * @param $search
     * @return array
     */
    public function searchAction()
    {
        $search = $this->getRequest()->get('search');
        $em = $this->getDoctrine()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('a, s, c')
            ->from('RtsAppMonBundle:App', 'a')
            ->join('a.server', 's')
            ->leftJoin('a.category', 'c')
            ->where(
            $qb->expr()->like('s.hostname', ':search')
        )->orWhere(
            $qb->expr()->like('s.ip_address', ':search')
        )->orWhere(
            $qb->expr()->like('a.name', ':search')
        )->orWhere(
            $qb->expr()->like('a.meta_data_json', ':search')
        )->orWhere(
            $qb->expr()->like('c.name', ':search')
        )
            ->orderBy('s.hostname', 'ASC')
            ->setParameter('search', "%$search%");

        $apps = $qb->getQuery()->getResult();

        $this->get('session')->setFlash('info', sprintf(
            $this->get('translator')->trans('You searched for "%s". %s application(s) found'),
            $search, count($apps)));

        $format = $this->getRequest()->getRequestFormat();
        
        $response = $this->render(
            'RtsAppMonBundle:Default:list.' . $format . '.twig',
            array('apps' => $apps)
        );
        
        return $response;
    }

    /**
     * @Route("/appmon/version.{_format}", defaults={"_format" = "json"}, requirements={"_format" = "json"})
     * @Template()
     */
    public function versionAction()
    {
        return
            array(
                'response' =>
                array(
                    'name' => 'AppMon',
                    'version' => '0.0.1',
                    'meta_data_json' => array(
                        "Symfony" => "2.0.14-DEV",
                        "PHP" => PHP_VERSION,
                        "MySQL" => "5.1.54"
                    )
                )
            );
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
     * @Route("/app/{id}/list.by.category.{_format}", requirements={"id" = "\d+", "_format" = "html|xml|rdf"}, defaults={"id" = 0, "_format" = "html"})
     * @Method({"GET"})
     */
    public function listByCategoryAction(AppCategory $category)
    {
        $apps = $this->getDoctrine()->getRepository('RtsAppMonBundle:App')
            ->findBy(array('category' => $category->getId()));


        $format = $this->getRequest()->getRequestFormat();
        $response = $this->render(
            'RtsAppMonBundle:Default:list.' . $format . '.twig',
            array('apps' => $apps)
        );

        return $response;
    }

    /**
     * @Route("/app/{id}/list.{_format}", requirements={"id" = "\d+", "_format" = "html|xml|rdf"}, defaults={"id" = 0, "_format" = "html"})
     * @Route("/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
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

        $format = $this->getRequest()->getRequestFormat();
        $response = $this->render(
        	'RtsAppMonBundle:Default:list.' . $format . '.twig',
        	array('apps' => $apps)
        );
        
        return $response;
    }
}

