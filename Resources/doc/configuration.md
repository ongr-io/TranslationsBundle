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
    bundles:
        - ONGR\DemoBundle\ONGRDemoBundle
```

Elasticsearch bundle configuration example:

```yml
ongr_elasticsearch:
    connections:
        default:
            hosts:
                - 127.0.0.1:9200
            index_name: ongr-translations
            settings:
                refresh_interval: -1
                number_of_replicas: 1
    managers:
        default:
            connection: default
            mappings:
                - ONGRTranslationsBundle
```

Configuration tree nodes:

| Node            | Default value | Required | Explanation
|-----------------|---------------|----------|------------
| repository      |               | Yes      | ES repository for storing translations.
| managed_locales |               | Yes      | Locales to be imported.
| formats         | []            | No       | Translation files formats to be imported.
| domains         | []            | No       | Domains to be imported.
| bundles         | []            | No       | Additional bundles to import (Full namespace).
