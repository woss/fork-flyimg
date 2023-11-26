<?php

namespace Core\Processor;

use Core\Entity\Command;
use Core\Entity\Image\OutputImage;

/**
 * Class SmartCropProcessor
 * @package Core\Processor
 */
class SmartCropProcessor extends Processor
{
    /**
     * Smart cropping
     *
     * @param OutputImage $outputImage
     *
     * @throws \Exception
     */
    public function smartCrop(OutputImage $outputImage)
    {

        $smartCropCmd = new Command(self::SMARTCROP_COMMAND);
        $smartCropCmd->addArgument($outputImage->getOutputTmpPath());
        $output = $this->execute($smartCropCmd);

        if (!empty($output)) {
            $geometry = $output[0];
            $cropCmd = new Command(self::IM_CONVERT_COMMAND);
            $cropCmd->addArgument($outputImage->getOutputTmpPath());
            $cropCmd->addArgument("-crop", $geometry);
            $cropCmd->addArgument($outputImage->getOutputTmpPath());
            $this->execute($cropCmd);
        }
    }
}
