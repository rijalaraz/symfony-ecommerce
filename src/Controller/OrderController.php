<?php

namespace App\Controller;

use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

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
            'addresses' => $addresses
        ]);

        return $this->render('order/index.html.twig', [
            'deliveryForm' => $form->createView(),
        ]);
    }
}
