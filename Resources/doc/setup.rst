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

FilterManagerBundle_ is used in UI for filtering translations.

.. _ElasticsearchBundle: https://github.com/ongr-io/ElasticsearchBundle
.. _Elasticsearch: http://www.elasticsearch.org/
.. _FilterManagerBundle: https://github.com/ongr-io/FilterManagerBundle
.. _FOSJsRoutingBundle: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle

.. code:: php

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
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
        repository: 'es.manager.translation.translation'

.. note::

    Guide for installing and configuring ElasticsearchBundle_ can be found `here <http://ongr.readthedocs.org/en/latest/components/ElasticsearchBundle/setup.html>`_.

.. note::

    We recommend to create standalone manager for translation:

.. code:: yaml

    #app/config/config.yml
    ongr_elasticsearch:
        managers:
            translation:
                connection: translation
                debug: true
                mappings:
                    - ONGRTranslationsBundle

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


Step 5: Using your new bundle
-----------------------------

.. note::

    Create Elasticsearch index if do not exists before using import command.

To start using your new bundle you should import data from translations files using bundles ``ongr:translations:import`` command. More details about using ``ongr:translations:import`` command can be found `Import command <import.html>`_.

After editing imported translation files you can export translations to ``app\Resources\translations\*domain*.*locale*.yml`` translations files. More details about ``ongr:translations:export`` command can be found `Export command <export.html>`_.
