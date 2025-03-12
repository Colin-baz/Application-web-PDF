<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
    }

    public function testRegisterFormSubmissionWithValidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('S\'inscrire')->form();
        $form['registrationForm[email]'] = 'testuser@example.com';
        $form['registrationForm[plainPassword]'] = 'Password123';
        $form['registrationForm[firstname]'] = 'John';
        $form['registrationForm[lastname]'] = 'Doe';

        $client->submit($form);

        $this->assertResponseRedirects('/login');
    }

    public function testRegisterFormSubmissionWithInvalidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('S\'inscrire')->form();
        $form['registrationForm[email]'] = 'invalid-email';
        $form['registrationForm[plainPassword]'] = 'short';

        $client->submit($form);

        $this->assertResponseIsSuccessful();
    }
}