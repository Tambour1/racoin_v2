<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Annonceur;
use App\Model\Departement;
use App\Model\Photo;
use App\Model\Categorie;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

#[AllowDynamicProperties]
class Item {
    
    /**
     * Affiche un item spécifique
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param int $n L'ID de l'annonce.
     * @param array $cat Les catégories d'annonces.
     * @return Response
     */
    public function afficherItem(Response $response, Twig $twig, array $menu, string $chemin, int $n, array $cat): Response
    {
        $this->annonce = Annonce::find($n);
        if (!$this->annonce) {
            return $response->withStatus(404)->write("404");
        }

        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . "/cat/" . $n, 'text' => Categorie::find($this->annonce->id_categorie)?->nom_categorie],
            ['href' => $chemin . "/item/" . $n, 'text' => $this->annonce->titre]
        ];

        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->departement = Departement::find($this->annonce->id_departement);
        $this->photo = Photo::where('id_annonce', '=', $n)->get();

        return $twig->render($response, "item.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "annonce"    => $this->annonce,
            "annonceur"  => $this->annonceur,
            "dep"        => $this->departement->nom_departement,
            "photo"      => $this->photo,
            "categories" => $cat
        ]);
    }

    /**
     * Affiche la page de confirmation avant la suppression d'un item.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param int $n L'ID de l'annonce.
     * @return Response
     */
    public function supprimerItemGet(Response $response, Twig $twig, array $menu, string $chemin, int $n): Response
    {
        $this->annonce = Annonce::find($n);
        if (!$this->annonce) {
            return $response->withStatus(404)->write("404");
        }

        return $twig->render($response, "delGet.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "annonce"    => $this->annonce
        ]);
    }

    /**
     * Supprime un item après validation par mot de passe.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param int $n L'ID de l'annonce.
     * @param array $cat Les catégories d'annonces.
     * @return Response
     */
    public function supprimerItemPost(Response $response, Twig $twig, array $menu, string $chemin, int $n, array $cat): Response
    {
        $this->annonce = Annonce::find($n);
        $reponse = false;
        if (password_verify($_POST["pass"], $this->annonce->mdp)) {
            $reponse = true;
            Photo::where('id_annonce', '=', $n)->delete();
            $this->annonce->delete();
        }

        return $twig->render($response, "delPost.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "annonce"    => $this->annonce,
            "pass"       => $reponse,
            "categories" => $cat
        ]);
    }

    /**
     * Affiche le formulaire pour modifier un item.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param int $id L'ID de l'annonce.
     * @return Response
     */
    public function modifyGet(Response $response, Twig $twig, array $menu, string $chemin, int $id): Response
    {
        $this->annonce = Annonce::find($id);
        if (!$this->annonce) {
            return $response->withStatus(404)->write("404");
        }

        return $twig->render($response, "modifyGet.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "annonce"    => $this->annonce
        ]);
    }

    /**
     * Traite la soumission du formulaire de modification.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param int $n L'ID de l'annonce.
     * @param array $cat Les catégories d'annonces.
     * @param array $dpt Les départements disponibles.
     * @return Response
     */
    public function modifyPost(Response $response, Twig $twig, array $menu, string $chemin, int $n, array $cat, array $dpt): Response
    {
        $this->annonce = Annonce::find($n);
        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->categItem = Categorie::find($this->annonce->id_categorie)->nom_categorie;
        $this->dptItem = Departement::find($this->annonce->id_departement)->nom_departement;

        $reponse = false;
        if (password_verify($_POST["pass"], $this->annonce->mdp)) {
            $reponse = true;
        }

        return $twig->render($response, "modifyPost.html.twig", [
            "breadcrumb"   => $menu,
            "chemin"       => $chemin,
            "annonce"      => $this->annonce,
            "annonceur"    => $this->annonceur,
            "pass"         => $reponse,
            "categories"   => $cat,
            "departements" => $dpt,
            "dptItem"      => $this->dptItem,
            "categItem"    => $this->categItem
        ]);
    }

    /**
     * Modifie un item après la validation du formulaire.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param array $allPostVars Les données du formulaire.
     * @param int $id L'ID de l'annonce.
     * @return Response
     */
    public function edit(Response $response, Twig $twig, array $menu, string $chemin, array $allPostVars, int $id): Response
    {
        date_default_timezone_set('Europe/Paris');

        function isEmail(string $email): bool {
            return preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email);
        }

        $nom = trim($allPostVars['nom']);
        $email = trim($allPostVars['email']);
        $phone = trim($allPostVars['phone']);
        $ville = trim($allPostVars['ville']);
        $departement = trim($allPostVars['departement']);
        $categorie = trim($allPostVars['categorie']);
        $title = trim($allPostVars['title']);
        $description = trim($allPostVars['description']);
        $price = trim($allPostVars['price']);

        $errors = [];

        if (empty($nom)) $errors[] = 'Veuillez entrer votre nom';
        if (!isEmail($email)) $errors[] = 'Veuillez entrer une adresse mail correcte';
        if (empty($phone) || !is_numeric($phone)) $errors[] = 'Veuillez entrer votre numéro de téléphone';
        if (empty($ville)) $errors[] = 'Veuillez entrer votre ville';
        if (!is_numeric($departement)) $errors[] = 'Veuillez choisir un département';
        if (!is_numeric($categorie)) $errors[] = 'Veuillez choisir une catégorie';
        if (empty($title)) $errors[] = 'Veuillez entrer un titre';
        if (empty($description)) $errors[] = 'Veuillez entrer une description';
        if (empty($price) || !is_numeric($price)) $errors[] = 'Veuillez entrer un prix';

        if (!empty($errors)) {
            return $twig->render($response, "add-error.html.twig", [
                "breadcrumb" => $menu,
                "chemin"     => $chemin,
                "errors"     => $errors
            ]);
        } else {
            $this->annonce = Annonce::find($id);
            $idannonceur = $this->annonce->id_annonceur;
            $this->annonceur = Annonceur::find($idannonceur);

            $this->annonceur->email = htmlentities($allPostVars['email']);
            $this->annonceur->nom_annonceur = htmlentities($allPostVars['nom']);
            $this->annonceur->telephone = htmlentities($allPostVars['phone']);
            $this->annonce->ville = htmlentities($allPostVars['ville']);
            $this->annonce->id_departement = $allPostVars['departement'];
            $this->annonce->prix = htmlentities($allPostVars['price']);
            $this->annonce->mdp = password_hash($allPostVars['psw'], PASSWORD_DEFAULT);
            $this->annonce->titre = htmlentities($allPostVars['title']);
            $this->annonce->description = htmlentities($allPostVars['description']);
            $this->annonce->id_categorie = $allPostVars['categorie'];
            $this->annonce->date = date('Y-m-d');

            $this->annonceur->save();
            $this->annonceur->annonce()->save($this->annonce);

            return $twig->render($response, "modif-confirm.html.twig", [
                "breadcrumb" => $menu,
                "chemin"     => $chemin
            ]);
        }
    }
}
