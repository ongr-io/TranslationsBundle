ONGR Translations Bundle
===

This bundle provides graphical user interface for translations management. It
enables easy control and manipulation of translation files stored in multiple
domains in your entire project. Translations can be automatically collected 
from and dumped into your project. 

[![Stable Release](https://poser.pugx.org/ongr/translations-bundle/v/stable.svg)](https://packagist.org/packages/ongr/translations-bundle)
[![Build Status](https://travis-ci.org/ongr-io/TranslationsBundle.svg?branch=master)](https://travis-ci.org/ongr-io/TranslationsBundle)
[![Coverage](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/?branch=master)
[![Quality Score](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/?branch=master)

Documentation
---

The full documentation of the bundle can be found [here][1]

Installation
---

Follow 5 quick steps to get ready to translate.

### Step 1: Download the Bundle

FilterManager bundle is installed using [Composer][2]

```bash
# You can require any version you need, check the latest stable to make sure you are using the newest version.
$ composer require ongr/translations-bundle "~1.0"
```

> Please note that filter manager requires Elasticsearch bundle, guide on 
how to install and configure it can be found [here][3].

### Step 2: Enable the Bundle

Register bundles in `app/AppKernel.php`:

```php
class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            // ...
            new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
            new ONGR\FilterManagerBundle\ONGRFilterManagerBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new ONGR\TranslationsBundle\ONGRTranslationsBundle(),
        ];
    }

    // ...
}
```

> __Note:__ This bundle uses [ONGRElasticsearchBundle][3] to store translations.
Also [ONGRFilterManagerBundle][5] and [FOSJsRoutingBundle][6] bundles are used
to build user interface.

### Step 3: Import Routing          
          
Import API and UI routes:

```yml
# app/config/routing.yml
ongr_translation_ui:
    resource: "@ONGRTranslationsBundle/Resources/config/routing.yml"
    prefix:   /translations  # or any other prefix of your choice

fos_js_routing:
    resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"
```

### Step 4: Configure Elasticsearch Bundle  

This bundle relies on ONGR ElasticsearchBundle to store translations. You can
include this bundle in an existing managers mapping but we recommend to create
a separate index and manager for translations. More information on how to do 
that can be found in official ElasticsearchBundle [documentation][3].

### Step 5: Configure the Bundle

This bundle requires minimal configuration to get started:

```yml
# app/config/config.yml
ongr_translations:
    managed_locales: ["en", "de", "lt"]
    repository: 'es.manager.translations.translation'
```

In the example above `managed_locales` defines locales we are working with and
`repository` defines repository service for `Translation` document.

> Your repository ID may be different depending on what manager name you configured in
  `ongr_elasticsearch` section. In this case manager named `translations` is used

Lastly, enable Symfony translations component if you do not have it enabled yet:

```yml
framework:
    # ...
    translator: { fallbacks: ["%locale%"] }
```

That's it about setup. Follow next chapter to learn how to work with translations.

> For detailed [configuration reference][6] check dedicated documentation page.

Translate Your First Message!
---

Before starting to translate messages, you need to have some. Lets import
messages from this bundle. Here is a command `ongr:translations:import`
to do that:

```bash
$ bin/console ongr:translations:import ONGRTranslationsBundle
```

Install assets:

```bash
$ bin/console assets:install
```

Now open `http://127.0.0.1:8000/translations` in your browser. You should
see translations list. 

License
---

This package is licensed under the MIT license. For the full copyright and
license information, please view the [LICENSE][1] file that was distributed
with this source code. 

[1]: http://docs.ongr.io/TranslationsBundle
[2]: https://getcomposer.org
[3]: http://docs.ongr.io/ElasticsearchBundle
[4]: http://docs.ongr.io/FilterManagerBundle
[5]: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
[6]: http://docs.ongr.io/TranslationsBundle/configuration
