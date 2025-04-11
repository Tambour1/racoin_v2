## 1 - Mise en place de test 
- ./vendor/bin/phpunit à la racine pour lancer les tests du dossier "tests" avec PHPUnit
- Tests sur Additem

## 2 - Architecture
- Renommage de tous les fichiers PHP avec une majuscule
- Dossier "app" qui contient les controlleurs, les models et la connection à la base de données
- "index.php" déplacé dans le dossier "public"

## 3 - Mise à jour des packages
- Passage à Slim 4
- Utilisation de PHP DI comme gestionnaire de dépendances
- Utilisation de Slim-Twig-view pour combiné Twig avec Slim
- Utilisation de http-message pour gérer les réponses Http dans les rendus Twig

## 4 - Amélioration du code
- Standards PHP 8 sur le typage, les types de retour et les types des paramètres des fonctions 

## 5 - Réfactorisation
- Refactorisation principale sur les controlleurs et changement de "index.php" pour correspondre à Slim 4
- Passage en PSR-4 notamment pour les namespaces

## 7 - Ajouter une documentation
- Documentation PHP classique sur les controlleurs






