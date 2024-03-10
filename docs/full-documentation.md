# Basic Usage Examples

You can see the full list of options configurable by URL params, **with examples**, in the [URL-Options document](url-options.md)

We put a lot of defaults in place to prevent distortion, bad quality, weird cropping and unwanted padding.

The most common URL options are:

## Get an image to fill exact dimensions

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 300
- Height: 250
- Crop if necessary: `c_1`

`https://demo.flyimg.io/upload/w_300,h_250,c_1/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_300,h_250,c_1,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Get an image to fit maximum dimensions

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 300
- Height: 250
- Note that we omit the crop parameter

`https://demo.flyimg.io/upload/w_300,h_250/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_300,h_250,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Crop to a square and rotate 90 degrees clockwise

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 200
- Height: 200
- Crop: `c_1`
- Rotate: 90

`https://demo.flyimg.io/upload/w_200,h_200,c_1,r_90/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_200,h_200,c_1,r_90,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Get an image with exact dimensions and low quality

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 200
- Height: 200
- Crop: `c_1`
- Quality: 30

`https://demo.flyimg.io/upload/w_200,h_200,c_1,q_30/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_200,h_200,c_1,q_30,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Smart Crop

- Image: `https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`
- Width: 500
- Smart Crop: `smc_1`

**Without Smart Crop**

`https://demo.flyimg.io/upload/w_500/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`

![lago_ranco](https://demo.flyimg.io/upload/w_500/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg)

**With Smart Crop Enabled**

`https://demo.flyimg.io/upload/w_500,smc_1/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`

![lago_ranco](https://demo.flyimg.io/upload/w_500,smc_1/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg)

## Get a PDF page image to fit dimensions

- PDF: `http://mudawn.com/assets/lighthouses.pdf`
- Width: 200
- Height: 200
- Page: `pg_1`

`https://demo.flyimg.io/upload/w_200,h_200,pg_1/http://mudawn.com/assets/lighthouses.pdf`

![lago_ranco](https://demo.flyimg.io/upload/w_200,h_200,pg_1/http://mudawn.com/assets/lighthouses.pdf)

## Get a video image to fit dimensions from a time duration point

- Video: `http://mudawn.com/assets/big_buck_bunny_720p_2mb.mp4`
- Width: 200
- Height: 200
- Time: `tm_00:00:05`

`https://demo.flyimg.io/upload/w_200,h_200,tm_00:00:05/http://mudawn.com/assets/big_buck_bunny_720p_2mb.mp4`

![lago_ranco](https://demo.flyimg.io/upload/w_200,h_200,tm_00:00:05/http://mudawn.com/assets/big_buck_bunny_720p_2mb.mp4)

## Converting to Colorspace Gray

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 400
- Colorspace: `clsp_Gray`

`https://demo.flyimg.io/upload/w_400,clsp_Gray/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_400,clsp_Gray,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Converting to Monochrome

- Image: `https://mudawn.com/assets/butterfly-3000.jpg`
- Width: 400
- Monochrome: `mnchr_1`

`https://demo.flyimg.io/upload/w_400,mnchr_1/https://mudawn.com/assets/butterfly-3000.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_400,mnchr_1,o_jpg/https://mudawn.com/assets/butterfly-3000.jpg)

## Using width AND height

**example:`h_300,w_300`**  
By default setting width and height together, works like defining a rectangle that will define a **max-width** and **max-height** and the image will scale proportionally to fit that area without cropping.

By default; width, height, or both will **not scale up** an image that is smaller than the defined dimensions.

`h_300,w_300` : `https://demo.flyimg.io/upload/h_300,w_300/https://mudawn.com/assets/butterfly-3000.jpg`

## `smc` : smart crop

`bool`  
_Default:_ `false`  
_Description:_ Smart cropping feature, uses python script to determine coordinates

**example:`smc_1`**

`smc_1,w_500` : `https://demo.flyimg.io/upload/upload/smc_1,w_500/https://images.pexels.com/photos/1280553/pexels-photo-1280553.jpeg`

## `r` : rotate

`string`  
_Default:_ `null`  
_Description:_ Apply image rotation (using shear operations) to the image.

**example: `r_90`, `r_-180`,...**

`r_45` : `https://demo.flyimg.io/upload/r_-45,w_400,h_400/https://mudawn.com/assets/butterfly-3000.jpg`

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

`q_30` : `https://demo.flyimg.io/upload/q_30/https://mudawn.com/assets/butterfly-3000.jpg`

`q_100` : `https://demo.flyimg.io/upload/q_100/https://mudawn.com/assets/butterfly-3000.jpg`

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

`fc_1` : `https://demo.flyimg.io/upload/fc_1/http://facedetection.jaysalvat.com/img/faces.jpg`

![fc_1](https://demo.flyimg.io/upload/fc_1,o_jpg/http://facedetection.jaysalvat.com/img/faces.jpg)

## `fcp` : face-crop-position

`int`
_Default:_ `0`
_Description:_ When using the Face crop option and when the image contain more than one face, you can specify which one you want get cropped

**example:`fcp_1`,`fcp_0`,...**

`fcp_2` : `https://demo.flyimg.io/upload/fc_1,fcp_2/http://facedetection.jaysalvat.com/img/faces.jpg`

![fcp_2](https://demo.flyimg.io/upload/fc_1,fcp_2,o_jpg/http://facedetection.jaysalvat.com/img/faces.jpg)

## `fb` : face-blur

`int`
_Default:_ `0`
_Description:_ Apply blur effect on faces in a given image

**example:`fb_1`**

`fb_1` : `https://demo.flyimg.io/upload/fb_1/http://facedetection.jaysalvat.com/img/faces.jpg`

![fb_1](https://demo.flyimg.io/upload/fb_1,o_jpg/http://facedetection.jaysalvat.com/img/faces.jpg)

## Get the path to the generated image instead of serving it

Change the first part of the path from `upload` to `path`, like so:

`https://demo.flyimg.io/path/w_300,h_250,c_1/https://mudawn.com/assets/butterfly-3000.jpg` will output in the body of the response:

`http://localhost:8080/uploads/752d2124eef87b3112779618c96468da.jpg`

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


## Abstract storage with Flysystem

Storage files based on [Flysystem](http://flysystem.thephpleague.com/) which is `a filesystem abstraction allows you to easily swap out a local filesystem for a remote one. Technical debt is reduced as is the chance of vendor lock-in.`

Default storage is Local, but you can use other Adapters like AWS S3, Azure, FTP, DropBox, ...

Currently, only the **local** and **S3** are implemented as Storage Provider in Flyimg application, but you can add your specific one easily in `src/Core/Provider/StorageProvider.php`. Check an [example for AWS S3 here](application-options.md#using-aws-s3-as-storage-provider).

## Benchmark

See [benchmark.sh](https://github.com/flyimg/flyimg/blob/main/benchmark.sh) for more details.

Requires: [Apache Ab](https://httpd.apache.org/docs/2.4/programs/ab.html)

```sh
./benchmark.sh
```

Latest Results:

```sh
------------------------------------------------------------------------------
------------------------------------------------------------------------------
Crop http://localhost:8081/upload/w_500,h_500,c_1/wat-arun.jpg
------------------------------------------------------------------------------
This is ApacheBench, Version 2.3 <$Revision: 1903618 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.18.0
Server Hostname:        localhost
Server Port:            8081

Document Path:          /upload/w_500,h_500,c_1/wat-arun.jpg
Document Length:        130752 bytes

Concurrency Level:      4
Time taken for tests:   2.098 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      131423000 bytes
HTML transferred:       130752000 bytes
Requests per second:    476.68 [#/sec] (mean)
Time per request:       8.391 [ms] (mean)
Time per request:       2.098 [ms] (mean, across all concurrent requests)
Transfer rate:          61178.30 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       0
Processing:     3    5  34.0      3    1056
Waiting:        3    5  33.4      3    1054
Total:          3    5  34.0      4    1056

Percentage of the requests served within a certain time (ms)
  50%      4
  66%      4
  75%      4
  80%      4
  90%      4
  95%      5
  98%      6
  99%      6
 100%   1056 (longest request)

------------------------------------------------------------------------------
------------------------------------------------------------------------------
Simple Resize http://localhost:8081/upload/w_500,h_500/wat-arun.jpg
------------------------------------------------------------------------------
This is ApacheBench, Version 2.3 <$Revision: 1903618 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.18.0
Server Hostname:        localhost
Server Port:            8081

Document Path:          /upload/w_500,h_500/wat-arun.jpg
Document Length:        95525 bytes

Concurrency Level:      4
Time taken for tests:   1.010 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      96195000 bytes
HTML transferred:       95525000 bytes
Requests per second:    989.84 [#/sec] (mean)
Time per request:       4.041 [ms] (mean)
Time per request:       1.010 [ms] (mean, across all concurrent requests)
Transfer rate:          92985.65 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       1
Processing:     3    4   3.3      3      61
Waiting:        3    4   3.2      3      61
Total:          3    4   3.3      4      61

Percentage of the requests served within a certain time (ms)
  50%      4
  66%      4
  75%      4
  80%      4
  90%      4
  95%      5
  98%      6
  99%      8
 100%     61 (longest request)

------------------------------------------------------------------------------
------------------------------------------------------------------------------
Simple Resize with refresh http://localhost:8081/upload/w_500,h_500,rf_1/wat-arun.jpg
------------------------------------------------------------------------------
This is ApacheBench, Version 2.3 <$Revision: 1903618 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.18.0
Server Hostname:        localhost
Server Port:            8081

Document Path:          /upload/w_500,h_500,rf_1/wat-arun.jpg
Document Length:        95523 bytes

Concurrency Level:      4
Time taken for tests:   203.944 seconds
Complete requests:      1000
Failed requests:        936
   (Connect: 0, Receive: 0, Length: 936, Exceptions: 0)
Non-2xx responses:      913
Total transferred:      8241565 bytes
HTML transferred:       7937078 bytes
Requests per second:    4.90 [#/sec] (mean)
Time per request:       815.778 [ms] (mean)
Time per request:       203.944 [ms] (mean, across all concurrent requests)
Transfer rate:          39.46 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       2
Processing:    95  813 398.7   1009    1434
Waiting:       95  813 398.7   1009    1434
Total:         95  813 398.7   1009    1434

Percentage of the requests served within a certain time (ms)
  50%   1009
  66%   1043
  75%   1068
  80%   1082
  90%   1123
  95%   1164
  98%   1253
  99%   1321
 100%   1434 (longest request)

------------------------------------------------------------------------------
------------------------------------------------------------------------------
Resize http://localhost:8081/upload/w_500,h_500,rz_1/wat-arun.jpg
------------------------------------------------------------------------------
This is ApacheBench, Version 2.3 <$Revision: 1903618 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.18.0
Server Hostname:        localhost
Server Port:            8081

Document Path:          /upload/w_500,h_500,rz_1/wat-arun.jpg
Document Length:        95823 bytes

Concurrency Level:      4
Time taken for tests:   2.104 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      96493000 bytes
HTML transferred:       95823000 bytes
Requests per second:    475.39 [#/sec] (mean)
Time per request:       8.414 [ms] (mean)
Time per request:       2.104 [ms] (mean, across all concurrent requests)
Transfer rate:          44796.88 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       1
Processing:     3    5  35.6      4    1128
Waiting:        3    5  35.6      3    1128
Total:          3    5  35.6      4    1128

Percentage of the requests served within a certain time (ms)
  50%      4
  66%      4
  75%      4
  80%      4
  90%      4
  95%      4
  98%      5
  99%      8
 100%   1128 (longest request)

------------------------------------------------------------------------------
------------------------------------------------------------------------------
Rotate http://localhost:8081/upload/r_-45,w_400,h_400/wat-arun.jpg
------------------------------------------------------------------------------
This is ApacheBench, Version 2.3 <$Revision: 1903618 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.18.0
Server Hostname:        localhost
Server Port:            8081

Document Path:          /upload/r_-45,w_400,h_400/wat-arun.jpg
Document Length:        68958 bytes

Concurrency Level:      4
Time taken for tests:   1.808 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      69628000 bytes
HTML transferred:       68958000 bytes
Requests per second:    553.16 [#/sec] (mean)
Time per request:       7.231 [ms] (mean)
Time per request:       1.808 [ms] (mean, across all concurrent requests)
Transfer rate:          37612.77 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       1
Processing:     3    5  25.7      3     805
Waiting:        3    5  25.7      3     805
Total:          3    5  25.7      4     806

Percentage of the requests served within a certain time (ms)
  50%      4
  66%      4
  75%      4
  80%      4
  90%      4
  95%      4
  98%      7
  99%     10
 100%    806 (longest request)
```
