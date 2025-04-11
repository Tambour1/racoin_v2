<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\{Index, GetCategorie, GetDepartment, Item, AddItem, Search, ViewAnnonceur, KeyGenerator};
use App\Model\{Annonce, Categorie, Annonceur, Departement};

return function (App $app) {

    $container = $app->getContainer();
    $twig      = $container->get('view');
    $menu      = $container->get('menu');
    $chemin    = $container->get('chemin');

    $cat = new GetCategorie();
    $dpt = new GetDepartment();

    $app->get('/', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
        $index = new Index();
        return $index->displayAllAnnonce($response, $twig, $menu, $chemin, $cat->getCategories());
    });

    $app->get('/item/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n    = $args['n'];
        $item = new Item();
        return $item->afficherItem($response, $twig, $menu, $chemin, $n, $cat->getCategories());
    });

    $app->get('/add', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat, $dpt) {
        $ajout = new AddItem();
        return $ajout->addItemView($response, $twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());
    });

    $app->post('/add', function (Request $request, Response $response) use ($twig, $menu, $chemin) {
        $post = $request->getParsedBody();
        $ajout = new AddItem();
        return $ajout->addNewItem($response, $twig, $menu, $chemin, $post);
    });

    $app->get('/item/{id}/edit', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin) {
        $id = $args['id'];
        $item = new Item();
        return $item->modifyGet($response,$twig, $menu, $chemin, $id);
    });

    $app->post('/item/{id}/edit', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat, $dpt) {
        $id = $args['id'];
        $post = $request->getParsedBody();
        $item = new Item();
        $item->modifyPost($twig, $menu, $chemin, $id, $post, $cat->getCategories(), $dpt->getAllDepartments());
        return $response;
    });

    $app->map(['GET', 'POST'], '/item/{id}/confirm', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin) {
        $id = $args['id'];
        $post = $request->getParsedBody();
        $item = new Item();
        return $item->edit($response,$twig, $menu, $chemin, $id, $post);
    });

    $app->get('/search', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
        $s = new Search();
        return $s->show($response, $twig, $menu, $chemin, $cat->getCategories());
    });

    $app->post('/search', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
        $post = $request->getParsedBody();
        $s = new Search();
        return $s->research($response, $twig, $menu, $chemin, $cat->getCategories(),$post);
    });

    $app->get('/annonceur/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n = $args['n'];
        $a = new ViewAnnonceur();
        return $a->afficherAnnonceur($response,$twig, $menu, $chemin, $n, $cat->getCategories());
    });

    $app->get('/del/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin) {
        $n = $args['n'];
        $item = new Item();
        return $item->supprimerItemGet($response,$twig, $menu, $chemin, $n);
    });

    $app->post('/del/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n = $args['n'];
        $item = new Item();
        return $item->supprimerItemPost($response,$twig, $menu, $chemin, $n, $cat->getCategories());
    });

    $app->get('/cat/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n = $args['n'];
        $categorie = new GetCategorie();
        return $categorie->displayCategorie($response, $twig, $menu, $chemin, $cat->getCategories(), $n);
    });

    $app->get('/api', function (Request $request, Response $response) use ($twig, $chemin) {
        $template = $twig->load('api.html.twig');
        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . '/api', 'text' => 'Api']
        ];
        echo $template->render(['breadcrumb' => $menu, 'chemin' => $chemin]);
        return $response;
    });

    // API GROUP
    $app->group('/api', function ($group) use ($twig, $chemin, $menu) {
        $cat = new GetCategorie();

        $group->get('/annonce/{id}', function (Request $request, Response $response, $args) {
            $id = $args['id'];
            $data = Annonce::select([
                'id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur',
                'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'
            ])->find($id);

            if (!$data) return $response->withStatus(404);
            $data->categorie = Categorie::find($data->categorie);
            $data->annonceur = Annonceur::select('email', 'nom_annonceur', 'telephone')->find($data->annonceur);
            $data->departement = Departement::select('id_departement', 'nom_departement')->find($data->departement);
            $data->links = ['self' => ['href' => "/api/annonce/{$data->id_annonce}"]];

            $response->getBody()->write($data->toJson());
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/annonces', function (Request $request, Response $response) {
            $data = Annonce::all(['id_annonce', 'prix', 'titre', 'ville']);
            foreach ($data as $a) {
                $a->links = ['self' => ['href' => "/api/annonce/{$a->id_annonce}"]];
            }
            $response->getBody()->write($data->toJson());
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/categorie/{id}', function (Request $request, Response $response, $args) {
            $id = $args['id'];
            $annonces = Annonce::where('id_categorie', '=', $id)->get(['id_annonce', 'prix', 'titre', 'ville']);
            foreach ($annonces as $a) {
                $a->links = ['self' => ['href' => "/api/annonce/{$a->id_annonce}"]];
            }
            $categorie = Categorie::find($id);
            $categorie->links = ['self' => ['href' => "/api/categorie/{$id}"]];
            $categorie->annonces = $annonces;

            $response->getBody()->write($categorie->toJson());
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/categories', function (Request $request, Response $response) {
            $data = Categorie::all();
            foreach ($data as $cat) {
                $cat->links = ['self' => ['href' => "/api/categorie/{$cat->id_categorie}"]];
            }
            $response->getBody()->write($data->toJson());
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->map(['GET', 'POST'], '/key', function (Request $request, Response $response) use ($twig, $menu, $chemin, $cat) {
            $kg = new KeyGenerator();
            if ($request->getMethod() === 'GET') {
                $kg->show($twig, $menu, $chemin, $cat->getCategories());
            } else {
                $nom = $request->getParsedBody()['nom'] ?? '';
                $kg->generateKey($twig, $menu, $chemin, $cat->getCategories(), $nom);
            }
            return $response;
        });

    });
};
