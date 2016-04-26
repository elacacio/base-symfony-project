<?php

namespace Arcmedia\CmsBundle\Form\Type;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ArcmediaPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'title',
            TextType::class,
            [
                'required' => true,
            ]
        )
            ->add(
                'content',
                CKEditorType::class,
                array(
                    'config_name' => 'admin_config',
                )
            )
            ->add(
                'active',
                CheckboxType::class,
                [
                    'required' => false,
                ]
            )

        ;
    }

    public function getName()
    {
        return 'newpage';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Arcmedia\CmsBundle\Entity\ArcmediaPage',
            )
        );
    }

}
