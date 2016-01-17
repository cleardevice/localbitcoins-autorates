<?php

namespace LocalBtc\Adapter;

use LocalBtc\AdsStorage;

interface OutputAdapterInterface {
    public function setOutputColumns(array $columns);
    public function process(AdsStorage $adsStorage);
}