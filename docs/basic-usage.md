# Basic Usage

You can see the full list of options configurable by URL params, **with examples**, in the [URL-Options document](url-options.md)

We put a lot of defaults in place to prevent distortion, bad quality, weird cropping and unwanted padding.

The most common URL options are:

## Get an image to fill exact dimensions

- Image: `https://flyimg.io/demo-images/Citroen-DS.jpg`
- Width: 300
- Height: 250
- Crop if necessary: `c_1`

`https://demo.flyimg.io/upload/w_300,h_250,c_1/https://flyimg.io/demo-images/Citroen-DS.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_300,h_250,c_1,o_jpg/https://flyimg.io/demo-images/Citroen-DS.jpg)

## Get an image to fit maximum dimensions

- Image: `https://flyimg.io/demo-images/Citroen-DS.jpg`
- Width: 300
- Height: 250
- Note that we omit the crop parameter

`https://demo.flyimg.io/upload/w_300,h_250/https://flyimg.io/demo-images/Citroen-DS.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_300,h_250,o_jpg/https://flyimg.io/demo-images/Citroen-DS.jpg)

## Crop to a square and rotate 90 degrees clockwise

- Image: `https://flyimg.io/demo-images/Citroen-DS.jpg`
- Width: 200
- Height: 200
- Crop: `c_1`
- Rotate: 90

`https://demo.flyimg.io/upload/w_200,h_200,c_1,r_90/https://flyimg.io/demo-images/Citroen-DS.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_200,h_200,c_1,r_90,o_jpg/https://flyimg.io/demo-images/Citroen-DS.jpg)

## Get an image with exact dimensions and low quality

- Image: `https://flyimg.io/demo-images/Citroen-DS.jpg`
- Width: 200
- Height: 200
- Crop: `c_1`
- Quality: 30

`https://demo.flyimg.io/upload/w_200,h_200,c_1,q_30/https://flyimg.io/demo-images/Citroen-DS.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_200,h_200,c_1,q_30,o_jpg/https://flyimg.io/demo-images/Citroen-DS.jpg)

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

- PDF: `https://flyimg.io/demo-images/lighthouses.pdf`
- Width: 800
- Height: 800
- Page: `pdfp_2`

`https://demo.flyimg.io/upload/w_800,h_800,pdfp_2/https://flyimg.io/demo-images/lighthouses.pdf`

![lago_ranco](https://demo.flyimg.io/upload/w_800,h_800,pdfp_2/https://flyimg.io/demo-images/lighthouses.pdf)

## Get a video image to fit dimensions from a time duration point

- Video: `https://flyimg.io/demo-images/big_buck_bunny_720p_2mb.mp4`
- Width: 400
- Height: 400
- Time: `tm_00:00:05`

`https://demo.flyimg.io/upload/w_400,h_400,tm_00:00:05/https://flyimg.io/demo-images/big_buck_bunny_720p_2mb.mp4`

![lago_ranco](https://demo.flyimg.io/upload/w_400,h_400,tm_00:00:05/https://flyimg.io/demo-images/big_buck_bunny_720p_2mb.mp4)

## Converting to Colorspace Gray

- Image: `https://flyimg.io/demo-images/Citroen-DS.jpg`
- Width: 400
- Colorspace: `clsp_Gray`

`https://demo.flyimg.io/upload/w_400,clsp_Gray/https://flyimg.io/demo-images/Citroen-DS.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_400,clsp_Gray,o_jpg/https://flyimg.io/demo-images/Citroen-DS.jpg)

## Converting to Monochrome

- Image: `https://flyimg.io/demo-images/Citroen-DS.jpg`
- Width: 400
- Monochrome: `mnchr_1`

`https://demo.flyimg.io/upload/w_400,mnchr_1/https://flyimg.io/demo-images/Citroen-DS.jpg`

![lago_ranco](https://demo.flyimg.io/upload/w_400,mnchr_1,o_jpg/https://flyimg.io/demo-images/Citroen-DS.jpg)

## Using width AND height

**example:`h_300,w_300`**  
By default setting width and height together, works like defining a rectangle that will define a **max-width** and **max-height** and the image will scale proportionally to fit that area without cropping.

By default; width, height, or both will **not scale up** an image that is smaller than the defined dimensions.

`h_300,w_300` : `https://demo.flyimg.io/upload/h_300,w_300/https://flyimg.io/demo-images/Citroen-DS.jpg`

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

`r_45` : `https://demo.flyimg.io/upload/r_-45,w_400,h_400/https://flyimg.io/demo-images/Citroen-DS.jpg`

## `o` : output

`string`  
_Default:_ `auto`  
_Description:_ Output format requested, for example you can force the output as jpeg file in case of source file is png. The default `auto` will try to output the best format for the requesting browser, falling back to the same format as the source image or finally with a fallback to **jpg**.

**example:`o_auto`,`o_input`,`o_png`,`o_webp`,`o_jpeg`,`o_jpg`**

## `q` : quality

`int` (0-100)  
_Default:_ `80`  
_Description:_ Sets the compression level for the output image. Your best results will be between **70** and **95**.

**example:`q_100`,`q_75`,...**

`q_30` : `https://demo.flyimg.io/upload/q_30/https://flyimg.io/demo-images/Citroen-DS.jpg`

`q_100` : `https://demo.flyimg.io/upload/q_100/https://flyimg.io/demo-images/Citroen-DS.jpg`

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

![fcp_2](https://demo.flyimg.io/upload/fc_1,fcp_2,o_png/http://facedetection.jaysalvat.com/img/faces.jpg)

## `fb` : face-blur

`int`
_Default:_ `0`
_Description:_ Apply blur effect on faces in a given image

**example:`fb_1`**

`fb_1` : `https://demo.flyimg.io/upload/fb_1/http://facedetection.jaysalvat.com/img/faces.jpg`

![fb_1](https://demo.flyimg.io/upload/fb_1,o_jpg/http://facedetection.jaysalvat.com/img/faces.jpg)

## Get the path to the generated image instead of serving it

Change the first part of the path from `upload` to `path`, like so:

`https://demo.flyimg.io/path/w_300,h_250,c_1/https://flyimg.io/demo-images/Citroen-DS.jpg` will output in the body of the response:

`http://localhost:8080/uploads/752d2124eef87b3112779618c96468da.jpg`

