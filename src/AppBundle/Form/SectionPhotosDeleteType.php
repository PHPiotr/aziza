<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;

class SectionPhotosDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('confirm', CheckboxType::class, array(
                'label'    => 'Czy na pewno chcesz usunąć zaznaczone zdjęcia z tej sekcji?',
                'required' => true,
                'constraints'=> new IsTrue(array('message'=>'Proszę potwierdź'))
            ))
            ->add('save', SubmitType::class);
    }

}