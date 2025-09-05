## Restricting Source Domains

Restricted domains disabled by default. This means that you can fetch a resource from any URL. To enable the domain restriction, change in config/parameters.yml

```yml
restricted_domains: true
```

After enabling, you need to put the white listed domains. You can use both exact domain matches and wildcard patterns:

```yml
whitelist_domains:
  - www.domain-1.org
  - www.domain-2.org
  - "*.example.com"
  - api.myservice.com
```

- **Exact matches**: `www.domain-1.org` - matches only `www.domain-1.org`
- **Wildcard patterns**: `"*.example.com"` - matches any subdomain of `example.com` (e.g., `api.example.com`, `cdn.example.com`, `www.example.com`)

**Note**: Wildcard patterns must be quoted in YAML to avoid parsing errors, as the `*` character has special meaning in YAML.

## Signature Generation

Based on this [RFC](https://github.com/flyimg/flyimg/issues/96) Signature Generation was added to Flyimg in order to avoid DDOS attacks.

First you need to edit `security_key` and `security_iv` in parameters.yml file and add a proper values.
Than any request to Fyimg app will throw an error unless it's encrypted.

To generate the encrypted url you need to run this command:

```sh
docker exec flyimg php app.php encrypt w_200,h_200,c_1/https://flyimg.io/demo-images/Citroen-DS.jpg
```

it'll return something like this:

```sh
Hashed request: TGQ1WWRKVGUrZUpoNmJMc2RMUENPL2t6ZDJkWkdOejlkM0p0U0F3WTgxOU5IMzF3U3R0d2V4b3dqbG52cFRTSFZDcmhrY1JnaGZYOHJ3V0NpZDNNRmc9PQ==
```

Now you can request the image throw this new url:

```html
http://localhost:8080/upload/TGQ1WWRKVGUrZUpoNmJMc2RMUENPL2t6ZDJkWkdOejlkM0p0U0F3WTgxOU5IMzF3U3R0d2V4b3dqbG52cFRTSFZDcmhrY1JnaGZYOHJ3V0NpZDNNRmc9PQ==
```
