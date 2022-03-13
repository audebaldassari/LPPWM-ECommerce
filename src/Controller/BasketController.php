<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\UserBasket;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/basket")
 */
class BasketController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="basket")
     */
    public function index(): Response
    {
        $user = $this->getUser();
    
        $sessionBasket = UserBasket::getBasketFromSession($this->session);
        $productsPrice = 0.00;
        $numberOfProducts = 0;
        $canMakeOrder = true;

        if (is_null($user)) {
            $productRepository = $this->getDoctrine()->getRepository(Product::class);
            foreach ($sessionBasket as $i => $item) {
                $product =  $productRepository->find($item->productId);
                if (!is_null($product) && !$product->isRemoved()) {
                    $item->id = $i;
                    $item->product = $product;
                    $productsPrice += $product->getPrice() * $item->quantity;
                    $numberOfProducts += $item->quantity;

                    if ($product->getQuantity() === 0 || $product->getQuantity() < $item->quantity) {
                        $canMakeOrder = false;
                    }
                }
                else {
                    unset($sessionBasket[$i]);
                    UserBasket::removeItemFromSessionBasket($this->session, $i);
                }
            }
        } else {
            foreach ($user->getBasket() as $item) {
                $productsPrice += $item->getProduct()->getPrice() * $item->getQuantity();
                $numberOfProducts += $item->getQuantity();

                if ($item->getProduct()->getQuantity() === 0 || $item->getProduct()->getQuantity() < $item->getQuantity()) {
                    $canMakeOrder = false;
                }
            }
        }

        $shippingPrice = ProductController::getShippingPrice($productsPrice);

        $totalPrice = $productsPrice + $shippingPrice;

        return $this->render('basket/index.html.twig', [
            'sessionBasket' => $sessionBasket,
            'shippingPrice' => $shippingPrice,
            'totalPrice' => $totalPrice,
            'numberOfProducts' => $numberOfProducts,
            'canMakeOrder' => $canMakeOrder
        ]);
    }

    /**
     * @Route("/edit-item/{id}", name="basket_edit", methods={"POST"})
     */
    public function editItem(Request $request, UserBasket $basketItem): Response
    {
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('basket_edit' . $user->getId(), $request->request->get('_token'))) {
            $quantity = (int) $request->request->get('quantity', 1);

            if ($basketItem->getUser()->getId() === $user->getId()) {
                $em = $this->getDoctrine()->getManager();

                $basketItem->setQuantity((int) $quantity);
                $em->flush();
            }
        }

        return $this->redirectToRoute('basket');
    }

    /**
     * @Route("/edit-item", name="basket_session_edit", methods={"POST"})
     */
    public function editSessionItem(Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);

        UserBasket::editSessionItem($this->session, $request->request->get('id'), [
            'quantity' => $quantity
        ]);

        return $this->redirectToRoute('basket');
    }

    /**
     * @Route("/remove-item/{id}", name="basket_remove_item", methods={"POST"})
     */
    public function removeItem(Request $request, UserBasket $basketItem): Response
    {
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('basket_remove_item' . $user->getId(), $request->request->get('_token'))) {
            if ($basketItem->getUser()->getId() === $user->getId()) {
                $em = $this->getDoctrine()->getManager();

                $em->remove($basketItem);
                $em->flush();
            }
        }

        return $this->redirectToRoute('basket');
    }

    /**
     * @Route("/remove-item", name="basket_remove_session_item", methods={"POST"})
     */
    public function removeSessionItem(Request $request): Response
    {
        UserBasket::removeItemFromSessionBasket(
            $this->session,
            $request->request->get('id')
        );

        return $this->redirectToRoute('basket');
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
