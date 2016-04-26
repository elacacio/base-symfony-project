<?php

namespace Arcmedia\CmsBundle\Controller;

use Arcmedia\CmsBundle\Helper\Alert;
use Arcmedia\CmsBundle\Manager\ArcmediaManager;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Arcmedia\CmsBundle\Entity\ArcmediaPage;
use Arcmedia\CmsBundle\Form\Type\ArcmediaPageType;
use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArcmediaController extends Controller
{

    /**
     * @Route("/",
     *      name="home",
     *      options={"expose"=true}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        /** @var ArcmediaPage[] $pages */
        $pages = $cms_manager->getActivePages();

        $active_page = null;
        if(count($pages) > 0){
            $active_page = $pages[0];
        }

        return $this->render(
            'ArcmediaCmsBundle:Home:index.html.twig',
            [
                'list_pages' => $pages,
                'title' => $this->get('translator')->trans('TEST_ARCMEDIA'),
                'default_order' => true,
                'active_page' => $active_page,
            ]
        );
    }



    /**
     * @Route("/admin",
     *      name="admin"
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function adminAction(Request $request)
    {
        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        $limit = ArcmediaPage::NUM_ITEMS;
        $page = $request->query->get('page', 1);

        /** @var AdapterInterface $recent_posts_interface */
        $recent_posts_interface = $cms_manager->listPages($limit);

        $pager = new Pagerfanta($recent_posts_interface);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        $pages = $pager->getCurrentPageResults();

        $num_results = $pager->getNbResults();

        return $this->render(
            'ArcmediaCmsBundle:Admin:index.html.twig',
            [
                'title'         => $this->get('translator')->trans('TEST_ARCMEDIA_ADMIN'),
                'list_pages'    => $pages,
                'num_results'   => $num_results,
                'bunch'         => $limit,
                'pager'         => $pager,
            ]
        );
    }


    /**
     * @Route("/admin/new-page",
     *      name="newpage")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newPageAction(Request $request)
    {
        $translator = $this->get('translator');

        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        $page = new ArcmediaPage();

        $form = $this->createForm(
            ArcmediaPageType::class,
            $page,
            [
                'action' => $this->generateUrl('newpage'),
            ]
        );
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $validator_manager = $this->get('page.validator');
            $validator_manager->checkAndCorrectFormPage($form, $page);
        }

        if ($form->isValid()) {

            $slugify = new Slugify();
            $page->setAlias($slugify->slugify($page->getTitle()));

            $page = $cms_manager->persistPage($page);

            if ($page) {
                $this->addFlash(Alert::SUCCESS,$translator->trans('PAGE_CREATED'));
                return $this->redirectToRoute('admin');
            }
            else {
                $this->addFlash(Alert::ERROR, $translator->trans('FORM_ERROR_CREATING'));
            }
        }

        return $this->render(
            'ArcmediaCmsBundle:Page:new_page.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

    }

    /**
     * @Route("/admin/edit-page/{page_id}",
     *      name="editpage")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editPageAction(Request $request, $page_id)
    {
        $translator = $this->get('translator');

        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        if(empty($page_id)){
            $this->addFlash(Alert::ERROR, $translator->trans('UNEXISTING_PAGE'));

            return $this->redirectToRoute('admin');
        }

        /** @var ArcmediaPage $page */
        $page = $cms_manager->find_by(['id' => $page_id]);

        if (empty($page)) {
            $this->addFlash(Alert::ERROR, $translator->trans('UNEXISTING_PAGE'));

            return $this->redirectToRoute('admin');
        }

        $current_title = $page->getTitle();

        $form = $this->createForm(
            ArcmediaPageType::class,
            $page,
            [
                'action' => $this->generateUrl('editpage', ['page_id' => $page_id]),
            ]
        );
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $validator_manager = $this->get('page.validator');
            $validator_manager->checkAndCorrectFormPage($form, $page, $current_title);
        }

        if ($form->isValid()) {

            $slugify = new Slugify();
            $page->setAlias($slugify->slugify($page->getTitle()));

            $page = $cms_manager->persistPage($page);

            if ($page) {
                $this->addFlash(Alert::SUCCESS,$translator->trans('PAGE_EDITED'));
                return $this->redirectToRoute('admin');
            }
            else {
                $this->addFlash(Alert::ERROR, $translator->trans('FORM_ERROR_EDITING'));
            }
        }

        return $this->render(
            'ArcmediaCmsBundle:Page:new_page.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/show/{page_alias}",
     *      name="detail_page",
     *      options={"expose"=true}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request, $page_alias)
    {
        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        /** @var ArcmediaPage[] $pages */
        $pages = $cms_manager->getActivePages();

        $active_page = $cms_manager->find_by(['alias' => $page_alias]);

        return $this->render(
            'ArcmediaCmsBundle:Home:index.html.twig',
            [
                'list_pages' => $pages,
                'title' => $this->get('translator')->trans('TEST_ARCMEDIA'). ' - ' . $active_page->getTitle(),
                'active_page' => $active_page,
            ]
        );
    }
}
