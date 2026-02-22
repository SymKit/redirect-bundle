# Symkit Redirect Bundle

[![CI](https://github.com/symkit/redirect-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/symkit/redirect-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/symkit/redirect-bundle.svg)](https://packagist.org/packages/symkit/redirect-bundle)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)

Symfony bundle to manage URL redirects (internal and external) from the database, with validation, route integration, and global search.

## Requirements

- PHP 8.2+
- Symfony 7.0 or 8.0
- Doctrine ORM
- For the admin UI: `symkit/crud-bundle`, `symkit/metadata-bundle`, `symkit/menu-bundle`
- To link redirects to routes: `symkit/routing-bundle`
- For global search: `symkit/search-bundle`

## Installation

```bash
composer require symkit/redirect-bundle
```

Register the bundle in `config/bundles.php` (automatic with Flex):

```php
return [
    Symkit\RedirectBundle\RedirectBundle::class => ['all' => true],
];
```

## Configuration

All options are enabled by default. Example with explicit values:

```yaml
# config/packages/symkit_redirect.yaml
symkit_redirect:
    enabled: true
    doctrine:
        entity_class: Symkit\RedirectBundle\Entity\Redirect
        repository_class: Symkit\RedirectBundle\Repository\RedirectRepository
    admin:
        enabled: true
        route_prefix: admin_redirect
        path_prefix: /admin/redirects
    listener:
        enabled: true
    search:
        enabled: true
```

- **enabled**: Enable or disable the whole bundle.
- **doctrine.entity_class**: FQCN of the redirect entity (see "Custom entity").
- **doctrine.repository_class**: FQCN of the redirect repository.
- **admin.enabled**: Register the CRUD controller and admin routes.
- **admin.route_prefix**: Prefix for route names (e.g. `admin_redirect_list`, `admin_redirect_edit`).
- **admin.path_prefix**: URL path prefix (e.g. `/admin/redirects`).
- **listener.enabled**: Register the listener that performs redirects on each request.
- **search.enabled**: Register the redirect search provider for global search.

## Routes

Include the admin routes in your application (e.g. `config/routes.yaml`):

```yaml
symkit_redirect:
    resource: '@SymkitRedirectBundle/config/routes.yaml'
    prefix: '%symkit_redirect.admin.path_prefix%'
```

This registers: `admin_redirect_list`, `admin_redirect_create`, `admin_redirect_edit`, `admin_redirect_delete`.

## Usage

### Manual creation

```php
use Symkit\RedirectBundle\Entity\Redirect;

$redirect = new Redirect();
$redirect->setUrlFrom('/old-page');
$redirect->setUrlTo('/new-page');

$entityManager->persist($redirect);
$entityManager->flush();
```

### Admin interface

With `admin.enabled: true` and `symkit/crud-bundle`, `symkit/metadata-bundle`, and `symkit/menu-bundle` installed, the CRUD interface is available at the configured URL (default `/admin/redirects`).

### Redirects to an internal route

If `symkit/routing-bundle` is installed, you can choose an internal route as the destination instead of an external URL ("Internal Route" field in the form).

## Custom entity

To use your own entity (extra fields, behaviour, etc.):

1. Extend `Symkit\RedirectBundle\Entity\Redirect` or implement `Symkit\RedirectBundle\Contract\RedirectEntityInterface` with the same Doctrine mapping.
2. Configure the FQCN:

```yaml
symkit_redirect:
    doctrine:
        entity_class: App\Entity\MyRedirect
```

3. Map your entity in Doctrine (XML or attributes) as usual.

## Validation

The bundle validates in particular:

- Relative URLs starting with `/`.
- No redirect from an URL to itself.
- Unique source (one redirect per source URL).
- A destination must be set (URL or internal route).

Validation messages live in the `validators` domain (bundle files `validators.*.xlf`).

## Global search

With `search.enabled: true` and `symkit/search-bundle`, redirects are indexed in global search (by source or destination URL). The displayed category is translated (domain `SymkitRedirectBundle`).

## Translations

- Domain: **SymkitRedirectBundle**.
- XLIFF files in `translations/`: `SymkitRedirectBundle.en.xlf`, `SymkitRedirectBundle.fr.xlf`.
- Constraint messages: `validators.en.xlf`, `validators.fr.xlf` (domain `validators`).

Form labels, admin list, and search use this domain.

## Tests

The bundle is covered by **unit** tests (services, form, validation), **integration** tests (bundle boot with TestKernel, Doctrine repository), and **functional** tests (HTTP request → 308 redirect). Run the suite with `make test`. Full quality (style, static analysis, architecture, mutation) runs via `make quality`; the mutation score (Infection) target is at least 64% on covered code.

## Contributing

```bash
make install
make cs-fix
make phpstan
make test
make quality         # cs-check + phpstan + deptrac + lint + test + infection
make ci              # security-check + quality
```

## License

MIT.
