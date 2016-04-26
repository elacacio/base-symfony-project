<?php

namespace Arcmedia\CmsBundle\Controller;

use Arcmedia\CmsBundle\Entity\ArcmediaPage;
use Arcmedia\CmsBundle\Manager\ArcmediaManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Arcmedia\CmsBundle\Controller\AjaxController;

class ArcmediaAjaxController extends AjaxController
{
    /**
     * @Route("/admin/activate/{page_id}",
     *     name="activate",
     *     defaults={"_format"="json"},
     *     options={"expose"=true},
     *     methods={"PUT"}
     * )
     *
     * @param int $page_id
     * @param Request $request
     * @throws BadRequestHttpException
     * @return JsonResponse
     */
    public function activateAction(Request $request, $page_id)
    {
        $this->assertAjax($request);

        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        /** @var ArcmediaPage $page */
        $page = $cms_manager->find_by(['id' => $page_id]);

        if(empty($page)){
            $msg = $this->get('translator')->trans('ACTION_NOT_ALLOWED');
            throw new ConflictHttpException($msg);
        }

        $cms_manager->activate($page);

        $html = $this->render(
            'ArcmediaCmsBundle:Ajax:link_active.html.twig'
        );

        return new JsonResponse(['html' => $html->getContent()]);
    }

    /**
     * @Route("/admin/unactivate/{page_id}",
     *     name="unactivate",
     *     defaults={"_format"="json"},
     *     options={"expose"=true},
     *     methods={"PUT"}
     * )
     *
     * @param int $page_id
     * @param Request $request
     * @throws BadRequestHttpException
     * @return JsonResponse
     */
    public function unactivateAction(Request $request, $page_id)
    {
        $this->assertAjax($request);

        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        /** @var ArcmediaPage $page */
        $page = $cms_manager->find_by(['id' => $page_id]);

        if(empty($page)){
            $msg = $this->get('translator')->trans('ACTION_NOT_ALLOWED');
            throw new ConflictHttpException($msg);
        }

        $cms_manager->activate($page, false);

        $html = $this->render(
            'ArcmediaCmsBundle:Ajax:link_inactive.html.twig'
        );

        return new JsonResponse(['html' => $html->getContent()]);
    }


    /**
     * @Route("/admin/delete/{page_id}",
     *     name="delete",
     *     defaults={"_format"="json"},
     *     options={"expose"=true},
     *     methods={"DELETE"}
     * )
     *
     * @param int $page_id
     * @param Request $request
     * @throws ConflictHttpException
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $page_id)
    {
        $this->assertAjax($request);

        /** @var ArcmediaManager $cms_manager */
        $cms_manager = $this->get('cms.manager');

        /** @var ArcmediaPage $page */
        $page = $cms_manager->find_by(['id' => $page_id]);

        if(empty($page)){
            $msg = $this->get('translator')->trans('ACTION_NOT_ALLOWED');
            throw new ConflictHttpException($msg);
        }

        $cms_manager->deletePage($page, false);

        return new JsonResponse(['success' => true]);
    }
}
