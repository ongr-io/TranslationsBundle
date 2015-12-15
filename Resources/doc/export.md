Export of Translations
===

This bundle provides `ongr:translations:export` command for exporting
translations to `app/Resources/translations` directory.

Command options
---

Command accepts several options:

| Option  | Type  | Explanation
|---------|-------|------------
| locales | array | Locales to be exported. If not specified, `managed_locales` from bundles configuration will be used.
| domains | array | Domains to export. If not specified, all domains will be exported.

Examples
---

You can simply run this command without any arguments or options. This will
export translations for all locales and domains:

```bash
$ app/console ongr:translations:export
```

Or you can set `locales` or `domains` options and limit the scope of exported
translations:                     

```bash
$ app/console ongr:translations:export --locales=en --domains=messages
```

Example above exports only translations in "en" locale and "messages" domain.
