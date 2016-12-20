<?php

/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 21.11.2016
 * Time: 12:58
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TurnierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class);

        if($options['button'] == true){
            $builder
                ->add('save', SubmitType::class, array(
                    'label' => ' Speichern',
                    'button_image' => 'check',
                    'button_color' => 'rgba(13, 135, 13, 1)',
                    'attr' => array(
                        'class' => 'btn btn-lg btn-sm btn-default',
                        'style' => 'color: rgba(13, 135, 13, 1);')
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Turnier',
            'button' => true
        ));
    }
}