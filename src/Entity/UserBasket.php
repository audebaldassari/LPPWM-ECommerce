<?php

namespace App\Entity;

use App\Repository\UserBasketRepository;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Expr\Cast\Array_;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @ORM\Entity(repositoryClass=UserBasketRepository::class)
 */
class UserBasket
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="basketItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public static function getBasketFromSession(SessionInterface $session): Array
    {
        $rawBasket = $session->get('basket', null);
        if (!is_null($rawBasket))
            return json_decode($rawBasket);
        
        return [];
    }

    public static function addItemToSessionBasket(SessionInterface $session, Array $item): void
    {
        $rawBasket = $session->get('basket', null);
        $basket = [];
        if (!is_null($rawBasket))
            $basket = json_decode($rawBasket);

        array_push($basket, $item);
        $session->set('basket', json_encode($basket));
    }

    public static function removeProductFromSessionBasket(SessionInterface $session, Product $product): void
    {
        $newBasket = [];
        $basket = self::getBasketFromSession($session);
        foreach ($basket as $item) {
            if ($item->productId !== $product->getId()) {
                array_push($newBasket, $item);
            }
        }

        $session->set('basket', json_encode($newBasket));
    }

    public static function removeItemFromSessionBasket(SessionInterface $session, int $index): void
    {
        $newBasket = [];
        $basket = self::getBasketFromSession($session);
        foreach ($basket as $i => $item) {
            if ($i !== $index) {
                array_push($newBasket, $item);
            }
        }

        $session->set('basket', json_encode($newBasket));
    }

    public static function editSessionItem(SessionInterface $session, int $index, Array $values): void
    {
        $newBasket = [];
        $basket = self::getBasketFromSession($session);
        foreach ($basket as $i => $item) {
            if ($i !== $index) {
                array_push($newBasket, $item);
            } else {
                array_push($newBasket, [
                    'productId' => $item->productId,
                    'quantity' => $values['quantity']
                ]);
            }
        }

        $session->set('basket', json_encode($newBasket));
    }

    public static function sessionContains(SessionInterface $session, Product $product): bool
    {
        $basket = self::getBasketFromSession($session);
        foreach ($basket as $item) {
            if ($item->productId === $product->getId()) {
                return true;
            }
        }

        return false;
    }

    public static function removeFromSession(SessionInterface $session): void
    {
        $session->remove('bakset');
    }
}
