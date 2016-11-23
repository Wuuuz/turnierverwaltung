<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 22.11.2016
 * Time: 20:56
 */
namespace TeilnehmerBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmitTypeExtension extends AbstractTypeExtension
{
    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return SubmitType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('button_image','button_color'));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if(isset($options['button_image']))
            $view->vars['button_image'] = $options['button_image'];
        else
            $view->vars['button_image'] = NULL;

        if(isset($options['button_color']))
            $view->vars['button_color'] = $options['button_color'];
        else
            $view->vars['button_color'] = NULL;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'button_image' => null,
            'button_color' => null,
        ));
    }
}