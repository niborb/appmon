<?php

namespace Rts\Bundle\AppMonBundle\Controller;

use Rts\Bundle\AppMonBundle\Entity\App;
use Rts\Bundle\AppMonBundle\Entity\AppCategory;
use Rts\Bundle\AppMonBundle\Entity\Server;
use Rts\Bundle\AppMonBundle\Controller;
use Rts\Bundle\AppMonBundle\Form\AppType;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{
    /**
     * @Route("/app/post/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template("RtsAppMonBundle:Default:edit.html.twig")
     */
    public function postAction(App $app = NULL)
    {
        if (!$app instanceof App) {
            $app = new App();
        }

        $form = $this->createForm(new AppType(), $app);
        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            // save app to db
            $this->getEntityManager()->persist($app);
            $this->getEntityManager()->flush();

            // get info from app server, and return to list
            $this->updateAction($app);

            $message = sprintf($this->trans('"%s" has been saved'), $app->getName());
            $this->setFlash('info', $message);

            return $this->redirect($this->generateUrl('rts_appmon_default_list'));
        }

        return array(
            'form'        => $form->createView(),
            'application' => $app
        );
    }

    /**
     * Delete the app record which is identified by it's id
     *
     * @Route("/app/delete/{id}", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @param \Rts\Bundle\AppMonBundle\Entity\App $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(App $app)
    {
        $appName = $app->getName();
        $this->getEntityManager()->remove($app);
        $this->getEntityManager()->flush();

        $message = sprintf($this->trans('"%s" has been deleted'), $appName);
        $this->setFlash('info', $message);

        return $this->redirect($this->generateUrl('rts_appmon_default_list'));
    }

    /**
     * @Route("/app/add")
     * @Secure(roles="ROLE_ADMIN")
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
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     * @Method({"GET"})
     * @param \Rts\Bundle\AppMonBundle\Entity\App $app
     * @return array
     */
    public function editAction(App $app)
    {
        $form = $this->createForm(
            new AppType(), $app
        );

        return array(
            'form'        => $form->createView(),
            'application' => $app
        );
    }

    /**
     * @Route("/app/update_all")
     * @Secure(roles="ROLE_USER")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAllAction()
    {
        $apps = $this->getRepository('RtsAppMonBundle:App')->findAll();

        foreach ($apps as $app) {
            $this->updateAction($app);
        }

        return $this->redirect($this->generateUrl('rts_appmon_default_list'));
    }

    /**
     * @Route("/app/update/{id}")
     * @Secure(roles="ROLE_USER")
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

            $this->setFlash('error', sprintf($this->trans('"%s" could not be updated because of unknown error'), $app->getName()));
            $this->redirect(
                $this->generateUrl('rts_appmon_default_list')
            );
        }


        // check if the app is located on a new server

        if (! $app->getServer()) {
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
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($app);

        if (count($errors) > 0) {
            $message = sprintf(
                $this->trans('"%s" could not be updated. Data is not valid'),
                $app->getName());
            $this->setFlash('error', $message);

            $this->getLogger()->notice(
                'Could not update App, because data is not valid',
                $errors
            );
            $this->redirect($this->generateUrl('rts_appmon_default_list'));
        }

        $this->getEntityManager()->persist($app);
        if (isset($server)) {
            $this->getEntityManager()->persist($server);
        }
        $this->getEntityManager()->flush();

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
        $message = sprintf($this->trans('"%s" has been updated'), $app);
        $this->setFlash('info', $message);

        return $this->redirect($this->generateUrl('rts_appmon_default_list'));
    }

    /**
     * Example to return version information in JSON
     *
     * @Route("/appmon/version.{_format}", defaults={"_format" = "json"}, requirements={"_format" = "json"})
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function versionAction()
    {

        $this->getLogger()->info(sprintf('api_key %s', $this->getRequest()->get('api_key')));

        $apiKey = $this->getRequest()->get('api_key');

        // 40f4ec89b29a6acbf49b6dedcea3e8ec == md5('appmon')
        if (NULL === $apiKey || $apiKey != '40f4ec89b29a6acbf49b6dedcea3e8ec') {
            throw $this->createNotFoundException();
        }

        return
            array(
                'response' =>
                array(
                    'name' => 'AppMon',
                    'version' => 'dev',
                    'meta_data_json' => array(
                        "Symfony" => \Symfony\Component\HttpKernel\Kernel::VERSION,
                        "PHP" => PHP_VERSION,
                    )
                )
            );
    }

    /**
     * @Route("/")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function helpAction()
    {
        return array();
    }

    /**
     * TODO: move the search query to the AppRepository class
     *
     * @Route("/app/search.{_format}", defaults={"_format" = "html"}, requirements={"_format" = "html|xml|rdf"})
     *
     * @param string $_format [optional] default = html
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction($_format = 'html')
    {
        $this->isGranted('ROLE_USER');

        $serverId   = $this->getRequest()->get('server');
        $categoryId = $this->getRequest()->get('category');
        $search     = $this->getRequest()->get('search');

        $apps = $this->getRepository('RtsAppMonBundle:App')
            ->getBySearchArguments(
                $serverId,
                $categoryId,
                $search)
        ;

        $this->setFlash('info', sprintf($this->trans('You searched for "%s". %s application(s) found'), $search, count($apps)));

        $response = $this->render(
            'RtsAppMonBundle:Default:list.' . $_format . '.twig',
            array('apps' => $apps)
        );

        return $response;
    }

    /**
     * @Route("/app/{id}/list.by.category.{_format}", requirements={"id" = "\d+", "_format" = "html|xml|rdf"}, defaults={"id" = 0, "_format" = "html"})
     * @Method({"GET"})
     *
     * @param \Rts\Bundle\AppMonBundle\Entity\AppCategory $category
     * @param string $_format [optional] default = html
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listByCategoryAction(AppCategory $category, $_format = 'html')
    {
        $this->isGranted('ROLE_USER');

        $apps = $this->getRepository('RtsAppMonBundle:App')->getByCategoryId($category);

        $response = $this->render(
            'RtsAppMonBundle:Default:list.' . $_format . '.twig',
            array('apps' => $apps)
        );

        return $response;
    }

    /**
     * @Route("/app/{id}/list.{_format}", requirements={"id" = "\d+", "_format" = "html|xml|rdf"}, defaults={"id" = 0, "_format" = "html"})
     * @Route("/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Template()
     *
     * @param \Rts\Bundle\AppMonBundle\Entity\Server $server
     * @param string $_format [optional] default = html
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return array
     */
    public function listAction(Server $server = NULL)
    {
        $this->isGranted('ROLE_USER');

        $apps = $this->getRepository('RtsAppMonBundle:App')->getByServerId($server);

        return array('apps' => $apps);
    }

}
