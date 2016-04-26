<?php


namespace Arcmedia\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AjaxController
 * @package Arcmedia\CmsBundle\Controller
 */
class AjaxController extends Controller
{
    /**
     * Valida una solicitud ajax tipo GET
     *
     * @param Request $request
     * @throws BadRequestHttpException
     */
    protected function assertAjax(Request $request)
    {
        // Si la llamada no es ajax, terminar
        if (!$request->isXmlHttpRequest()) {
            $message = $this->get('translator')->trans('ERR_INVALID_REQUEST');
            throw new BadRequestHttpException($message);
        }
    }

    /**
     * Valida si la solicitud esta identificada
     *
     * @throws AccessDeniedHttpException
     */
    protected function assertAuthenticated()
    {
        // Si no es un usuario identificado, terminar
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $message = $this->get('translator')->trans('ERR_NO_AUTH');
            throw new AccessDeniedHttpException($message);
        }
    }

    /**
     * Valida si la solicitud es ajax y esta identificada
     *
     * @param Request $request
     * @throws BadRequestHttpException
     * @throws AccessDeniedHttpException
     */
    protected function assertAjaxAuthenticated(Request $request)
    {
        $this->assertAjax($request);
        $this->assertAuthenticated();
    }

    /**
     * Valida el token CSRF, en una solicitud ajax
     *
     * @param Request $request
     * @throws BadRequestHttpException
     */
    protected function assertCsrf(Request $request)
    {
        // El token CSRF no es valido, terminar
        $sessionId = $request->getSession()->getId();
        $token = $request->headers->get('X-CSRFToken', $request->request->get('_csrf_token'));
        if (!$this->isCsrfTokenValid($sessionId, $token)) {
            $message = $this->get('translator')->trans('ERR_TOKEN');
            throw new BadRequestHttpException($message);
        }
    }

    /**
     * Valida una solicitud ajax tipo POST, PUT o DELETE
     *
     * @param Request $request
     * @throws BadRequestHttpException
     * @throws AccessDeniedHttpException
     */
    protected function assertAjaxCsrf(Request $request)
    {
        $this->assertAjax($request);
        $this->assertAuthenticated();
        $this->assertCsrf($request);
    }
}
