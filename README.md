ONGR Translations Bundle
===

This bundle provides graphical user interface for translations management. It
allows to add, edit and remove translations in multiple languages. Translations
can be automatically collected from and dumped into your project. 

[![Stable Release](https://poser.pugx.org/ongr/translations-bundle/v/stable.svg)](https://packagist.org/packages/ongr/translations-bundle)
[![Build Status](https://travis-ci.org/ongr-io/TranslationsBundle.svg?branch=master)](https://travis-ci.org/ongr-io/TranslationsBundle)
[![Coverage](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/?branch=master)
[![Quality Score](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ongr-io/TranslationsBundle/?branch=master)

Documentation
---

The documentation of the bundle can be found in [Resources/doc/][2]

Installation
---

Follow 5 quick steps to get ready to translate.

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following
command to download the latest stable version of this bundle:

```bash
$ composer require ongr/translations-bundle
```

> This command requires you to have Composer installed globally, as explained in
> the [installation chapter][3] of the Composer documentation.

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

> __Note:__ This bundle uses [ONGRElasticsearchBundle][4] to store translations.
Also [ONGRFilterManagerBundle][5] and [FOSJsRoutingBundle][6] bundles are used
to build user interface.

### Step 3: Import Routing          
          
Import API and UI routes:

```yml
# app/config/routing.yml
ongr_translation_ui:
    resource: "@ONGRTranslationsBundle/Resources/config/routing.yml"
    prefix:   /translations

fos_js_routing:
    resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"
```

### Step 4: Configure Elasticsearch Bundle  

This bundle provides `Translation` document. Add this bundle to your ES
manager's mapping to associate it:

```yml                
# app/config/config.yml
ongr_elasticsearch:
    # ...
    managers:
        default:
            # ...
            mappings:
                # ...
                - AppBundle
                - ONGRTranslationsBundle
```

Once the bundle is added open console and run command to update mapping in
Elasticsearch:

```bash
$ app/console ongr:es:mapping:update --force
```

### Step 5: Configure the Bundle

This bundle requires minimal configuration to get started:

```yml
# app/config/config.yml
ongr_translations:
    managed_locales: ["en", "de", "lt"]
    repository: 'es.manager.default.translation'
    exporter: ONGR\TranslationsBundle\Translation\Export\YmlExport
```

In the example above `managed_locales` defines locales we are working with,
`repository` defines repository service for `Translation` document. (Your
repository ID may be different depending on what manager name you configured in
`ongr_elasticsearch` section.) and `exporter` defines which format we want to
 export (Yaml by default).

Enable Symfony translations component if you don not have it enabled yet:

```yml
framework:
    # ...
    translator: { fallbacks: ["%locale%"] }
```

That's it about setup. Follow next chapter to learn how to work with translations.

> For detailed [configuration reference][9] check dedicated documentation page.

Translate Your First Message!
---

Before starting to translate messages, you need to have some. Lets import
messages from this bundle. Here is a command `ongr:translations:import`
to do that:

```bash
$ app/console ongr:translations:import ONGRTranslationsBundle
```

Install assets and start web server if it's not running yet:

```bash
$ app/console assets:install
$ app/console server:start
```

Now open `http://127.0.0.1:8000/translations/list` in your browser. You should
see translations list. You can enter edit mode by clicking on message. Change
translation and click "ok" or press <kbd>Enter</kbd> to save. Click "x" or
press <kbd>Esc</kbd> to discard changes.

> Read more about [import][7] and [export of translations][8] in dedicated
documentation pages.

License
---

This package is licensed under the MIT license. For the full copyright and
license information, please view the [LICENSE][1] file that was distributed
with this source code. 

[1]: LICENSE
[2]: Resources/doc/index.md
[3]: https://getcomposer.org/doc/00-intro.md
[4]: https://github.com/ongr-io/ElasticsearchBundle
[5]: https://github.com/ongr-io/FilterManagerBundle
[6]: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle  
[7]: Resources/doc/import.md
[8]: Resources/doc/export.md
[9]: Resources/doc/configuration.md
