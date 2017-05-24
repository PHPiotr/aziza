<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Gallery;
use Symfony\Component\Form\CallbackTransformer;
use AppBundle\Form\DataTransformer\NumberToRadioTransformer;
use Doctrine\Common\Persistence\ObjectManager;

class GalleryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'Tytuł galerii'])
            ->add('galleryPhotos', FileType::class, array(
                'multiple' => true,
                'data_class' => null,
                'mapped' => false,
                'label' => 'Dodaj zdjęcia do galerii'
            ))
            ->add('edytuj', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Gallery::class,
        ));
    }

}