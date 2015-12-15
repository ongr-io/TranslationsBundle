Import of Translations
===

Bundle provides `ongr:translations:import` command for importing translations into ES.

Command arguments
---

Comand has single optional argument - bundle namespace. If bundle namespace is
passed, command will import translations only from that bundle.

Example:

```bash
app/console ongr:translations:import AppBundle
```

Command options
---

Command accepts several options:

| Option      | Type  | Explanation
|-------------|-------|------------
| locales     | array | Locales to be imported. If not specified, `managed_locales` from bundles configuration will be used.
| domains     | array | Domains to import. If not specified, all domains will be imported.
| globals     | array | If specified, only globals translations will be imported from `app/Resources/translations`.
| config-only | array | If specified, command will import translations only from bundles specified in config.
