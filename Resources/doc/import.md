Importing Translations
===

Most of the time you will start to work with existing translations, not from
scratch. In this case you need to import them first. This bundle provides
`ongr:translations:import` command to do this job. Keep reading to find out
what options it supports and how to use them.

Using Command
---

Command `ongr:translations:import` has single optional argument - bundle
namespace. If bundle namespace is passed, command will import translations only
from that bundle. Otherwise it will import translations from all bundles.

Example below shows how to import translations from single bundle.

```bash
app/console ongr:translations:import AppBundle
```

You can also limit scope of data by setting one or few of available options.
See table below to find out what options are available and how they change
behaviour of this command.

| Option      | Type  | Explanation
|-------------|-------|------------
| locales     | array | Locales to be imported. If not specified, `managed_locales` from bundles configuration will be used.
| domains     | array | Domains to import. If not specified, all domains will be imported.
| globals     | array | If specified, only globals translations will be imported from `app/Resources/translations`.
| config-only | array | If specified, command will import translations only from bundles specified in config.
       
Lets say you want to import translations from all bundles but only those who
are in `messages` domain. When simply limit the scope using `domains` option:
     
```bash
app/console ongr:translations:import --domains=messages
```
