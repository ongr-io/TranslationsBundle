Export
======
Bundle provides ``ongr:translations:export`` command for exporting translations to ``app/Resources/translations``.

Command options
~~~~~~~~~~~~~~~

Command accepts several options:

======= ========== =====  ================
Option  Short name Type   Name Explanation
======= ========== =====  ================
locales -l         array  Locales to be exported. If not specified, ``managed_locales`` from bundles configuration will be used.
domains -d         array  Domains to export. If not specified, all domains will be exported.
======= ========== =====  ================
