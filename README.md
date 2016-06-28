# TimeIncSwaggerBundle

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
    formats:
        - application/json
    bundles:
        - AcmeDemoBundle
```

The full [configuration reference](docs/annotation-reference.md) for a comprehensive list of all values and defaults.

### Routing

The bundle comes with a single route to view the `swagger.json` schema. To enable it, add the following to your 
`./app/config/routing_dev.yml`:

```yaml
_swagger:
    resource: "@SwaggerBundle/Resources/config/routing.xml"
    prefix:   /
```

You can then access your schema through `/_swagger/swagger.json`. As noted before, this should not be added to the 
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
a symfony route as a swagger endpoint.

The annotation can be defined on a class or a method with the following properies:

| Property | Context       | Default | Notes |
|----------|---------------|---------|-------|
| method   | CLASS         |         | The method name to inspect   |
| route    | CLASS\|METHOD |         | The Symfony route to inspect |
| returns  | CLASS\|METHOD | entity  | The response data type (collection\|entity) |
| entity   | CLASS\|METHOD |         | The entity that is returned. If omitted, will be guessed based on controller name |

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
     *     entity="Acme\PetBundle\Entity\Owner"
     * )
     */
    public function getOwnerAction()
    {}   
}
```

### Command Line

You can dump the json schema to the console with the `swagger:dump` command:

```bash
./bin/console -e=dev swagger:dump
```

> HINT: You can use python's `json.tool` module to pretty-print the output:
> > `./bin/console -e=dev swagger:dump | python -mjson.tool`
