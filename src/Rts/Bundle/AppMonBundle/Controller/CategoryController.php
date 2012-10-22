<?php

namespace Rts\Bundle\AppMonBundle\Controller;

use Rts\Bundle\AppMonBundle\Entity\App;
use Rts\Bundle\AppMonBundle\Entity\AppCategory;
use Rts\Bundle\AppMonBundle\Form\AppCategoryType;
use Rts\Bundle\AppMonBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;

class CategoryController extends Controller
{

    /**
     * @Route("/category/post/{id}", requirements={"id" = "\d+"}, defaults={"id" = NULL})
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template("RtsAppMonBundle:Category:edit.html.twig")
     */
    public function postAction(AppCategory $category = NULL)
    {
        if (!$category instanceof AppCategory) {
            $category = new AppCategory();
        }

        $form = $this->createForm(
            new AppCategoryType(),
            $category
        );

        $form->bindRequest($this->getRequest());
        if ($form->isValid()) {
            $this->getEntityManager()->persist($category);
            $this->getEntityManager()->flush();

            $message = sprintf(
                $this->trans('%s has been saved'),
                $category->getName()
            );
            $this->setFlash('info', $message);

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
     * @Route("/category/add")
     * @Secure(roles="ROLE_ADMIN")
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
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     * @Method({"GET"})
     * @param \Rts\Bundle\AppMonBundle\Entity\AppCategory $category
     * @return array
     */
    public function editAction(AppCategory $category)
    {
        $form = $this->createForm(
            new AppCategoryType(),
            $category
        );

        return array(
            'form'     => $form->createView(),
            'category' => $category
        );
    }

    /**
     * @Route("/category/delete/{id}", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @param \Rts\Bundle\AppMonBundle\Entity\AppCategory $category
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(AppCategory $category)
    {
        $categoryName = $category->getName();

        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();

        $message = sprintf(
            $this->trans('"%s" has been deleted'),
            $categoryName
        );
        $this->setFlash('info', $message);

        return $this->redirect($this->generateUrl('rts_appmon_category_list'));
    }

    /**
     * @Route("/category/list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function listAction()
    {
        $categories = $this->getRepository('RtsAppMonBundle:AppCategory')
            ->findAll();

        return array(
            'categories' => $categories
        );
    }

}
