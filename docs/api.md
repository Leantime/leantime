# JSON-RPC API

Leantime provides a JSON-RPC 2.0 API. The API is a thin wrapper accessible through the API domain (`app/Domain/Api/Controllers/Jsonrpc.php`) and provides structured access to the service layers of all domains.

## Method Routing Convention

```
leantime.rpc.{domain}.{methodname}                  # 4 segments (service = domain name)
leantime.rpc.{domain}.{servicename}.{methodname}     # 5 segments
```

## How It Works

The controller uses PHP Reflection to introspect service method parameters, matches request params by name, validates required params, and attempts type casting. Services are resolved via `app()->make()`.

## Authentication

Two types:
1. **Leantime API Keys** (`x-api-key` header): Format `lt_{user}_{key}`, acts as service account
2. **Laravel Sanctum** (Bearer tokens): Personal access tokens (requires AdvancedAuth plugin)

## Deprecated API Controllers

The `app/Domain/Api/Controllers/` directory contains legacy REST-like controllers (Tickets.php, Projects.php, etc.) that return JSON. These are deprecated -- all new JS API calls should go through the JSON-RPC endpoint.
