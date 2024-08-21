<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserTest extends WebTestCase
{
    public function testSomething(): void
    {
        /**
         * 1. Créer un faux client (navigateur) de pointer vers une URL
         * 2. Remplir les champs de mon formulaire d'inscription
         * 3. Rediriger vers le formulaire de login
         * 4. Est-ce que tu peux regarder si dans ma page j'ai le message d'alerte suivant : Votre compte est correctement créé, veuillez vous connecter
         */

        // 1.
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        // 2. (firstname, lastname, email, password, confirmation du password)
        $client->submitForm('Valider', [
            'register_user[email]' => 'julie@exemple.fr',
            'register_user[plainPassword][first]' => '123456',
            'register_user[plainPassword][second]' => '123456',
            'register_user[firstname]' => 'Julie',
            'register_user[lastname]' => 'Doe',
        ]);

        // 3.
        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        // 4.
        $this->assertSelectorTextContains('div.alert.alert-success', 'Votre compte est correctement créé, veuillez vous connecter');
    }
}
