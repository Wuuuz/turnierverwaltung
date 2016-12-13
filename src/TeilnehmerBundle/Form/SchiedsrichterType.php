<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 01.12.2016
 * Time: 21:57
 */

namespace TeilnehmerBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchiedsrichterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('verein', EntityType::class, array(
                'class' => 'TeilnehmerBundle\Entity\Verein',
            ))
            ->add('save', SubmitType::class, array(
                'label' => ' Speichern',
                'button_image' => 'check',
                'button_color' => 'rgba(13, 135, 13, 1)',
                'attr' => array(
                    'class' => 'btn btn-lg btn-sm btn-default',
                    'style' => 'color: rgba(13, 135, 13, 1);')
            ))
            ->add('sichernUndSchliessen', SubmitType::class, array(
                'label' => ' Speichern & Schließen',
                'button_image' => 'ok',
                'button_color' => 'rgba(66, 66, 66, 1)',
                'attr' => array(
                    'class' => 'btn btn-lg btn-sm btn-default',
                    'style' => 'color: rgba(66, 66, 66, 1);')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TeilnehmerBundle\Entity\Schiedsrichter'
        ));
    }
}