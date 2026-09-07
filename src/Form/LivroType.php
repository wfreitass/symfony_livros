<?php

namespace App\Form;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use App\Form\DataTransformer\BrazilianMoneyTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivroType extends AbstractType
{
    public function __construct(
        private readonly BrazilianMoneyTransformer $moneyTransformer
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título do Livro',
                'attr' => [
                    'placeholder' => 'Ex: Dom Casmurro',
                    'maxlength' => 40,
                ],
            ])
            ->add('editora', TextType::class, [
                'label' => 'Editora',
                'attr' => [
                    'placeholder' => 'Ex: Garnier',
                    'maxlength' => 40,
                ],
            ])
            ->add('edicao', IntegerType::class, [
                'label' => 'Edição',
                'attr' => [
                    'placeholder' => 'Ex: 1',
                    'min' => 1,
                ],
            ])
            ->add('anoPublicacao', TextType::class, [
                'label' => 'Ano de Publicação',
                'attr' => [
                    'placeholder' => 'Ex: 1899',
                    'maxlength' => 4,
                    'pattern' => '\d{4}',
                ],
                'help' => 'Informe o ano com 4 dígitos (ex: 2024).',
            ])
            ->add('valor', TextType::class, [
                'label' => 'Valor (R$)',
                'attr' => [
                    'placeholder' => 'Ex: 49,90',
                    'class' => 'money-mask',
                ],
                'help' => 'Digite o valor em reais (ex: 150,00).',
            ])
            ->add('autores', EntityType::class, [
                'class' => Autor::class,
                'choice_label' => 'nome',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Autores',
                'attr' => [
                    'class' => 'form-select',
                    'size' => 4,
                ],
                'help' => 'Segure Ctrl (ou Cmd) para selecionar múltiplos autores.',
            ])
            ->add('assuntos', EntityType::class, [
                'class' => Assunto::class,
                'choice_label' => 'descricao',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Assuntos / Gêneros',
                'attr' => [
                    'class' => 'form-select',
                    'size' => 4,
                ],
                'help' => 'Segure Ctrl (ou Cmd) para selecionar múltiplos assuntos.',
            ]);

        // Aplica o DataTransformer no campo valor
        $builder->get('valor')->addModelTransformer($this->moneyTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livro::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
