<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = $this->getCategoryRepository()->findAll();

        $selectOptions = [];
        foreach($categories as $category) {
            $selectOptions[$category->getLabel()] = $category->getId();
        }

        $builder
            ->add('title')
            ->add('image')
            ->add('price', NumberType::class, [
                'scale' => 2
            ])
            ->add('quantity', IntegerType::class)
            ->add('category', ChoiceType::class, [
                'choices' => $selectOptions,
            ]);

        $builder
            ->get('category')
            ->addModelTransformer(new CallbackTransformer(
                function ($category) {
                    if (!is_null($category))
                        return $category->getId();
                    return null;
                },
                function ($categoryId) {
                    return $this->getCategoryRepository()->find($categoryId);
                }
            ));
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    private function getCategoryRepository(): ProductCategoryRepository
    {
        return $this->entityManager->getRepository(ProductCategory::class);
    }
}
