<?php

namespace App\Controller;

use App\Model\Categorie;
use App\Model\Annonce;
use App\Model\Photo;
use App\Model\Annonceur;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class GetCategorie
{

    protected array $categories = [];
    protected array $annonce = [];

    /**
     * Récupère toutes les catégories triées par nom
     * 
     * @return array 
     */
    public function getCategories(): array
    {
        return Categorie::orderBy('nom_categorie')->get()->toArray();
    }

    /**
     * Récupère le contenu des annonces pour une catégorie spécifique
     * 
     * @param string $chemin Le chemin de base pour les URLs
     * @param int $n L'ID de la catégorie
     * @return void
     */
    public function getCategorieContent(string $chemin, int $n): void
    {
        $tmp = Annonce::with("Annonceur")
            ->orderBy('id_annonce', 'desc')
            ->where('id_categorie', "=", $n)
            ->get();

        $annonce = [];
        foreach ($tmp as $t) {
            $t->nb_photo = Photo::where("id_annonce", "=", $t->id_annonce)->count();
            if ($t->nb_photo > 0) {
                $t->url_photo = Photo::select("url_photo")
                    ->where("id_annonce", "=", $t->id_annonce)
                    ->first()->url_photo;
            } else {
                $t->url_photo = $chemin . '/img/noimg.png';
            }

            $t->nom_annonceur = Annonceur::select("nom_annonceur")
                ->where("id_annonceur", "=", $t->id_annonceur)
                ->first()->nom_annonceur;

            $annonce[] = $t;
        }
        $this->annonce = $annonce;
    }

    /**
     * Affiche la vue pour une catégorie spécifique
     * 
     * @param Response $response La réponse HTTP
     * @param Twig $twig L'instance Twig pour le rendu de template
     * @param array $menu Le menu de navigation
     * @param string $chemin Le chemin de base pour les URLs
     * @param array $cat Les catégories d'annonces
     * @param int $n L'ID de la catégorie
     * @return Response
     */
    public function displayCategorie(Response $response, Twig $twig, array $menu, string $chemin, array $cat, int $n ): Response {
        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . "/cat/" . $n, 'text' => Categorie::find($n)->nom_categorie]
        ];

        $this->getCategorieContent($chemin, $n);

        return $twig->render($response, "index.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $this->annonce
        ]);
    }
}
