# Symkit Redirect Bundle

[![CI](https://github.com/symkit/redirect-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/redirect-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/redirect-bundle.svg)](https://packagist.org/packages/symkit/redirect-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

Bundle Symfony pour gérer des redirections d’URL (internes et externes) depuis la base de données, avec validation, intégration aux routes et à la recherche globale.

## Prérequis

- PHP 8.2+
- Symfony 7.0 ou 8.0
- Doctrine ORM
- Pour l’interface d’administration : `symkit/crud-bundle`, `symkit/metadata-bundle`, `symkit/menu-bundle`
- Pour lier des redirections à des routes : `symkit/routing-bundle`
- Pour la recherche globale : `symkit/search-bundle`

## Installation

```bash
composer require symkit/redirect-bundle
```

Enregistrez le bundle dans `config/bundles.php` (automatique avec Flex) :

```php
return [
    Symkit\RedirectBundle\RedirectBundle::class => ['all' => true],
];
```

## Configuration

Toutes les options sont activées par défaut. Exemple avec valeurs explicites :

```yaml
# config/packages/symkit_redirect.yaml
symkit_redirect:
    enabled: true
    doctrine:
        entity_class: Symkit\RedirectBundle\Entity\Redirect
    admin:
        enabled: true
        route_prefix: admin_redirect
        path_prefix: /admin/redirects
    listener:
        enabled: true
    search:
        enabled: true
```

- **enabled** : active ou désactive tout le bundle.
- **doctrine.entity_class** : FQCN de l’entité de redirection (voir « Entité personnalisée »).
- **admin.enabled** : enregistre le contrôleur CRUD et les routes d’administration.
- **admin.route_prefix** : préfixe des noms de routes (ex. `admin_redirect_list`, `admin_redirect_edit`).
- **admin.path_prefix** : préfixe des chemins d’URL (ex. `/admin/redirects`).
- **listener.enabled** : enregistre le listener qui effectue les redirections sur chaque requête.
- **search.enabled** : enregistre le fournisseur de recherche pour la recherche globale.

## Routes

Incluez les routes d’administration dans votre application (ex. `config/routes.yaml`) :

```yaml
symkit_redirect:
    resource: '@SymkitRedirectBundle/config/routes.yaml'
    prefix: '%symkit_redirect.admin.path_prefix%'
```

Cela enregistre notamment : `admin_redirect_list`, `admin_redirect_create`, `admin_redirect_edit`, `admin_redirect_delete`.

## Utilisation

### Création manuelle

```php
use Symkit\RedirectBundle\Entity\Redirect;

$redirect = new Redirect();
$redirect->setUrlFrom('/old-page');
$redirect->setUrlTo('/new-page');

$entityManager->persist($redirect);
$entityManager->flush();
```

### Interface d’administration

Avec `admin.enabled: true` et les bundles `symkit/crud-bundle`, `symkit/metadata-bundle` et `symkit/menu-bundle` installés, l’interface CRUD est disponible à l’URL configurée (par défaut `/admin/redirects`).

### Redirections vers une route interne

Si `symkit/routing-bundle` est installé, vous pouvez choisir une route interne comme destination au lieu d’une URL externe (champ « Internal Route » dans le formulaire).

## Entité personnalisée

Pour utiliser votre propre entité (champs supplémentaires, comportements, etc.) :

1. Étendez `Symkit\RedirectBundle\Entity\Redirect` ou implémentez `Symkit\RedirectBundle\Contract\RedirectEntityInterface` avec le même mapping Doctrine.
2. Configurez le FQCN :

```yaml
symkit_redirect:
    doctrine:
        entity_class: App\Entity\MyRedirect
```

3. Mappez votre entité dans Doctrine (XML ou attributs) comme d’habitude.

## Validation

Le bundle valide notamment :

- URLs relatives commençant par `/`.
- Absence de redirection d’une URL vers elle-même.
- Unicité de la source (une seule redirection par URL source).
- Présence d’une destination (URL ou route interne).

Les messages de validation sont dans le domaine `validators` (fichiers `validators.*.xlf` du bundle).

## Recherche globale

Avec `search.enabled: true` et `symkit/search-bundle`, les redirections sont indexées dans la recherche globale (par URL source ou destination). La catégorie affichée est traduite (domaine `SymkitRedirectBundle`).

## Traductions

- Domaine : **SymkitRedirectBundle**.
- Fichiers XLIFF dans `translations/` : `SymkitRedirectBundle.en.xlf`, `SymkitRedirectBundle.fr.xlf`.
- Messages de contraintes : `validators.en.xlf`, `validators.fr.xlf` (domaine `validators`).

Les libellés du formulaire, de la liste admin et de la recherche utilisent ce domaine.

## Contribuer

```bash
make install
make install-hooks   # Hook commit pour retirer Co-authored-by
make cs-fix
make phpstan
make test
make quality         # cs-check + phpstan + deptrac + lint + test + infection
make ci              # security-check + quality
```

## Licence

MIT.
