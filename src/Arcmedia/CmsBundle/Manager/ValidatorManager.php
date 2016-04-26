<?php

namespace Arcmedia\CmsBundle\Manager;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Arcmedia\CmsBundle\Entity\ArcmediaPage;

class ValidatorManager
{
    const ALLOWED_TAGS = '<p><hr><ol><ul><li><strong><em><u><s>';

    /** @var EntityManager */
    protected $manager;

    /** @var Translator */
    protected $translator;

    public function __construct($manager, $translator)
    {
        $this->manager = $manager;
        $this->translator = $translator;
    }

    /*
     * Check and set errors
     *
     * @param Form $form
     * @param ArcmediaPage $page
     * @param string $current_title
     *
     * return Form
     * */
    public function checkAndCorrectFormPage(Form &$form, ArcmediaPage &$page, $current_title = '')
    {
        $translator = $this->translator;
        $title = strip_tags($form->get('title')->getData());

        $slugify = new Slugify();
        $repository = $this->manager->getRepository('ArcmediaCmsBundle:ArcmediaPage');

        $exists_page = empty($current_title) ?
            $repository->findOneBy(['alias' => $slugify->slugify($title), 'active' => true]) :
            null;

        // Check blacklist
        $slug_title = $slugify->slugify($title);
        switch($slug_title){
            case 'home':
            case 'admin':
            case 'admin/new-page':
            case 'admin/edit-page':
                    $forbidden_title = true;
                break;

            default:
                $forbidden_title = false;
        }

        if($exists_page){
            $form->get('title')->addError(new FormError($translator->trans('FORM_PAGE_DUPLICATE')));
        }
        else if($forbidden_title){
            $form->get('title')->addError(new FormError($translator->trans('FORM_PAGE_RESERVED_WORD')));
        }
        else{
            $page->setTitle(trim(strip_tags($title)));
        }

        $content = strip_tags($form->get('content')->getData(), self::ALLOWED_TAGS);
        $page->setContent(trim($content));

        if($current_title == ''){
            $max_order = $repository->getMaxOrder();
            $page->setOrder($max_order+1);
        }
    }
}
