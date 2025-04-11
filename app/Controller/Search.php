<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Categorie;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class Search {

    /**
     * Affiche la page de recherche.
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

        return $twig->render($response, "search.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat
        ]);
    }

    /**
     * Effectue la recherche d'annonces selon les critères fournis.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param array $cat Les catégories d'annonces.
     * @param array $array Les critères de recherche.
     * @return Response
     */
    public function research(Response $response, Twig $twig, array $menu, string $chemin, array $cat, array $array): Response {
        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . "/search", 'text' => "Résultats de la recherche"]
        ];

        $nospace_mc = str_replace(' ', '', $array['motclef']);
        $nospace_cp = str_replace(' ', '', $array['codepostal']);

        $query = Annonce::select();

        if (
            ($nospace_mc === "") &&
            ($nospace_cp === "") &&
            (($array['categorie'] === "Toutes catégories" || $array['categorie'] === "-----")) &&
            ($array['prix-min'] === "Min") &&
            (($array['prix-max'] === "Max") || ($array['prix-max'] === "nolimit"))
        ) {
            $annonce = Annonce::all();
        } else {
            if ($nospace_mc !== "") {
                $query->where('description', 'like', '%' . $array['motclef'] . '%');
            }

            if ($nospace_cp !== "") {
                $query->where('ville', '=', $array['codepostal']);
            }

            if ($array['categorie'] !== "Toutes catégories" && $array['categorie'] !== "-----") {
                $categ = Categorie::select('id_categorie')->where('id_categorie', '=', $array['categorie'])->first()->id_categorie;
                $query->where('id_categorie', '=', $categ);
            }

            if ($array['prix-min'] !== "Min" && $array['prix-max'] !== "Max") {
                if ($array['prix-max'] !== "nolimit") {
                    $query->whereBetween('prix', [$array['prix-min'], $array['prix-max']]);
                } else {
                    $query->where('prix', '>=', $array['prix-min']);
                }
            } elseif ($array['prix-max'] !== "Max" && $array['prix-max'] !== "nolimit") {
                $query->where('prix', '<=', $array['prix-max']);
            } elseif ($array['prix-min'] !== "Min") {
                $query->where('prix', '>=', $array['prix-min']);
            }

            $annonce = $query->get();
        }

        return $twig->render($response, "index.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "annonces"   => $annonce,
            "categories" => $cat
        ]);
    }
}
