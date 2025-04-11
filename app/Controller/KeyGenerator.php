<?php

namespace App\Controller;

use App\Model\ApiKey;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class KeyGenerator {

    /**
     * Affiche la page de génération de clé.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param array $cat Les catégories d'annonces.
     * @return Response
     */
    public function show(Response $response, Twig $twig, array $menu, string $chemin, array $cat): Response {
        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . "/search", 'text' => "Recherche"]
        ];

        return $twig->render($response, "key-generator.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat
        ]);
    }

    /**
     * Génère une clé API unique et la sauvegarde.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param array $cat Les catégories d'annonces.
     * @param string $nom Le nom pour lequel générer la clé.
     * @return Response
     */
    public function generateKey(Response $response, Twig $twig, array $menu, string $chemin, array $cat, string $nom): Response {
        $nospace_nom = str_replace(' ', '', $nom);

        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . "/search", 'text' => "Recherche"]
        ];

        if ($nospace_nom === '') {
            return $twig->render($response, "key-generator-error.html.twig", [
                "breadcrumb" => $menu,
                "chemin"     => $chemin,
                "categories" => $cat
            ]);
        } else {
            $key = uniqid();

            $apikey = new ApiKey();
            $apikey->id_apikey = $key;
            $apikey->name_key  = htmlentities($nom);
            $apikey->save();

            return $twig->render($response, "key-generator-result.html.twig", [
                "breadcrumb" => $menu,
                "chemin"     => $chemin,
                "categories" => $cat,
                "key"        => $key
            ]);
        }
    }
}
