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
 * @Route("/product")
 */
class ProductController extends AbstractController
{
	private $session;

	public function __construct(SessionInterface $session)
	{
		$this->session = $session;
	}

	/**
	 * @Route("/{id}", name="product_show", methods={"GET"})
	 */
	public function show(Product $product): Response
	{
		return $this->render('product/show.html.twig', [
			'product' => $product,
			'isProductInSessionBasket' => UserBasket::sessionContains($this->session, $product)
		]);
	}

	/**
	 * @Route("/{id}/add-to-basket", name="add_product_to_basket", methods={"POST"})
	 */
	public function addToBasket(Request $request, Product $product): Response
	{
		$user = $this->getUser();

		$quantity = (int) $request->request->get('quantity', 1);

		if (is_null($user)) {
			if (!UserBasket::sessionContains($this->session, $product)) {
				UserBasket::addItemToSessionBasket($this->session, [
					'productId' => $product->getId(),
					'quantity' => $quantity
				]);
			}
		}
		else {
			if ($this->isCsrfTokenValid('add_to_basket' . $product->getId(), $request->request->get('_token'))) {
				if ($product->getQuantity() !== 0 && !$user->hasProductInBasket($product) && !$product->isRemoved()) {
					$em = $this->getDoctrine()->getManager();

					$user->addProductToBasket($product, $quantity);
					$em->flush();
				}
			}
		}

		return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
	}

	/**
	 * @Route("/{id}/remove-from-basket", name="remove_product_from_basket", methods={"POST"})
	 */
	public function removeFromBasket(Request $request, Product $product): Response
	{
		$user = $this->getUser();

		if (!is_null($user)) {
			if ($this->isCsrfTokenValid('remove_from_basket' . $product->getId(), $request->request->get('_token'))) {
				$basketItem = $user->getBasketItemFromProduct($product);

				if (!is_null($basketItem)) {
					$em = $this->getDoctrine()->getManager();
					$user->removeItemFromBasket($basketItem);
					$em->flush();
				}
			}
		}
		else {
			UserBasket::removeProductFromSessionBasket($this->session, $product);
		}

		return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
	}

	public static function getShippingPrice(int $orderPrice): float
	{
		if ($orderPrice >= 100)
			return 0.0;
		else if ($orderPrice >= 50)
			return 4.99;
		else if ($orderPrice >= 30)
			return 9.99;
		else if ($orderPrice >= 20)
			return 14.99;
		else
			return 19.99;
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
