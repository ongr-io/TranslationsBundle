Setup
=====

Step 1: Install Translations bundle
-----------------------------------

Translations bundle is installed using `Composer <https://getcomposer.org>`_.

.. code:: bash

    php composer.phar require ongr/translations-bundle "dev-master"

Step 2: Enable Translations bundle
----------------------------------

Bundle uses ElasticsearchBundle_ for storing translations in Elasticsearch_ index.

Bundle provides UI for editing translations strings. To use bundled UI, you need to enable FOSJsRoutingBundle_.

BazingaJsTranslationBundle_ is used to expose symfony application translations to Javascript.

angular-symfony-translation_ is used for BazingaJsTranslationBundle_ integration with AngularJs.

FilterManagerBundle_ is used in UI for filtering translations.

.. _ElasticsearchBundle: https://github.com/ongr-io/ElasticsearchBundle
.. _Elasticsearch: http://www.elasticsearch.org/
.. _FilterManagerBundle: https://github.com/ongr-io/FilterManagerBundle
.. _FOSJsRoutingBundle: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
.. _BazingaJsTranslationBundle: https://github.com/willdurand/BazingaJsTranslationBundle
.. _angular-symfony-translation: https://github.com/boxuk/angular-symfony-translation

.. code:: php

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new ONGR\TranslationsBundle\ONGRTranslationsBundle(),
            new ONGR\FilterManagerBundle\ONGRFilterManagerBundle(),
            // ...
        );
    }

Step 3: Add configuration
-------------------------

Add minimal configuration for TranslationsBundle bundle.

.. code:: yaml

    #app/config/config.yml
    ongr_translations:
        managed_locales: ["lt", "de", "en"]

.. note::

    Guide for installing and configuring ElasticsearchBundle_ can be found `here <http://ongr.readthedocs.org/en/latest/components/ElasticsearchBundle/setup.html>`_.

More about bundles `configuration tree <configuration.html>`_.

Step 4: Add configuration for routing
-------------------------------------

Import ``TranslationsBundle`` API and UI routes:

.. code-block:: yaml

    #app/config/routing.yml

    ongr_translation_ui:
        resource: "@ONGRTranslationsBundle/Resources/config/routing.yml"
        prefix:   /translations

    fos_js_routing:
        resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

    _bazinga_jstranslation:
        resource: "@BazingaJsTranslationBundle/Resources/config/routing/routing.yml"


Step 5: Dump JavaScript translations
------------------------------------

This is done using BazingaJsTranslationBundle_ with command ``php app/console bazinga:js-translation:dump [target]``. Please refer to `bundles documentation <https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/Resources/doc/index.md>`_ for more information.


Step 6: Using your new bundle
-----------------------------

.. note::

    Create Elasticsearch index if do not exists before using import command.

To start using your new bundle you should import data from translations files using bundles ``ongr:translations:import`` command. More details about using ``ongr:translations:import`` command can be found `Import command <import.html>`_.

After editing imported translation files you can export translations to ``app\Resources\translations\*domain*.*locale*.yml`` translations files. More details about ``ongr:translations:export`` command can be found `Export command <export.html>`_.
