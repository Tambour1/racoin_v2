<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Photo;
use App\Model\Annonceur;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class Index
{
    protected array $annonce = [];

    /**
     * Affiche toutes les annonces
     * 
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param array $cat Les catégories d'annonces.
     * @return Response
     */
    public function displayAllAnnonce(Response $response, Twig $twig, array $menu, string $chemin, array $cat): Response
    {
        $menu = [
            ['href' => $chemin, 'text' => 'Accueil']
        ];

        $this->getAll($chemin);

        return $twig->render($response, "index.html.twig", [
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $this->annonce
        ]);
    }

    /**
     * Récupère toutes les annonces
     * 
     * @return void
     */
    public function getAll(): void
    {
        $tmp     = Annonce::with("Annonceur")->orderBy('id_annonce', 'desc')->take(12)->get();
        $annonce = [];

        foreach ($tmp as $t) {
            $t->nb_photo = Photo::where("id_annonce", "=", $t->id_annonce)->count();
            if ($t->nb_photo > 0) {
                $t->url_photo = Photo::select("url_photo")
                    ->where("id_annonce", "=", $t->id_annonce)
                    ->first()->url_photo;
            } else {
                $t->url_photo = '/img/noimg.png';
            }

            $t->nom_annonceur = Annonceur::select("nom_annonceur")
                ->where("id_annonceur", "=", $t->id_annonceur)
                ->first()->nom_annonceur;

            $annonce[] = $t;
        }

        $this->annonce = $annonce;
    }
}
