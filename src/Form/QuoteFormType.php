<?php

namespace App\Form;

use App\Entity\Quotes;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('meta', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'attr' => ['class' => 'form-control'],
                'choice_label' => 'name',
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quotes::class,
        ]);
    }
}
