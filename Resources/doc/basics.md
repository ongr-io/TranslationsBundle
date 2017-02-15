# Basic Usage

This section provides information about the normal workflow and considerations
for the bundle usage.

### Getting started

After the bundle is [configured][1] you can access the translations list. If you
did not import anything at this point, you will notice that the list is empty. 
This bundle is aimed at modifying the translations, that is why you will not find
buttons for creating or deleting a translation. If you are using this bundle, most
likely you already have translation files, so you should simply import them. 

Run this command to import all available translations:

```bash

$ bin/console ongr:translations:import

```

If you need to import only specific translations please refer to the [import][2]
section for more details on the command.

> **Warning!** Please have in mind that if you already have existing translations in
elasticsearch and simply want to load new ones, this command will overwrite any matching
translation documents, so you should always export your changes first or all changes will
be lost.

### Using the interface

After running this command you will have translations that you can work with. 
You can filter them by domain, or specify translation specific tags in *edit* mode.
Note, however, that the tags should be seen more like temporary notices, because once
you rerun the import command all of them will be lost.

You can make changes to the messages by simply clicking on them or entering the *edit*
mode. Once a change is made, the old value is recorded to history and you can check it
at any time by pressing the *history* button.

Once you have finished editing the messages, you must export them back to the translation
files so you could use the new values in twig. You can press the *export* button in the
interface. This way you will see all the pending changes that are ready to be committed.
If you only need to export specific values, you can check out the [export][3] section.

### Advanced usage

This bundle provides ways to dynamically handle its behaviour. If you need more flexibility
on importing or exporting data, you can check the dedicated pages for each of them. Also
the bundle handles everything through an API, so if you are interesting in customizing 
a specific part of translation handling, please refer to the [API][4] section of the 
documentation.


[1]: http://docs.ongr.io/TranslationsBundle/configuration
[2]: http://docs.ongr.io/TranslationsBundle/import
[3]: http://docs.ongr.io/TranslationsBundle/export
[3]: http://docs.ongr.io/TranslationsBundle/api
