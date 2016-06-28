# Configuration Reference

```yaml
# ./app/config/config_dev.yaml
swagger:
    version: '2.0'      # Swagger version
    info:
        title: 'My API' # Required
        version: '1.0'  # Required
        description: 'My API Description'
    formats: []         # Required - list of all content types the API accepts/produces (e.g. application/json)
    bundles: []         # Required - list of bundles to search for annotations in
    paths:   []         # List of any paths to include in the search for annotations
```
