Exporting Translations
===

Eventually when you have translated all messages you needed to, you can export
your translations. This bundle provides `ongr:translations:export` command to
do that for you. It saves exported translations into `app/Resources/translations`
directory.

Using Command
---

Command accepts several options:

| Option  | Type  | Explanation
|---------|-------|------------
| locales | array | Locales to be exported. If not specified, `managed_locales` from bundles configuration will be used.
| domains | array | Domains to export. If not specified, all domains will be exported.

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
