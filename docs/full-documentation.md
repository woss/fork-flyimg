# Basic Usage Examples

You can see the full list of options configurable by URL params, **with examples**, in the [URL-Options document](url-options.md)

We put a lot of defaults in place to prevent distortion, bad quality, weird cropping and unwanted padding.

The most common URL options are:

## Get an image to fill exact dimensions

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 300
- Height: 250
- Crop if necesary: `c_1`

`https://demo.fluimg.io/upload/w_300,h_250,c_1/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.fluimg.io/upload/w_300,h_250,c_1,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

This will serve the image.

## Get the path to the generated image instead of serving it

Change the first part of the path from `upload` to `path`, like so:

`https://demo.fluimg.io/path/w_300,h_250,c_1/https://mudawn.com/assets/butterfly-3000.jpg` will output in the body of the response:

`http://localhost:8080/uploads/752d2124eef87b3112779618c96468da.jpg`

## Get an image to fit maximum dimensions

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 300
- Height: 250
- Note that we ommit the crop parameter

`https://demo.fluimg.io/upload/w_300,h_250/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.fluimg.io/upload/w_300,h_250,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Crop to a square and rotate 90 degrees clockwise

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 200
- Height: 200
- Crop: `c_1`
- Rotate: 90

`https://demo.fluimg.io/upload/w_200,h_200,c_1,r_90/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.fluimg.io/upload/w_200,h_200,c_1,r_90,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Get an image with exact dimensions and low quality

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 200
- Height: 200
- Crop: `c_1`
- Quality: 30

`https://demo.fluimg.io/upload/w_200,h_200,c_1,q_30/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.fluimg.io/upload/w_200,h_200,c_1,q_30,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Smart Crop

- Image: `https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`
- Width: 500
- Smart Crop: `smc_1`

**Without Smart Crop**

`https://demo.fluimg.io/upload/w_500/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`

![lago_ranco](https://demo.fluimg.io/upload/w_500/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg)

**With Smart Crop Enabled**

`https://demo.fluimg.io/upload/w_500,smc_1/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`

![lago_ranco](https://demo.fluimg.io/upload/w_500,smc_1/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg)

## Get a PDF page image to fit dimensions

- PDF: `http://mudawn.com/assets/lighthouses.pdf`
- Width: 200
- Height: 200
- Page: `pg_1`

`https://demo.fluimg.io/upload/w_200,h_200,pg_1/http://mudawn.com/assets/lighthouses.pdf`

![lago_ranco](https://demo.fluimg.io/upload/w_200,h_200,pg_1/http://mudawn.com/assets/lighthouses.pdf)

## Get a video image to fit dimensions from a time duration point

- Video: `http://mudawn.com/assets/big_buck_bunny_720p_2mb.mp4`
- Width: 200
- Height: 200
- Time: `tm_00:00:05`

`https://demo.fluimg.io/upload/w_200,h_200,tm_00:00:05/http://mudawn.com/assets/big_buck_bunny_720p_2mb.mp4`

![lago_ranco](https://demo.fluimg.io/upload/w_200,h_200,tm_00:00:05/http://mudawn.com/assets/big_buck_bunny_720p_2mb.mp4)

## Converting to Colorspace Gray

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 400
- Colorspace: `clsp_Gray`

`https://demo.fluimg.io/upload/w_400,clsp_Gray/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.fluimg.io/upload/w_400,clsp_Gray,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Converting to Monochrome

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 400
- Monochrome: `mnchr_1`

`https://demo.fluimg.io/upload/w_400,mnchr_1/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.fluimg.io/upload/w_400,mnchr_1,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Using width AND height

**example:`h_300,w_300`**  
By default setting width and height together, works like defining a rectangle that will define a **max-width** and **max-height** and the image will scale proportionally to fit that area without cropping.

By default; width, height, or both will **not scale up** an image that is smaller than the defined dimensions.

`h_300,w_300` : `https://demo.fluimg.io/upload/h_300,w_300/https://mudawn.com/assets/butterfly-3000.jpg`

## `smc` : smart crop

`bool`  
_Default:_ `false`  
_Description:_ Smart cropping feature, uses python script to determine coordinates

**example:`smc_1`**

`smc_1,w_500` : `https://demo.fluimg.io/upload/upload/smc_1,w_500/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`

## `r` : rotate

`string`  
_Default:_ `null`  
_Description:_ Apply image rotation (using shear operations) to the image.

**example: `r_90`, `r_-180`,...**

`r_45` : `https://demo.fluimg.io/upload/r_-45,w_400,h_400/https://mudawn.com/assets/butterfly-3000.jpg`

## `o` : output

`string`  
_Default:_ `auto`  
_Description:_ Output format requested, for example you can force the output as jpeg file in case of source file is png. The default `auto` will try to output the best format for the requesting browser, falling back to the same format as the source image or finally with a fallback to **jpg**.

**example:`o_auto`,`o_input`,`o_png`,`o_webp`,`o_jpeg`,`o_jpg`**

## `q` : quality

`int` (0-100)  
_Default:_ `90`  
_Description:_ Sets the compression level for the output image. Your best results will be between **70** and **95**.

**example:`q_100`,`q_75`,...**

`q_30` : `https://demo.fluimg.io/upload/q_30/https://mudawn.com/assets/butterfly-3000.jpg`

`q_100` : `https://demo.fluimg.io/upload/q_100/https://mudawn.com/assets/butterfly-3000.jpg`

## Refresh or re-fetch source image

`rf` : refresh  
_Default:_ `false`  
_Description:_ When this parameter is 1, it will force a re-request of the original image and run it through the transformations and compression again. It will delete the local cached copy.

**example:`rf_1`**

## Face Detection options

## `fc` : face-crop

`int`
_Default:_ `0`
_Description:_ Using [facedetect](https://github.com/wavexx/facedetect) repository to detect faces and passe the coordinates to ImageMagick to crop.

**example:`fc_1`**

`fc_1` : `https://demo.fluimg.io/upload/fc_1/http://facedetection.jaysalvat.com/img/faces.jpg`

![fc_1](https://demo.fluimg.io/upload/fc_1,o_jpg/http://facedetection.jaysalvat.com/img/faces.jpg)

## `fcp` : face-crop-position

`int`
_Default:_ `0`
_Description:_ When using the Face crop option and when the image contain more than one face, you can specify which one you want get cropped

**example:`fcp_1`,`fcp_0`,...**

`fcp_2` : `https://demo.fluimg.io/upload/fc_1,fcp_2/http://facedetection.jaysalvat.com/img/faces.jpg`

![fcp_2](https://demo.fluimg.io/upload/fc_1,fcp_2,o_jpg/http://facedetection.jaysalvat.com/img/faces.jpg)

## `fb` : face-blur

`int`
_Default:_ `0`
_Description:_ Apply blur effect on faces in a given image

**example:`fb_1`**

`fb_1` : `https://demo.fluimg.io/upload/fb_1/http://facedetection.jaysalvat.com/img/faces.jpg`

![fb_1](https://demo.fluimg.io/upload/fb_1,o_jpg/http://facedetection.jaysalvat.com/img/faces.jpg)

## Server Options

There are some easy to setup server configurations in the `config/parameters.yml` file, you can see the full list of options and server configurations in the **[Application Options Document](application-options.md)**

## Security: Restricting Source Domains

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

## Security: Signature Generation

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

## Run Unit Tests

```sh
docker exec flyimg vendor/bin/phpunit
```

Generate Html Code Coverage

```sh
docker exec flyimg vendor/bin/phpunit --coverage-html build/html
```

## How to Provision the application on

- [DigitalOcean](https://github.com/flyimg/DigitalOcean-provision)
- [AWS Elastic-Beanstalk](https://github.com/flyimg/Elastic-Beanstalk-provision)

## Technology stack

- Server: nginx
- Application: [Silex](http://silex.sensiolabs.org/), a PHP micro-framework.
- Image manipulation: ImageMagick
- JPEG encoder: MozJpeg
- Storage: [Flysystem](http://flysystem.thephpleague.com/)
- Containerisation: Docker

## Abstract storage with Flysystem

Storage files based on [Flysystem](http://flysystem.thephpleague.com/) which is `a filesystem abstraction allows you to easily swap out a local filesystem for a remote one. Technical debt is reduced as is the chance of vendor lock-in.`

Default storage is Local, but you can use other Adapters like AWS S3, Azure, FTP, DropBox, ...

Currently, only the **local** and **S3** are implemented as Storage Provider in Flyimg application, but you can add your specific one easily in `src/Core/Provider/StorageProvider.php`. Check an [example for AWS S3 here](application-options.md#using-aws-s3-as-storage-provider).

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
