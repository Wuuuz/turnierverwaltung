<?php

/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 21.11.2016
 * Time: 12:58
 */

namespace TeilnehmerBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MannschaftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('verein', EntityType::class, array(
                'class' => 'TeilnehmerBundle\Entity\Verein',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                    ->where('u.status > 0');
                },
            ))
            ->add('altersklasse', EntityType::class, array(
                'class' => 'TurnierplanBundle\Entity\Altersklasse',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id != 8');
                },
            ))
            ->add('herkunft', ChoiceType::class, array(
                'choices' => array(
                    'Heimmannschaft' => '1',
                    'ausländische Mannschaft' => '2',
                    'deutsche Mannschaft' => '3',
                ),
                'expanded' => true,
                'multiple' => false
            ))
            ->add('liga', ChoiceType::class, array(
                'choices' => array(
                    'Bezirksklasse' => '1',
                    'Bezirksliga' => '2',
                    'Bezirksoberliga' => '3',
                    'Landesliga' => '4',
                    'Bayernliga' => '5',
                ),
                'expanded' => false,
                'multiple' => false
            ))
            ->add('ligaplatz', ChoiceType::class, array(
                'choices' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                ),
                'multiple' => false
            ))
            ->add('anzPersonen', TextType::class)
            ->add('ankunft', DateTimeType::class, array(
                'widget' => 'single_text',
                'format' => 'dd.MM.y HH:mm',
                'attr' => [
                    'class' => 'form-control input-inline datepicker',
                    'data-provide' => 'datepicker',
]
            ))
            ->add('unterkunft', EntityType::class, array(
                'class' => 'TeilnehmerBundle\Entity\Unterkunft',
            ))
            ->add('anzEssen', TextType::class)
            ->add('anzSr', TextType::class)
            ->add('mvName', TextType::class)
            ->add('mvStrasse', TextType::class)
            ->add('mvPLZ', TextType::class)
            ->add('mvOrt', TextType::class)
            ->add('mvLand', TextType::class)
            ->add('mvTelefon', TextType::class)
            ->add('mvEmail', EmailType::class)
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
            'data_class' => 'TeilnehmerBundle\Entity\Mannschaft',
        ));
    }
}