<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Form\Type\TagsInputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Defines the form used to create and manipulate projects.
 *
 */
final class ProjectType extends AbstractType
{
    // Form types are services, so you can inject other services in them if needed
    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // For the full reference of options defined by each form field type
        // see https://symfony.com/doc/current/reference/forms/types.html

        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'title',
            ])
            ->add('tasknumber', NumberType::class)
            ->add('filename', null)
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'in progress' => 'IN_PROGRESS' ,
                    'done' => 'DONE',
                    'blocked' => 'BLOCKED',
                ],
                
            ])
            ->add('description', null, [
                'attr' => ['rows' => 20],
                'label' => 'description',
            ])
            ->add('image', FileType::class, array('data_class' => null, 'mapped' =>false))
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}