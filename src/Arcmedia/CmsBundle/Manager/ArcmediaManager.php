<?php

namespace Arcmedia\CmsBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Exception;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use Arcmedia\CmsBundle\Entity\ArcmediaPage;

class ArcmediaManager
{
    /** @var EntityManager */
    protected $manager;
    /** @var Translator */
    protected $translator;

    public function __construct($manager, $translator)
    {
        $this->manager = $manager;
        $this->translator = $translator;
    }

    /**
     *  Returns Doctrine Adapter to iterate page items.
     *
     * @param int $limit
     * @return DoctrineORMAdapter
     */
    public function listPages($limit = ArcmediaPage::NUM_ITEMS)
    {
        $repository = $this->manager->getRepository('ArcmediaCmsBundle:ArcmediaPage');

        $query = $repository->listPages($limit);

        /** @var DoctrineORMAdapter $adapter */
        $adapter = new DoctrineORMAdapter($query);

        return $adapter;
    }

    /**
     *  Returns all the active pages.
     *
     * @param int $limit
     * @return ArcmediaPage[]|Null
     */
    public function getActivePages($limit = ArcmediaPage::NUM_ITEMS)
    {
        $repository = $this->manager->getRepository('ArcmediaCmsBundle:ArcmediaPage');

        $result = $repository->getActivePages($limit);

        return $result;
    }


    /**
     *  Returns Generic findOneBy any attribute.
     *
     * @param Array $attribute
     * @return ArcmediaPage|Null
     */
    public function find_by($attribute){
        $page = null;

        foreach($attribute as $key => $value){
            /** @var ArcmediaPage $page */
            $page = $this->manager->getRepository('ArcmediaCmsBundle:ArcmediaPage')->findOneBy(
                [$key => $value]
            );
        }

        return $page;
    }

    /**
     * Activate/Deactivate Page
     *
     * @param ArcmediaPage $page
     * @return ArcmediaPage
     */
    public function activate($page, $active = true)
    {
        $page->setActive($active);
        $this->manager->persist($page);
        $this->manager->flush();

        return $page;
    }


    /**
     * Remove Page
     *
     * @param ArcmediaPage $page
     * @throws Exception
     * @return ArcmediaPage
     */
    public function deletePage($page)
    {
        $this->manager->beginTransaction();

        try{
            $this->manager->remove($page);
            $this->manager->flush();

            $repository = $this->manager->getRepository('ArcmediaCmsBundle:ArcmediaPage');

            /** @var ArcmediaPage[] $pages */
            $pages = $repository->getActivePages();

            //Reorder pages
            usort($pages, array($this, "short_order"));
            $this->reorder($pages);
            foreach($pages as $page){
                $this->manager->persist($page);
                $this->manager->flush();
            }
            $this->manager->commit();

        }
        catch (Exception $e) {
            $this->manager->close();
            $this->manager->rollback();

            throw $e;
        }

        return $page;
    }

    /**
     * Add new page
     * @param ArcmediaPage $page
     *
     * @return ArcmediaPage
     * @throws Exception
     */
    public function persistPage($page)
    {
        try {
            $this->manager->persist($page);
            $this->manager->flush();

        } catch (Exception $e) {
            $this->manager->close();

            throw $e;
        }

        return $page;
    }

    /**
     * Returns elements sorted by order fied
     *
     * @param ArcmediaPage $a
     * @param ArcmediaPage $b
     * @return ArcmediaPage[]
     */
    protected function short_order($a, $b)
    {
        return $a->getOrder() > $b->getOrder();
    }

    /**
     * Returns elements sorted by order fied
     *
     * @param ArcmediaPage[] $pages
     * @return ArcmediaPage[]
     */
    private function reorder(&$pages)
    {
        $i = 1;
        foreach($pages as &$page){
            $page->setOrder($i);
            $i++;
        }
    }
}
