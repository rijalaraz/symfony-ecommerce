<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends AbstractController
{
    /**
     * 1ère étape du tunnel d'achat
     * Choix de l'adresse de livraison et du transporteur
     */
    #[Route('/order/delivery', name: 'app_order')]
    public function index(): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $addresses = $user->getAddresses();

        if (count($addresses) == 0) {
            return $this->redirectToRoute('app_account_address_form');
        }

        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $addresses,
            'action' => $this->generateUrl('app_order_summary')
        ]);

        return $this->render('order/index.html.twig', [
            'deliveryForm' => $form->createView(),
        ]);
    }

    /**
     * 2ème étape du tunnel d'achat
     * Recap de la commande de l'utilisateur
     * Insertion en base de données
     * Préparation du paiement vers stripe
     */
    #[Route('/order/summary', name: 'app_order_summary')]
    public function add(Request $request, Cart $cart): Response
    {
        if ($request->getMethod() != 'POST') {
            return $this->redirectToRoute('app_cart');
        }

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $addresses = $user->getAddresses();

        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $addresses,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
        }

        return $this->render('order/summary.html.twig', [
            'choices' => $form->getData(),
            'cart' => $cart->getCart(),
            'totalWt' => $cart->getTotalWt()
        ]);
    }
}
