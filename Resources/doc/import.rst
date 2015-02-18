Import
======

Bundle provides ``ongr:translations:import`` command for importing translations into ES.

Command arguments
~~~~~~~~~~~~~~~~~
Bundle has one argument - bundle name. If bundle name is passed, command will import translations only from that bundle.

Command options
~~~~~~~~~~~~~~~

Command accepts several options:

======= ========== =====  ================
Option  Short name Type   Name Explanation
======= ========== =====  ================
locales -l         array  Locales to be imported. If not specified, ``managed_locales`` from bundles configuration will be used.
domains -d         array  Domains to import. If not specified, all domains will be imported.
globals -g         array  If specified, only globals translations will be imported from ``app/Resources/translations``.
======= ========== =====  ================
