<?php

namespace App\Classe;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    public function __construct(private RequestStack $requestStack)
    {
        
    }

    public function add(Product $product)
    {
        $cart = $this->requestStack->getSession()->get('cart');

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

    public function decrease($id)
    {
        $cart = $this->requestStack->getSession()->get('cart');

        if ($cart[$id]['qty'] > 1) {
            $cart[$id]['qty'] = $cart[$id]['qty'] - 1;
        } else {
            unset($cart[$id]);
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function fullQuantity()
    {
        $cart = $this->requestStack->getSession()->get('cart');

        $quantity = 0;

        if (empty($cart)) {
            return $quantity;
        };

        foreach ($cart as $product) {
            $quantity += $product['qty'];
        }

        return $quantity;
    }

    public function getTotalWt()
    {
        $cart = $this->requestStack->getSession()->get('cart');

        $price = 0;

        if (empty($cart)) {
            return $price;
        }

        foreach ($cart as $product) {
            $price += $product['object']->getPriceWt() * $product['qty'];
        }

        return $price;
    }

    public function remove()
    {
        return $this->requestStack->getSession()->remove('cart');
    }

    public function getCart()
    {
        return $this->requestStack->getSession()->get('cart');
    }
}