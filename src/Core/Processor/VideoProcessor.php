<?php

namespace Core\Processor;

use Core\Entity\Command;
use Core\Entity\OptionsBag;
use Core\Exception\ExecFailedException;

/**
 * Class VideoProcessor
 */
class VideoProcessor extends Processor
{
    /**
     * Generate video source image
     *
     * Generate a full resolution image at the requested (or default) time
     * duration to be used as the source image.
     *
     * @param OptionsBag $optionsBag      Options bag
     * @param string     $sourceImagePath Source image path (movie)
     *
     * @return string
     */
    public function generateVideoSourceImage(OptionsBag $optionsBag, string $sourceImagePath)
    {
        $time = $optionsBag->get('time');
        $tmpTime = str_replace(['.', ':'], '', $time);
        $videoSourceImagePath = $sourceImagePath . '-' . $tmpTime;

        if (file_exists($videoSourceImagePath) && !$optionsBag->get('refresh')) {
            return $videoSourceImagePath;
        }

        $cmd = new Command(self::FFMPEG_COMMAND);

        $cmd->addArgument('-y');
        $cmd->addArgument('-i', $sourceImagePath);
        $cmd->addArgument("-vf scale='iw:ih'");
        $cmd->addArgument('-ss', $time);
        $cmd->addArgument('-f', 'image2');
        $cmd->addArgument('-vframes', 1);
        $cmd->addArgument($videoSourceImagePath);
        $cmd->addArgument('-hide_banner');
        $cmd->addArgument('-loglevel', 'error');
        $this->logger->info('VideoProcessorCommand: ' . $cmd);

        $this->execute($cmd);

        // For some reason in ffmpeg the error:-
        // 'Output file is empty, nothing was encoded (check -ss / -t / -frames parameters if used)'
        // is in warning level but if we turn that on we get other ignorable errors show up ('deprecated pixel format
        // used, make sure you did set range correctly using ffmpeg'). So just check the output file
        // exists and if not it's likely that the user set a duration greater than the movie length.
        if (!file_exists($videoSourceImagePath)) {
            $msg = 'Output file is empty, possibly the time parameter is greater than the movie length.';
            throw new ExecFailedException($msg);
        }

        return $videoSourceImagePath;
    }
}
