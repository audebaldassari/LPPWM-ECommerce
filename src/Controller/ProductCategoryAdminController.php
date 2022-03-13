<?php

namespace App\Controller;

use App\Entity\ProductCategory;
use App\Entity\UserBasket;
use App\Form\ProductCategoryType;
use App\Repository\ProductCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/product-category")
 */
class ProductCategoryAdminController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="admin_product_category_index", methods={"GET"})
     */
    public function index(ProductCategoryRepository $productCategoryRepository): Response
    {
        return $this->render('product_category/admin/index.html.twig', [
            'product_categories' => $productCategoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_product_category_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $productCategory = new ProductCategory();
        $form = $this->createForm(ProductCategoryType::class, $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $productCategory->setIsRemoved(false);
            $entityManager->persist($productCategory);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_category_index');
        }

        return $this->render('product_category/admin/new.html.twig', [
            'product_category' => $productCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_product_category_show", methods={"GET"})
     */
    public function show(ProductCategory $productCategory): Response
    {
        return $this->render('product_category/admin/show.html.twig', [
            'product_category' => $productCategory,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_product_category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ProductCategory $productCategory): Response
    {
        $form = $this->createForm(ProductCategoryType::class, $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_product_category_index');
        }

        return $this->render('product_category/admin/edit.html.twig', [
            'product_category' => $productCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/remove/{id}", name="admin_product_category_remove", methods={"POST"})
     */
    public function remove(Request $request, ProductCategory $productCategory): Response
    {
        if ($this->isCsrfTokenValid('remove'.$productCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $productCategory->setIsRemoved(true);

            foreach ($productCategory->getProducts() as $product) {
                $product->setIsRemoved(true);

                $basketItems = $product->getBasketItems();
                foreach ($basketItems as $item) {
                    $entityManager->remove($item);
                }
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_product_category_show', ['id' => $productCategory->getId()]);
    }

    /**
     * @Route("/activate/{id}", name="admin_product_category_activate", methods={"POST"})
     */
    public function activate(Request $request, ProductCategory $productCategory): Response
    {
        if ($this->isCsrfTokenValid('activate' . $productCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $productCategory->setIsRemoved(false);

            foreach ($productCategory->getProducts() as $product) {
                $product->setIsRemoved(false);
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_product_category_show', ['id' => $productCategory->getId()]);
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
