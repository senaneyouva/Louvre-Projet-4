<?php

namespace LouvreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, array(

 'label' => false,
      'attr' => array(
                 
                    'placeholder'=> 'Votre Email',
                ),

                ))

            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                 'label' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => array(
                    'class' => 'date',
               
                ),
            ))
            ->add('duree', CheckboxType::class, array(
                'required' => false,
                 'label' => ' Demi-journée',
                'label_attr' => array ('class' => 'duree'),
            ))
            ->add('tickets', CollectionType::class, array(
                'entry_type' => TicketType::class,
                 'label' => ' ',
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => array(
                    'label_attr' => array('class'=>'effacer'),
                    ),
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LouvreBundle\Entity\Commande'
        ));
    }
}
