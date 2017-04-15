# SwaggerBundle

[![Travis](https://img.shields.io/travis/TimeIncOSS/swagger-bundle.svg?style=flat-square)](https://travis-ci.org/TimeIncOSS/swagger-bundle)
[![Packagist](https://img.shields.io/packagist/dt/timeinc/swagger-bundle.svg?style=flat-square)](https://packagist.org/packages/timeinc/swagger-bundle)

This bundle provides integration of [swagger-php](https://github.com/zircote/swagger-php) in [Symfony](https://symfony.com/).
It should only run in the dev environment, as searching for annotations at runtime is not performant. The bundle comes 
with the ability to generate a `swagger.json` file that can be statically served.

## Installation

Install via Composer:
```bash
composer require timeinc/swagger-bundle --dev
```

## Enable Bundle
In your `app/AppKernel.php` file, add the following to your list of dev bundles:
```php
<?php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        // ...

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            // ...
            $bundles[] = new TimeInc\SwaggerBundle\SwaggerBundle();
        }

        // ...
    }
}

```

## Configure Bundle

### Application Configuration
Open `./app/config/config_dev.yml` and add your configuration like below.

```yaml
swagger:
    version: '2.0'
    info:
        title: 'My API'
        version: '1.0.0'
        description: 'My API Description'
    host: 'my-api-host:8080'
    base_path: '/v2'
    schemes:
        - https
    alternative_hosts:
        - { name: 'production', host: 'productionhost', schemes: [https], base_path: '/api' }
    produces:
        - application/json
    consumes:
        - application/json
    annotations:
        bundles:
            - AcmeDemoBundle
    pretty: true
```

The full [configuration reference](docs/configuration-reference.md) for a comprehensive list of all values and defaults.

### Routing

The bundle comes with routes to view the default `swagger.json` schema or schemas with overridden host, schemes and base_path as defined by your presets. To enable the routes, add the following to your 
`./app/config/routing_dev.yml`:

```yaml
_swagger:
    resource: "@SwaggerBundle/Resources/config/routing.xml"
    prefix:   /
```

You can then access your default schema through `/_swagger/swagger.json` or your alternative schemas through `/_swagger/swagger-{alternative-host-name}.json`. As noted before, this should not be added to the 
production routes.

## Usage

Once you have registered your bundles under `swagger > bundles` in `config_dev.yml`, the swagger-bundle will 
automatically search for any [swagger-php](https://github.com/zircote/swagger-php) annotations under:

- [bundle_dir]/Controller
- [bundle_dir]/Document
- [bundle_dir]/Entity
- [bundle_dir]/Form
- [bundle_dir]/Model
- [bundle_dir]/Swagger

Please see swagger-php's [annotation reference](https://github.com/zircote/swagger-php/blob/master/docs/Getting-started.md) 
for details about swagger-php's syntax.

### Symfony Routes

This bundle comes with an extra `\TimeInc\SwaggerBundle\Swagger\Annotation\Route` annotation that allows you to define
a symfony route as a swagger endpoint. using the annotation will set a swagger path for each method and add parameters
for the path variables and any defaults as query strings.

The annotation can be defined on a class or a method with the following properies:

| Property    | Context       | Default | Notes |
|-------------|---------------|---------|-------|
| method      | CLASS         |         | The method name to inspect   |
| route       | CLASS\|METHOD |         | The Symfony route to inspect |
| returns     | CLASS\|METHOD | entity  | The response data type (collection\|entity) |
| entity      | CLASS\|METHOD |         | The entity that is returned. If omitted, will be guessed based on controller name |
| queryParams | CLASS\|METHOD | []      | Any extra query parameters as Key/Value array of queryParameter/dataType |
| headers     | CLASS\|METHOD | []      | Any extra header parameters as Key/Value array of headerName/dataType |

> NOTE: If the entity is omitted, the bundle will search for an entity name of the same name as the controller in the 
  same bundle. For Example, the below example controller is `Acme\PetBundle\Controller\PetController`, so the bundle
  will search for an entity in `Acme\PetBundle\Entity\Pet`

```php
<?php

namespace Acme\PetBundle\Controller;

use TimeInc\SwaggerBundle\Swagger\Annotation\Route;

/**
 * @Route(
 *     method="cgetAction",
 *     route="api_get_pets",
 *     returns="collection"
 * )
 */
class PetController
{
    /**
     * This annotation defines no entity. The bundle will check an entity exists in:
     *     Acme\PetBundle\Entity\Pet
     * 
     * @Route(
     *     route="api_get_pet"
     * )
     */
    public function getAction()
    {}
    
    /**
     * @Route(
     *     route="api_get_pet_owner",
     *     entity="Acme\PetBundle\Entity\Owner",
     *     headers={"X-TEST": "string"},
     *     queryParams={"query": "string", "page": "integer"}
     * )
     */
    public function getOwnerAction()
    {}   
}
```

### Command Line

You can dump the json schema to the console with the `swagger:dump` command:

```bash
./bin/console -e=dev swagger:dump --pretty
```

> HINT: You can use the option `--alternative-host=product` to override host, schemes and base_path with your production preset

## API Gateway

The bundle can also generate a swagger schema for an 
[AWS API Gateway](https://aws.amazon.com/api-gateway/) using the HTTP 
Proxy.

The schema can then be imported into the AWS console to generate a 1-1 
route mapping over a HTTP proxy for your API. All parameters and headers
and imported into AWS and passed through.

### API Gateway

Please [see the docs](docs/api-gateway.md) on how to integrate your API with API Gateway.
