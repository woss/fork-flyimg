### Server Options

There are some easy to setup server configurations in the `config/parameters.yml` file, you can see the full list of options and server configurations in the **[Application Options Document](docs/application-options.md)**

### Security: Restricting Source Domains

Restricted domains disabled by default. This means that you can fetch a resource from any URL. To enable the domain restriction, change in config/parameters.yml

```yml
restricted_domains: true
```

After enabling, you need to put the white listed domains

```yml
whitelist_domains:
  - www.domain-1.org
  - www.domain-2.org
```

### Security: Signature Generation

Based on this [RFC](https://github.com/flyimg/flyimg/issues/96) Signature Generation was added to Flyimg in order to avoid DDOS attacks.

First you need to edit `security_key` and `security_iv` in parameters.yml file and add a proper values.
Than any request to Fyimg app will throw an error unless it's encrypted.

To generate the encrypted url you need to run this command:

```sh
docker exec flyimg php app.php encrypt w_200,h_200,c_1/https://mudawn.com/assets/butterfly-3000.jpg
```

it'll return something like this:

```sh
Hashed request: TGQ1WWRKVGUrZUpoNmJMc2RMUENPL2t6ZDJkWkdOejlkM0p0U0F3WTgxOU5IMzF3U3R0d2V4b3dqbG52cFRTSFZDcmhrY1JnaGZYOHJ3V0NpZDNNRmc9PQ==
```

Now you can request the image throw this new url:

```html
http://localhost:8080/upload/TGQ1WWRKVGUrZUpoNmJMc2RMUENPL2t6ZDJkWkdOejlkM0p0U0F3WTgxOU5IMzF3U3R0d2V4b3dqbG52cFRTSFZDcmhrY1JnaGZYOHJ3V0NpZDNNRmc9PQ==
```

### Run Unit Tests

```sh
docker exec flyimg vendor/bin/phpunit
```

Generate Html Code Coverage

```sh
docker exec flyimg vendor/bin/phpunit --coverage-html build/html
```

### How to Provision the application on

- [DigitalOcean](https://github.com/flyimg/DigitalOcean-provision)
- [AWS Elastic-Beanstalk](https://github.com/flyimg/Elastic-Beanstalk-provision)

## Technology stack

- Server: nginx
- Application: [Silex](http://silex.sensiolabs.org/), a PHP micro-framework.
- Image manipulation: ImageMagick
- JPEG encoder: MozJpeg
- Storage: [Flysystem](http://flysystem.thephpleague.com/)
- Containerisation: Docker

### Abstract storage with Flysystem

Storage files based on [Flysystem](http://flysystem.thephpleague.com/) which is `a filesystem abstraction allows you to easily swap out a local filesystem for a remote one. Technical debt is reduced as is the chance of vendor lock-in.`

Default storage is Local, but you can use other Adapters like AWS S3, Azure, FTP, DropBox, ...

Currently, only the **local** and **S3** are implemented as Storage Provider in Flyimg application, but you can add your specific one easily in `src/Core/Provider/StorageProvider.php`. Check an [example for AWS S3 here](https://github.com/flyimg/flyimg/blob/main/docs/application-options.md#using-aws-s3-as-storage-provider).

## Benchmark

See [benchmark.sh](https://github.com/flyimg/flyimg/blob/main/benchmark.sh) for more details.

Requires: [Vegeta](http://github.com/tsenart/vegeta)

```sh
./benchmark.sh
```

Latest Results:

```sh
Crop http://localhost:8080/upload/w_200,h_200,c_1/Rovinj-Croatia.jpg
Requests      [total, rate]            500, 50.10
Duration      [total, attack, wait]    9.991377689s, 9.97999997s, 11.377719ms
Latencies     [mean, 50, 95, 99, max]  19.402096ms, 12.844271ms, 54.65001ms, 96.276948ms, 135.597203ms
Bytes In      [total, mean]            5337500, 10675.00
Bytes Out     [total, mean]            0, 0.00
Success       [ratio]                  100.00%
Status Codes  [code:count]             200:500

Resize http://localhost:8080/upload/w_200,h_200,rz_1/Rovinj-Croatia.jpg
Requests      [total, rate]            500, 50.10
Duration      [total, attack, wait]    9.992435445s, 9.979999871s, 12.435574ms
Latencies     [mean, 50, 95, 99, max]  16.676093ms, 12.376525ms, 49.676187ms, 97.354697ms, 127.14737ms
Bytes In      [total, mean]            3879500, 7759.00
Bytes Out     [total, mean]            0, 0.00
Success       [ratio]                  100.00%
Status Codes  [code:count]             200:500

Rotate http://localhost:8080/upload/r_-45,w_400,h_400/Rovinj-Croatia.jpg
Requests      [total, rate]            500, 50.10
Duration      [total, attack, wait]    9.992650741s, 9.979999937s, 12.650804ms
Latencies     [mean, 50, 95, 99, max]  13.634143ms, 11.587252ms, 26.873827ms, 50.446923ms, 68.222253ms
Bytes In      [total, mean]            17609000, 35218.00
Bytes Out     [total, mean]            0, 0.00
Success       [ratio]                  100.00%
Status Codes  [code:count]             200:500
```
## Roadmap

- [x] Benchmark the application.
- [ ] Decouple the core logic from Silex in order to make it portable.
- [ ] Add overlays functionality (Text on top of the image)
- [ ] Storage auto-mapping
- [ ] Add support for FLIFF, BPG and JPEG2000

## Generate CHANGELOG

`github-changes -o flyimg -r flyimg -a -k GITHUB-TOKEN --only-pulls --use-commit-body`