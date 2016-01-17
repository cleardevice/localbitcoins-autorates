<?php

namespace LocalBtc\Adapter;

use LocalBtc\Ad;
use LocalBtc\AdsStorage;

class Csv extends AbstractOutput implements OutputAdapterInterface
{
    protected $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function process(AdsStorage $adsStorage)
    {
        $csvFileHandler = fopen($this->fileName, 'w');
        if (false === $csvFileHandler) {
            throw new \Exception('Error creating file');
        }

        $outputColumns = $this->getOutputColumns($adsStorage);

        # title
        $written = fputcsv($csvFileHandler, $outputColumns);
        if (false === $written) {
            throw new \Exception('Error write data');
        }

        foreach ($adsStorage as $ad) {
            /** @var Ad $ad */
            $written = fputcsv($csvFileHandler, $ad->getAdData($outputColumns));
            if (false === $written) {
                throw new \Exception('Error write data');
            }
        }

        $result = fclose($csvFileHandler);
        if (false === $result) {
            throw new \Exception('Error closing file');
        }
    }
}