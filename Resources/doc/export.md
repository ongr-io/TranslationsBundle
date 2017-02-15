# Exporting Translations

Eventually when you have translated all messages you needed to, you will need to export
your translations. This bundle provides `ongr:translations:export` command to
do that for you. It overrides the translation files that the bundle initially
imported them from.

> Note that for now the bundle only supports exporting to .yml format. Exporters
for other formats will be presented soon.

### Using Command

Translations can be exported using a button in the users interface. In fact, pressing it
triggers this very command in the background, without any specified options. If you want 
more flexibility, you can run the command in the console also specifying options to alter
its behaviour. By default, the command will only attempt to export changed messages, but
this can be modified as well.

Command accepts several options:

| Option  | Type  | Explanation
|---------|-------|------------
| locales | array | Locales to be exported. If not specified, `managed_locales` from bundles configuration will be used.
| domains | array | Domains to export. If not specified, all domains will be exported.
| force   | -     | If specified all translations will be exported, not just modified ones.

You can simply run this command without any arguments or options. This will
export changed translation messages for all locales and domains:

```bash
$ bin/console ongr:translations:export
```

Or you can set `locales` or `domains` options and limit the scope of exported
translations:                     

```bash
$ bin/console ongr:translations:export --locales=en --domains=messages
```

Also, if you wish, you can force the bundle to export all messages:

```bash
$ bin/console ongr:translations:export --force
```
