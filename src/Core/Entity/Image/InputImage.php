<?php

namespace Core\Entity\Image;

use Core\Entity\OptionsBag;
use Core\Exception\ExecFailedException;
use Core\Exception\ReadFileException;
use Core\Entity\ImageMetaInfo;

class InputImage
{
    /** @var OptionsBag */
    protected $optionsBag;

    /** @var string */
    protected $sourceImageUrl;

    /** @var string */
    protected $sourceImagePath;

    /** @var string */
    protected $sourceImageMimeType;

    /** @var ImageMetaInfo */
    protected $sourceImageInfo;

    /**  @var string */
    protected $sourceFileMimeType;

    /**
     * OutputImage constructor.
     *
     * @param OptionsBag $optionsBag
     * @param string     $sourceImageUrl
     *
     * @throws \Exception
     */
    public function __construct(OptionsBag $optionsBag, string $sourceImageUrl)
    {
        $this->optionsBag = $optionsBag;
        $this->sourceImageUrl = $sourceImageUrl;

        $this->sourceImagePath = $optionsBag->hashOriginalImageUrl($this->sourceImageUrl);
        $this->saveToTemporaryFile();

        $mime = finfo_file(
            finfo_open(FILEINFO_MIME_TYPE),
            $this->sourceImagePath
        );

        if (strpos($mime, 'video/') !== false) {
            $this->sourceFileMimeType = $mime;
            $time = $this->getTime();
            $tmpTime = str_replace(['.', ':'], '', $time);
            $dest = $this->sourceImagePath . '-'. $tmpTime;
            $overwrite = $this->optionsBag->get('refresh') ? ' -y' : ' -n';
            $cmd = "ffmpeg " . $overwrite . " -i " . $this->sourceImagePath . " -vf scale='iw:ih' -ss " . $time .
            " -f image2 -vframes 1 " . $dest . " -hide_banner -loglevel warning";
            exec($cmd . ' 2>&1', $output);
            foreach ($output as $item) {
                if (strpos($item, 'Output file is empty') !== false) {
                    $msg = 'Output file is empty, possibly the time parameter is greater than the movie length.';
                    throw new ExecFailedException($msg);
                }
            }
            $this->sourceImagePath = $dest;
        }

        $this->sourceImageInfo = new ImageMetaInfo($this->sourceImagePath);
    }

    /**
     * Save given image to temporary file and return the path
     *
     * @throws \Exception
     */
    protected function saveToTemporaryFile()
    {
        if (file_exists($this->sourceImagePath) && !$this->optionsBag->get('refresh')) {
            return;
        }

        $opts = [
            'http' =>
                [
                    'header' => $this->optionsBag->appParameters()->parameterByKey('header_extra_options'),
                    'method' => 'GET',
                    'max_redirects' => '0',
                ],
        ];
        $context = stream_context_create($opts);

        if (!$stream = @fopen($this->sourceImageUrl, 'r', false, $context)
        ) {
            throw  new ReadFileException(
                'Error occurred while trying to read the file Url : '
                .$this->sourceImageUrl
            );
        }
        $content = stream_get_contents($stream);
        fclose($stream);
        file_put_contents($this->sourceImagePath, $content);
    }

    /**
     * Remove Input Image
     */
    public function removeInputImage()
    {
        if (file_exists($this->sourceImagePath())) {
            unlink($this->sourceImagePath());
        }
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function extractKey(string $key): string
    {
        $value = '';
        if ($this->optionsBag->has($key)) {
            $value = $this->optionsBag->get($key);
            $this->optionsBag->remove($key);
        }

        return is_null($value) ? '' : $value;
    }

    /**
     * @return OptionsBag
     */
    public function optionsBag(): OptionsBag
    {
        return $this->optionsBag;
    }

    /**
     * @return string
     */
    public function sourceImageUrl(): string
    {
        return $this->sourceImageUrl;
    }

    /**
     * @return string
     */
    public function sourceImagePath(): string
    {
        return $this->sourceImagePath;
    }

    /**
     * @return string
     */
    public function sourceImageMimeType(): string
    {
        if (isset($this->sourceImageMimeType)) {
            return $this->sourceImageMimeType;
        }

        $this->sourceImageMimeType = $this->sourceImageInfo->mimeType();

        return $this->sourceImageMimeType;
    }

    public function sourceImageInfo()
    {
        return $this->sourceImageInfo;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime(): string
    {
        return $this->optionsBag->get('time');
    }

    /**
     * @return string
     */
    public function sourceFileMimeType(): string
    {
        return isset($this->sourceFileMimeType) ? $this->sourceFileMimeType : '';
    }
}
