<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the book title']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'The title cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter book title'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('author', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the author name']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'The author name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter author name'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('isbn', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the ISBN']),
                    new Regex([
                        'pattern' => '/^(?:\d[- ]?){9}[\dXx]$|^(?:\d[- ]?){13}$/',
                        'message' => 'Please enter a valid 10 or 13-digit ISBN',
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., 978-3-16-148410-0'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('publicationDate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a publication date']),
                ],
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Fiction' => 'Fiction',
                    'Non-fiction' => 'Non-fiction',
                    'Science Fiction' => 'Science Fiction',
                    'Fantasy' => 'Fantasy',
                    'Mystery' => 'Mystery',
                    'Thriller' => 'Thriller',
                    'Romance' => 'Romance',
                    'Biography' => 'Biography',
                    'History' => 'History',
                    'Science' => 'Science',
                    'Other' => 'Other',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a genre']),
                ],
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('copies', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the number of copies']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'The number of copies cannot be negative',
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'min' => 0],
                'label_attr' => ['class' => 'form-label'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}