<?php

/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 29.11.2016
 * Time: 09:59
 */

namespace UserBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Util\LegacyFormHelper;

class UserFormType extends AbstractType
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
        $this->class = 'UserBundle\Entity\User';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
        if($options['information']) {
            $builder->add('name', TextType::class)
                ->add('username', TextType::class)
                ->add('email', EmailType::class)
                ->add('groups', EntityType::class, array(
                    'class' => 'UserBundle\Entity\Group',
                    'multiple' => true,
                    'expanded' => true,
                ));
        }

        if($options['password']) {
            $builder
                ->add('plainPassword', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\RepeatedType'), array(
                    'type' => LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\PasswordType'),
                    'options' => array('translation_domain' => 'FOSUserBundle'),
                    'first_options' => array('label' => 'Passwort'),
                    'second_options' => array('label' => 'Passwort wiederholen'),
                    'invalid_message' => 'fos_user.password.mismatch',
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\User',
            'password' => null,
            'information' => null,
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
        return 'app_user';
    }
}