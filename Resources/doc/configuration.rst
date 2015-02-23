Configuration
-------------

Bundle can be configured for importing only certain locales/domains/formats.

Bundle configuration example:

.. code:: yaml

    ongr_translations:
        es_manager: "default"
        managed_locales: ["lt", "de", "en"]
        formats: ["yml", "xlf"]
        domains: ["messages"]
        bundles:
            - ONGR\DemoBundle\ONGRDemoBundle

Elasticsearch bundle configuration example:

.. code:: yaml

    ongr_elasticsearch:
        connections:
            default:
                hosts:
                    - { host: 127.0.0.1:9200 }
                index_name: ongr-translations
                settings:
                    refresh_interval: -1
                    number_of_replicas: 1
        managers:
            default:
                connection: default
                mappings:
                    - ONGRTranslationsBundle


Configuration tree nodes:

=============== ============= ======== =========================================
Node            Default value Required Explanation
=============== ============= ======== =========================================
es_manager      default       No       Es manager for storing translations.
managed_locales               Yes      Locales to be imported.
formats         []            no       Translation files formats to be imported.
domains         []            No       Domains to be imported.
bundles         []            No       Additional bundles to import.
=============== ============= ======== =========================================
