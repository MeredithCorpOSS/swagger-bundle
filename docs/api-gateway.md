# Integration with Amazon API Gateway

Using the `x-amazon-apigateway-any-method` parameter, we can tell API 
Gateway to pass everything it's passed onto our API.

```
---
swagger: "2.0"
info:
  title: "Passthrough proxy"
host: "<api_gateway_host>"
basePath: "<base_path>"
schemes:
- "https"
paths:
  /{proxy+}:
    x-amazon-apigateway-any-method:
      parameters:
      - name: "proxy"
        in: "path"
      x-amazon-apigateway-integration:
        type: "http_proxy"
        uri: "<api_domain>/{proxy}"
        httpMethod: "ANY"
        passthroughBehavior: "when_no_match"
        requestParameters:
          integration.request.path.proxy: "method.request.path.proxy"
```

See https://aws.amazon.com/blogs/aws/api-gateway-update-new-features-simplify-api-development/ for amazon's documentation on this feature.
