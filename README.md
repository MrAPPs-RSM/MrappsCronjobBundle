# Mrapps Cronjob Bundle
Gestione cronjob automatici - Symfony2

## Requisiti

  - PHP 5.4+
  - Symfony2 2.6+
  - Mrapps BackendBundle

## Installazione

composer.json:
```json
{
	"require": {
		"mrapps/cronjobbundle": "dev-master"
	},
	"repositories": [
        {
          "type": "vcs",
          "url": "https://github.com/MrAPPs-RSM/MrappsCronjobBundle.git"
        }
    ]
}
```

AppKernel.php:
```php
$bundles = array(
    [...]
    new Mrapps\CronjobBundle\MrappsCronjobBundle(),
);
```

routing.yml:
```yaml
mrapps_cronjob:
    resource: "@MrappsCronjobBundle/Controller/"
    type:     annotation
    prefix:   /
```

config.yml:
```yaml
mrapps_cronjob: ~
```

## Utilizzo


Generazione delle voci nella sidebar:
```!/bin/bash
app/console mrapps:backend:buildsidebar
```


Esecuzione nuova chiamata:
```!/bin/bash
[BASE_URL]/mrapps_cronjob/nextstep
```