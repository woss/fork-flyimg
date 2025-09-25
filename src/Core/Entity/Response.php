<?php

namespace Core\Entity;

use Core\Entity\Image\OutputImage;
use Core\Handler\ImageHandler;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Response
 * @package Core\Entity
 */
class Response extends BaseResponse
{
    /** @var ImageHandler */
    protected $imageHandler;
    /** @var Pimple\Container */
    protected $storageFileSystem;
    /** @var AppParameters */
    protected $appParameters;

    /**
     * Response constructor.
     *
     * @param $imageHandler
     * @param $storageFileSystem
     * @param $appParameters
     */
    public function __construct($imageHandler, $storageFileSystem, $appParameters)
    {
        $this->storageFileSystem = $storageFileSystem;
        $this->imageHandler = $imageHandler;
        $this->appParameters = $appParameters;
        parent::__construct();
        $this->addSecurityHeaders();
    }


    /**
     * @param OutputImage $image
     *
     */
    protected function generateHeaders(OutputImage $image)
    {
        $outputImageName = $image->getOutputImageName();
        $this->headers->set('Content-Type', $this->imageHandler->responseContentType($image));
        $this->headers->set('Content-Length', $this->getOutputImageSize($image));
        $this->headers->set('Source-Content-Length', filesize($image->getInputImage()->sourceImagePath()));
        $this->headers->set('Source-Content-Type', $image->getInputImage()->sourceImageMimeType());
        $this->headers->set('Content-Disposition', sprintf('inline;filename="%s"', $outputImageName));

        $expireDate = new \DateTime();
        $expireDate->add(new \DateInterval('P1Y'));
        $this->setExpires($expireDate);
        $longCacheTime = 3600 * 24 * (int)$this->appParameters->parameterByKey('header_cache_days');

        $this->setMaxAge($longCacheTime);
        $this->setSharedMaxAge($longCacheTime);
        $this->setPublic();

        if ($image->getInputImage()->optionsBag()->get('refresh')) {
            $this->headers->set('Cache-Control', 'no-cache, private');
            $this->setExpires(null)->expire();

            $this->headers->set('im-identify', $this->imageHandler->imageProcessor()->imageIdentityInformation($image));
            $this->headers->set('im-command', $image->getCommandString());
        }

        $this->addLastModifiedHeader($outputImageName);
    }

    /**
     * Add Security Headers
     */
    protected function addLastModifiedHeader($outputImageName): void
    {
        $storageHandler = $this->storageFileSystem['storage_handler'];
        $lastModified = gmdate("D, d M Y H:i:s T", $storageHandler->lastModified($outputImageName));
        $this->headers->set('Last-Modified', $lastModified);
    }

    /**
     * Add Security Headers
     */
    protected function addSecurityHeaders(): void
    {
        $this->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $this->headers->set('Content-Security-Policy', "script-src 'self'");
        $this->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $this->headers->set('X-XSS-Protection', '1; mode=block');
        $this->headers->set('X-Content-Type-Options', 'nosniff');
        $this->headers->set('Referrer-Policy', 'strict-origin');
    }

    /**
     * @param OutputImage $image
     *
     */
    public function generateImageResponse(OutputImage $image): void
    {
        $this->setContent($image->getOutputImageContent());
        $this->generateHeaders($image);
        // if cache is disabled, then remove the generated image and return the image in the response
        if ($this->appParameters->parameterByKey('disable_cache')) {
            $image->removeGeneratedImage();
        }
    }

    /**
     * @param OutputImage $image
     *
     */
    public function generatePathResponse(OutputImage $image): void
    {
        $imagePath = sprintf($this->storageFileSystem['file_path_resolver'], $image->getOutputImageName());
        $this->setContent($imagePath);
    }

    /**
     * Process upload stream request and generate the image response.
     */
    public function handleUploadStream(string $options, Request $request): void
    {
        $multipartSource = $this->resolveMultipartFileSource($request);
        if ($multipartSource) {
            $image = $this->imageHandler->processImage($options, $multipartSource);
            $this->generateImageResponse($image);
            return;
        }

        $source = $this->resolveBodySource($request);
        $image = $this->imageHandler->processImage($options, $source);
        $this->generateImageResponse($image);
    }

    /**
     * Resolve a source path when a multipart form file is uploaded.
     */
    private function resolveMultipartFileSource(Request $request): ?string
    {
        if (!$request->files || !$request->files->has('file')) {
            return null;
        }
        $uploaded = $request->files->get('file');
        if (is_array($uploaded)) {
            $uploaded = reset($uploaded);
        }
        if (!$uploaded) {
            return null;
        }
        $realPath = method_exists($uploaded, 'getRealPath') ? $uploaded->getRealPath() : '';
        return ($realPath && file_exists($realPath)) ? $realPath : null;
    }

    /**
     * Resolve a source string from the request body and headers.
     * Produces either a data URI or returns the raw string if it's already a data URI.
     */
    private function resolveBodySource(Request $request): string
    {
        $contentType = $request->headers->get('Content-Type', '');
        $rawBody = (string)$request->getContent();
        $trimmed = trim($rawBody);

        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($rawBody, true);
            $source = isset($data['dataUri']) ? (string)$data['dataUri'] : '';
            if (!$source && isset($data['base64'])) {
                $source = 'data:application/octet-stream;base64,' . $data['base64'];
            }
            return $source ?: '';
        }

        if (
            stripos($contentType, 'application/octet-stream') !== false
            || stripos($contentType, 'image/') !== false
        ) {
            if ($trimmed !== '' && stripos($trimmed, 'data:') === 0) {
                return $trimmed;
            }
            return $this->looksLikeBase64($trimmed)
                ? 'data:application/octet-stream;base64,' . $trimmed
                : 'data:application/octet-stream;base64,' . base64_encode($rawBody);
        }

        return ($trimmed !== '' && stripos($trimmed, 'data:') === 0)
            ? $trimmed
            : 'data:application/octet-stream;base64,' . $trimmed;
    }

    /**
     * Heuristic check if a string looks like base64.
     */
    private function looksLikeBase64(string $s): bool
    {
        if ($s === '') {
            return false;
        }
        if (preg_match('/^[A-Za-z0-9+\/\r\n%=]+$/', $s) !== 1) {
            return false;
        }
        $len = strlen(str_replace(["\r", "\n"], '', $s));
        return $len % 4 === 0;
    }

    /**
     * @param OutputImage $image
     * @return string
     */
    private function getOutputImageSize(OutputImage $image): string
    {
        return $this->storageFileSystem['storage_handler']->fileSize($image->getOutputImageName());
    }
}
