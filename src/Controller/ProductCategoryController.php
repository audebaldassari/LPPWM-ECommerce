<?php

namespace App\Controller;

use App\Entity\ProductCategory;
use App\Entity\UserBasket;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product-category")
 */
class ProductCategoryController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/{id}", name="product_category_show", methods={"GET"})
     */
    public function show(ProductCategory $productCategory): Response
    {
        if ($productCategory->isRemoved()) {
            return $this->redirectToRoute('home');
        }

        return $this->render('product_category/show.html.twig', [
            'category' => $productCategory,
        ]);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        // Always add categories for navbar on render
        $categoryRepository = $this->getDoctrine()->getRepository(ProductCategory::class);
        $parameters['categories'] = $categoryRepository->findAll();

        // Always add session basket length
        $basket = UserBasket::getBasketFromSession($this->session);
        $parameters['basketLength'] = count($basket);

        return parent::render($view, $parameters, $response);
    }
}
