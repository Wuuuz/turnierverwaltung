<?php

/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 29.11.2016
 * Time: 09:59
 */

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupFormType extends AbstractType
{
    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class The Group class name
     */
    public function __construct()
    {
        $this->class = 'UserBundle\Entity\Group';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = $options['roles'];

        $builder->add('name', TextType::class)
                ->add('roles', ChoiceType::class, array(
                    'choices'=> $roles,
                    'mapped' => true,
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'Rollen'
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'csrf_token_id' => 'group',
            // BC for SF < 2.8
            'intention' => 'group',
            'roles' => null
        ));
    }

    // BC for SF < 3.0
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_group';
    }
}