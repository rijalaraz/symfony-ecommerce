<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Address;
use App\Entity\OrderDetail;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

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
    public function add(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        if ($request->getMethod() != 'POST') {
            return $this->redirectToRoute('app_cart');
        }

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $addresses = $user->getAddresses();

        $products = $cart->getCart();

        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $addresses,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Stocker les infos dans la BDD

            // Création de la chaîne adresse
            /**
             * @var Address $addressObj
             */
            $addressObj = $form->get('addresses')->getData();
            $address = sprintf('%s %s', $addressObj->getFirstname(), $addressObj->getLastname()).'<br>';
            $address .= $addressObj->getAddress().'<br>';
            $address .= sprintf('%s %s', $addressObj->getPostal(), $addressObj->getCity()).'<br>';
            $address .= $addressObj->getCountry().'<br>';
            $address .= $addressObj->getPhone();

            $order = new Order();
            $order->setCreatedAt(new \DateTime());
            $order->setState(1);
            $order->setCarrierName($form->get('carriers')->getData()->getName());
            $order->setCarrierPrice($form->get('carriers')->getData()->getPrice());
            $order->setDelivery($address);

            foreach ($products as $product) {
                $orderDetail = new OrderDetail();
                /**
                 * @var Product $productObj
                 */
                $productObj = $product['object'];

                $orderDetail->setProductName($productObj->getName());
                $orderDetail->setProductIllustration($productObj->getIllustration());
                $orderDetail->setProductPrice($productObj->getPrice());
                $orderDetail->setProductTva($productObj->getTva());
                $orderDetail->setProductQuantity($product['qty']);
                $order->addOrderDetail($orderDetail);
            }

            $entityManager->persist($order);
            $entityManager->flush();
        }

        return $this->render('order/summary.html.twig', [
            'choices' => $form->getData(),
            'cart' => $products,
            'totalWt' => $cart->getTotalWt()
        ]);
    }
}
