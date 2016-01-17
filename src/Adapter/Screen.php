<?php

namespace LocalBtc\Adapter;

use LocalBtc\Ad;
use LocalBtc\AdsStorage;

class Screen extends AbstractOutput implements OutputAdapterInterface
{
    protected $column;
    protected $lt;
    protected $gt;

    public function __construct($column, $lt, $gt)
    {
        $this->column = $column;
        $this->lt = $lt;
        $this->gt = $gt;
    }

    public function process(AdsStorage $adsStorage)
    {
        $outputColumns = $this->getOutputColumns($adsStorage);

        $filteredAdsData = [];
        foreach ($adsStorage as $ad) {
            /** @var Ad $ad */
            $adData = $ad->getAdData($outputColumns);
            if ($this->lt && $adData[$this->column] > $this->lt) {
                continue;
            }
            if ($this->gt && $adData[$this->column] < $this->gt) {
                continue;
            }

            $filteredAdsData[] = $adData;
        }

        if (empty($filteredAdsData)) {
            throw new \Exception('No data', 100);
        }

        # title
//        print implode("\t", $outputColumns) . PHP_EOL;
        foreach ($filteredAdsData as $adData) {
            print implode("\t", $adData) . PHP_EOL;
        }
    }
}