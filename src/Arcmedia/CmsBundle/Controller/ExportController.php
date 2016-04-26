<?php

namespace Arcmedia\CmsBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Arcmedia\CmsBundle\Entity\ArcmediaPage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{

    /**
     * @Route("/admin/export",
     *      name="export")
     *
     * @return Response
     */
    public function exportAction()
    {
        // get the service container to pass to the closure
        $container = $this->container;
        $response = new StreamedResponse(function() use($container) {
            $em = $container->get('doctrine')->getManager();

            // The getExportQuery method returns a query that is used to retrieve
            // all the objects. The iterate method is used to limit the memory consumption
            $repository = $em->getRepository('ArcmediaCmsBundle:ArcmediaPage');
            $results = $repository->listPages()->iterate();
            $handle = fopen('php://output', 'r+');

            fputcsv($handle, ['Id', 'Order', 'Title', 'Alias', 'Content']);

            while (false !== ($row = $results->next())) {
                /** @var ArcmediaPage $page */
                $page = $row[0];
                fputcsv($handle, [$page->getId(), $page->getOrder(), $page->getTitle(), $page->getAlias(), $page->getContent()]);
                // used to limit the memory consumption
                $em->detach($row[0]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition','attachment; filename="export.csv"');

        return $response;
    }
}
