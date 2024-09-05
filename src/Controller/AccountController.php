<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressUserType;
use App\Form\PasswordUserType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }

    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }

    #[Route('/account/modify-pwd', name: 'app_account_modify_pwd')]
    public function password(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(PasswordUserType::class, $user, [
            'passwordHasher' => $passwordHasher
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                'Votre mot de passe est correctement mis à jour'
            );
        }

        return $this->render('account/password.html.twig', [
            'modifyPwd' => $form->createView()
        ]);
    }

    #[Route('/account/addresses', name: 'app_account_addresses')]
    public function addresses(): Response
    {
        return $this->render('account/addresses.html.twig');
    }

    #[Route('/account/addresses/delete/{id}', name: 'app_account_address_delete')]
    public function addressDelete($id, AddressRepository $addressRepository): Response
    {
        $address = $addressRepository->findOneById($id);

        if (!$address || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_account_addresses');
        }

        $this->addFlash(
            'success',
            'Votre adresse est correctement supprimée'
        );

        $this->entityManager->remove($address);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_account_addresses');
    }

    #[Route('/account/addresses/add/{id}', name: 'app_account_address_form', defaults: [ 'id' => null ])]
    public function addressForm(Request $request, $id, AddressRepository $addressRepository): Response
    {
        if ($id) {
            /**
             * @var Address $address
             */
            $address = $addressRepository->findOneById($id);
            if (!$address || $address->getUser() != $this->getUser()) {
                return $this->redirectToRoute('app_account_addresses');
            }
        } else {
            $address = new Address();
            $address->setUser($this->getUser());
        }

        $form = $this->createForm(AddressUserType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();   

            $this->addFlash(
                'success',
                'Votre adresse est correctement sauvegardée'
            );

            return $this->redirectToRoute('app_account_addresses');
        }

        return $this->render('account/addressForm.html.twig', [
            'addressForm' => $form->createView()
        ]);
    }
}
