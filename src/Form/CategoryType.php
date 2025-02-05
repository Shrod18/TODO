<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Todo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
        ;

        if ($options['is_edit']) {
            $builder->add('todos', EntityType::class, [
                'class' => Todo::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'is_edit' => false,
        ]);
    }
}
