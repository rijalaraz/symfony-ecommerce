<?php

namespace App\Classe;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    /**
     * getCart()
     * Fonction retournant le panier
     */
    public function getCart()
    {
        return $this->requestStack->getSession()->get('cart');
    }

    /**
     * add()
     * Fonction permettant l'ajout d'un produit au panier
     */
    public function add(Product $product)
    {
        $cart = $this->getCart();

        // Ajouter une quantity + 1  à mon produit
        if(!empty($cart[$product->getId()])) {
            $cart[$product->getId()] = [
                'object' => $product,
                'qty' => $cart[$product->getId()]['qty'] + 1
            ];
        } else {
            $cart[$product->getId()] = [
                'object' => $product,
                'qty' => 1
            ];
        }

        // Créer ma session Cart
        $this->requestStack->getSession()->set('cart', $cart);
    }

    /**
     * decrease()
     * Fonction permettant la suppression d'une quantité d'un produit au panier
     */
    public function decrease($id)
    {
        $cart = $this->getCart();

        if ($cart[$id]['qty'] > 1) {
            $cart[$id]['qty'] = $cart[$id]['qty'] - 1;
        } else {
            unset($cart[$id]);
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    /**
     * fullQuantity()
     * Fonction retournant le nombre total de produits au panier
     */
    public function fullQuantity()
    {
        $cart = $this->getCart();

        $quantity = 0;

        if (empty($cart)) {
            return $quantity;
        };

        foreach ($cart as $product) {
            $quantity += $product['qty'];
        }

        return $quantity;
    }

    /**
     * getTotalWt()
     * Fonction retournant le prix total des produits au panier
     */
    public function getTotalWt()
    {
        $cart = $this->getCart();

        $price = 0;

        if (empty($cart)) {
            return $price;
        }

        foreach ($cart as $product) {
            /**
             * @var Product $productObj
             */
            $productObj = $product['object'];
            $price += $productObj->getPriceWt() * $product['qty'];
        }

        return $price;
    }

    /**
     * remove()
     * Fonction permettant de supprimer totalement le contenu du panier
     */
    public function remove()
    {
        return $this->requestStack->getSession()->remove('cart');
    }
}