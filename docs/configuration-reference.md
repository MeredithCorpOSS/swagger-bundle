# Configuration Reference

```yaml
# ./app/config/config_dev.yaml
swagger:
    version: '2.0'       # Swagger version
    info:
        title: 'My API'  # Required
        version: '1.0'   # Required
        description: 'My API Description'
    host: ~              # API Host
    base_path: ~         # API base path
    alternative_hosts:   # presets to override host, schemes and base_path
        - { name: 'production', host: 'productionhost', schemes: [https], base_path: '/api' }
    provides: []         # Default list of all content types the API provides (e.g. [application/json])
    consumes: []         # Default list of all content types the API consumes (e.g. [application/json])
    schemes: []          # Default list of all API Schemes (e.g. [http, https])
    
    annotations:
        bundles: []      # Required - list of bundles to search for annotations in
        paths:   []      # List of any paths to include in the search for annotations
        
    pretty: true        # Set to true to generate pretty JSON. Default false
```
