<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\ProductCategory;
use App\Entity\UserBasket;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="orders")
     */
    public function index(): Response
    {
        $orders = $this->getUser()->getOrders();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/{id}", name="order_show", methods={"GET"})
     */
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order
        ]);
    }

    /**
     * @Route("/new", name="order_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('order_basket' . $user->getId(), $request->request->get('_token'))) {
            if (count($user->getBasket()) === 0)
            return $this->redirectToRoute('basket');

            $em = $this->getDoctrine()->getManager();
            $order = new Order();

            $productsPrice = 0.00;
            foreach ($user->getBasket() as $item) {
                $product = $item->getProduct();

                if ($product->getQuantity() > 0 && $product->getQuantity() >= $item->getQuantity()) {
                    $product->setQuantity($product->getQuantity() - $item->getQuantity());
                    $orderItem = new OrderItem();
                    $orderItem->setProduct($product);
                    $orderItem->setUnitPrice($product->getPrice());
                    $orderItem->setQuantity($item->getQuantity());

                    $order->addItem($orderItem);

                    $productsPrice += $product->getPrice() * $item->getQuantity();
                }

                $em->remove($item);
            }

            $order->setReference(strtoupper(uniqid()));
            $order->setShippingPrice(ProductController::getShippingPrice($productsPrice));
            $order->setDate(new DateTime());

            $user->addOrder($order);

            $em->flush();
        }

        return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
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
