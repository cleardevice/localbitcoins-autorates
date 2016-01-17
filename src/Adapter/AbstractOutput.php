<?php

namespace LocalBtc\Adapter;

use LocalBtc\AdsStorage;

abstract class AbstractOutput implements OutputAdapterInterface
{
    protected $outputColumns = [];

    public function setOutputColumns(array $columns)
    {
        $this->outputColumns = $columns;
    }

    protected function getOutputColumns(AdsStorage $adsStorage)
    {
        $outputColumns = $this->outputColumns;
        if (empty($this->outputColumns)) {
            $adsStorage->rewind();
            $ad = $adsStorage->current();

            $outputColumns = $ad->getKeys();
        }

        return $outputColumns;
    }
}