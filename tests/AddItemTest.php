<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use App\Controller\AddItem;

class AddItemTest extends TestCase
{
    public function testIsEmailValide()
    {
        $controller = new AddItem();

        $this->assertTrue($controller->isEmail("test@example.com"));
        $this->assertTrue($controller->isEmail("utilisateur123@domaine.fr"));
    }

    public function testIsEmailInvalide()
    {
        $controller = new AddItem();

        $this->assertFalse($controller->isEmail("test@.com"));
        $this->assertFalse($controller->isEmail("utilisateur@invalide"));
        $this->assertFalse($controller->isEmail("pasUnEmail"));
    }

    public function testFormValidationWithErrors()
    {
        $controller = new AddItem();

        $fakeData = [
            'nom' => '',
            'email' => 'bademail',
            'phone' => 'non-numérique',
            'ville' => '',
            'departement' => 'abc',
            'categorie' => '',
            'title' => '',
            'description' => '',
            'price' => 'NaN',
            'psw' => 'abc',
            'confirm-psw' => 'xyz',
        ];

        $errors = $controller->validateForm($fakeData);
        $this->assertGreaterThan(0, count($errors));
    }
}
