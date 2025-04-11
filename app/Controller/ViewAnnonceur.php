<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Annonceur;
use App\Model\Photo;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class ViewAnnonceur {

    /**
     * Affiche les informations de l'annonceur et ses annonces.
     *
     * @param Response $response La réponse HTTP.
     * @param Twig $twig L'instance Twig pour le rendu de template.
     * @param array $menu Le menu de navigation.
     * @param string $chemin Le chemin de base pour les URLs.
     * @param int $n L'ID de l'annonceur.
     * @param array $cat Les catégories d'annonces.
     * @return Response
     */
    public function afficherAnnonceur(Response $response, Twig $twig, array $menu, string $chemin, int $n, array $cat): Response {
        $this->annonceur = Annonceur::find($n);
        if (!isset($this->annonceur)) {
            return $response->withStatus(404)->write("404 - Annonceur non trouvé");
        }

        $tmp = Annonce::where('id_annonceur', '=', $n)->get();

        $annonces = [];
        foreach ($tmp as $a) {
            $a->nb_photo = Photo::where('id_annonce', '=', $a->id_annonce)->count();
            
            if ($a->nb_photo > 0) {
                $a->url_photo = Photo::select('url_photo')
                    ->where('id_annonce', '=', $a->id_annonce)
                    ->first()->url_photo;
            } else {
                $a->url_photo = $chemin . '/img/noimg.png'; 
            }

            $annonces[] = $a;
        }

        return $twig->render($response, "annonceur.html.twig", [
            'nom'        => $this->annonceur,
            'chemin'     => $chemin,
            'annonces'   => $annonces,
            'categories' => $cat
        ]);
    }
}
