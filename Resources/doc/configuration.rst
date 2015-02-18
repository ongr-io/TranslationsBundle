Configuration
-------------

Bundle can be configured for importing only certain locales/domains/formats.

Full configuration example:

.. code:: yaml

    ongr_translations:
        es_manager: "default"
        managed_locales: ["lt", "de", "en"]
        formats: ["yml", "xlf"]
        domains: ["messages"]

Configuration tree nodes:

=============== ============= ======== =========================================
Node            Default value Required Explanation
=============== ============= ======== =========================================
es_manager      default       No       Es manager for storing translations.
managed_locales               Yes      Locales to be imported.
formats         []            no       Translation files formats to be imported.
domains:        []            No       Domains to be imported.
=============== ============= ======== =========================================
