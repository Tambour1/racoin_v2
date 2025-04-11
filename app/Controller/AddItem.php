<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Annonceur;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class AddItem
{   
    /**
     * Affichage de la page d'ajout d'un item
     * 
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param array $cat Les catégories d'annonces.
     * @param array $dpt Les départements d'annonces.
     * @return Response
     */
    public function addItemView(Response $response, Twig $twig, array $menu, string $chemin, array $cat, array $dpt): Response
    {
        return $twig->render($response, "add.html.twig", [
            "breadcrumb"   => $menu,
            "chemin"       => $chemin,
            "categories"   => $cat,
            "departements" => $dpt
        ]);
    }

    /**
     * Vérifie si l'email est valide
     * 
     * @param string $email L'email à valider.
     * @return bool
     */
    public function isEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valide le formulaire d'ajout d'un item
     * 
     * @param array $data Les données du formulaire.
     * @return array
     */
    public function validateForm(array $data): array
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors['nameAdvertiser'] = 'Veuillez entrer votre nom';
        }
        if (!$this->isEmail($data['email'])) {
            $errors['emailAdvertiser'] = 'Veuillez entrer une adresse mail correcte';
        }
        if (empty($data['phone']) || !is_numeric($data['phone'])) {
            $errors['phoneAdvertiser'] = 'Veuillez entrer un numéro de téléphone valide';
        }
        if (empty($data['ville'])) {
            $errors['villeAdvertiser'] = 'Veuillez entrer votre ville';
        }
        if (!is_numeric($data['departement'])) {
            $errors['departmentAdvertiser'] = 'Veuillez choisir un département';
        }
        if (!is_numeric($data['categorie'])) {
            $errors['categorieAdvertiser'] = 'Veuillez choisir une catégorie';
        }
        if (empty($data['title'])) {
            $errors['titleAdvertiser'] = 'Veuillez entrer un titre';
        }
        if (empty($data['description'])) {
            $errors['descriptionAdvertiser'] = 'Veuillez entrer une description';
        }
        if (empty($data['price']) || !is_numeric($data['price'])) {
            $errors['priceAdvertiser'] = 'Veuillez entrer un prix';
        }
        if (empty($data['psw']) || empty($data['confirm-psw']) || $data['psw'] !== $data['confirm-psw']) {
            $errors['passwordAdvertiser'] = 'Les mots de passe ne sont pas identiques';
        }

        return $errors;
    }

    /**
     * Ajoute un nouvel item
     * 
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param array $allPostVars Les données du formulaire.
     * @return Response
     */
    public function addNewItem(Response $response, Twig $twig, array $menu, string $chemin, array $allPostVars): Response
    {
        date_default_timezone_set('Europe/Paris');

        // Nettoyage des données
        $formData = array_map('trim', $allPostVars);

        // Validation
        $errors = $this->validateForm($formData);

        if (!empty($errors)) {
            return $twig->render($response, "add-error.html.twig", [
                "breadcrumb" => $menu,
                "chemin"     => $chemin,
                "errors"     => array_values($errors)
            ]);
        }

        $annonceur = new Annonceur();
        $annonceur->email         = htmlentities($formData['email']);
        $annonceur->nom_annonceur = htmlentities($formData['nom']);
        $annonceur->telephone     = htmlentities($formData['phone']);
        $annonceur->save();

        $annonce = new Annonce();
        $annonce->ville          = htmlentities($formData['ville']);
        $annonce->id_departement = (int) $formData['departement'];  
        $annonce->prix           = (float) htmlentities($formData['price']);
        $annonce->mdp            = password_hash($formData['psw'], PASSWORD_DEFAULT);
        $annonce->titre          = htmlentities($formData['title']);
        $annonce->description    = htmlentities($formData['description']);
        $annonce->id_categorie   = (int) $formData['categorie'];  
        $annonce->date           = date('Y-m-d');

        $annonceur->annonce()->save($annonce);

        return $twig->render($response, "add-confirm.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin
        ]);
    }
}
