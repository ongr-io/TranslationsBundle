Configuration
===

This bundle can be configured to work with specific locales, domains and
formats.

Bundle configuration example:

```yml
ongr_translations:
    repository: "es.manager.default.translation"
    managed_locales: ["lt", "de", "en"]
    formats: ["yml", "xlf"]
    domains: ["messages"]
    list_size: 10000
    bundles:
        - ONGR\DemoBundle\ONGRDemoBundle
```

Configuration tree nodes:

| Node            | Default value | Required | Explanation
|-----------------|---------------|----------|------------
| repository      |               | Yes      | ES repository for storing translations.
| managed_locales |               | Yes      | Locales to be imported.
| list_size       | 1000          | No       | Maximum amount of translations rendered in a list.
| formats         | []            | No       | Translation files formats to be imported.
| domains         | []            | No       | Domains to be imported.
| bundles         | []            | No       | Additional bundles to import (Full namespace).
