<?php

namespace Rts\Bundle\AppMonBundle\Controller;

use Rts\Bundle\AppMonBundle\Entity\App;
use Rts\Bundle\AppMonBundle\Entity\AppCategory;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CategoryController extends Controller
{

    /**
     * @Route("/category/post/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Method({"POST"})
     * @Template("RtsAppMonBundle:Category:edit.html.twig")
     */
    public function postAction(AppCategory $category = NULL)
    {
        if (!$category instanceof AppCategory) {
            $category = new AppCategory();
        }

        $form = $this->createForm(
            new \Rts\Bundle\AppMonBundle\Form\AppCategoryType(),
            $category
        );

        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($category);
            $em->flush();

            $this->get('session')->setFlash('info',
                $this->get('translator')->trans(sprintf("%s has been saved",
                    $category)
                )
            );

            return $this->redirect(
                $this->generateUrl('rts_appmon_category_list')
            );
        }

        return array(
            'form' => $form->createView(),
            'category' => $category
        );
    }

    /**
     * @Route("/category/add", defaults={"id" = NULL})
     * @Template("RtsAppMonBundle:Category:edit.html.twig")
     * @Method({"GET"})
     * @return array
     */
    public function addAction()
    {
        return $this->editAction(new AppCategory());
    }

    /**
     * @Route("/category/edit/{id}", requirements={"id" = "\d+"})
     * @Template()
     * @Method({"GET"})
     * @param \Rts\Bundle\AppMonBundle\Entity\AppCategory $category
     * @return array
     */
    public function editAction(AppCategory $category)
    {
        $form = $this->createForm(
            new \Rts\Bundle\AppMonBundle\Form\AppCategoryType(),
            $category
        );

        return array(
            'form' => $form->createView(),
            'category' => $category
        );
    }

    /**
     * delete the app record
     * @Route("/category/delete/{id}", requirements={"id" = "\d+"})
     * @param \Rts\Bundle\AppMonBundle\Entity\AppCategory $category
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(AppCategory $category)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($category);
        $em->flush();

        $this->get('session')->setFlash('info',
            $this->get('translator')->trans(sprintf('"%s" has been deleted', $app)));


        return $this->redirect($this->generateUrl('rts_appmon_category_list'));
    }

    /**
     * @Route("/category/list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $categories = $em->getRepository('RtsAppMonBundle:AppCategory')->findAll();
        return array('categories' => $categories);
    }

}

