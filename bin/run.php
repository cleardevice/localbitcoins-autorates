<?php

namespace LocalBtc;

use CommandLine;
use LocalBtc\Adapter\Csv;
use LocalBtc\Adapter\Screen;

require __DIR__ . '/../vendor/autoload.php';

class Runner
{
    const OUTPUT_COLUMNS = [
//        'profile/last_online',
//        'data/created_at',
        'data/online_provider',
        'data/currency',
        'data/temp_price',
        'data/min_amount',
        'data/max_amount',
        'data/countrycode',
        'data/temp_price_usd',
        'data/ad_id',
        'profile/name',
    ];

    protected $api;

    public function __construct()
    {
        $this->api = new PublicApi();
    }

    public function processParams()
    {
        $args = CommandLine::parseArgs($_SERVER['argv']);

        if (isset($args['h']) || isset($args['help'])) {
            $this->usage();
            return true;
        }

        if (isset($args['o'])) {
            $args['out'] = $args['o'];
        }

        if (false === isset($args['out'])) {
            $this->error(1, 'No output format specified');
        }

        switch (strtolower($args['o'])) {
            case 'csv':
                $currency = $this->getCurrency($args);
                if (false === $currency) {
                    $currencies = $this->getCurrencies($args);
                } else {
                    $currencies = [$currency];
                }

                $this->getAsCsv($this->getRequestType($args), $currencies);
                return true;

            case 'screen':
                $currency = $this->getCurrency($args);
                if (false === $currency) {
                    $this->error(1, 'Empty currency');
                }

                $this->checkCurrency($currency);

                $this->showToScreen(
                    $this->getRequestType($args), $currency,
                    $this->getColumnName($args), $this->getValue($args, 'lt'), $this->getValue($args, 'gt')
                );
                return true;
        }

        $this->error(1, 'Unknown output format specified');
    }

    protected function error($errorCode, $errorText = '')
    {
        $errorText = $errorText . PHP_EOL . 'Try \'--help\' for more information.';
        throw new \Exception($errorText, $errorCode);
    }

    protected function getAdsList($type, $currency)
    {
        $adsStorage = $this->api->getByOperationTypeAndCurrency($type, $currency);
        return $adsStorage;
    }

    protected function getAsCsv($type, $currencies)
    {
        $adsStorage = new AdsStorage();
        foreach ($currencies as $currency) {
            $adsStorage->addAll($this->getAdsList($type, $currency));
        }

        $fileName = sprintf('%s_%s.csv', $type, implode('-', $currencies));
        $adapter = new Csv($fileName);
        $adapter->setOutputColumns(self::OUTPUT_COLUMNS);
        $adapter->process($adsStorage);
    }

    protected function showToScreen($type, $currency, $column, $lt = false, $gt = false)
    {
        $adsStorage = new AdsStorage();
        $adsStorage->addAll($this->getAdsList($type, $currency));

        $adapter = new Screen($column, $lt, $gt);
        $adapter->setOutputColumns(self::OUTPUT_COLUMNS);
        $adapter->process($adsStorage);
    }

    protected function getRequestType(array $args)
    {
        if (isset($args['t'])) {
            $args['type'] = $args['t'];
        }

        if (false === isset($args['type'])) {
            $this->error(1, 'Empty type');
        }

        if (false === $this->api->checkRequestType($args['type'])) {
            $errorText = sprintf('Types should be %s. Typed: %s', implode('/', PublicApi::REQUEST_TYPES), $args['type']);
            $this->error(2, $errorText);
        }

        return $args['type'];
    }

    protected function getCurrency(array $args)
    {
        if (isset($args['c'])) {
            $args['currency'] = $args['c'];
        }

        if (false === isset($args['currency'])) {
            return false;
        }

        return $args['currency'];
    }

    protected function getCurrencies(array $args)
    {
        if (false === isset($args['currencies'])) {
            $this->error(1, 'Empty currencies');
        }

        $currencies = explode(',', $args['currencies']);
        foreach ($currencies as $currency) {
            $this->checkCurrency($currency);
        }

        return $currencies;
    }

    protected function checkCurrency($currency)
    {
        if (false === $this->api->checkCurrency($currency)) {
            $errorText = sprintf('Currencies should be %s. Typed: %s', implode('/', PublicApi::REQUEST_CURRENCIES), $currency);
            $this->error(2, $errorText);
        }
    }

    protected function getColumnName(array $args)
    {
        $columnName = 'data/temp_price';
        if (isset($args['column'])) {
            $columnName = $args['column'];
        }

        return $columnName;
    }

    protected function getValue(array $args, $argType)
    {
        if (false === isset($args[$argType])) {
            return false;
        }

        return doubleval($args[$argType]);
    }

    protected function usage() {
        $usageString = <<<END

SYNOPSIS
        php bin/run.php -o OUTPUT_FORMAT [OPTION]...
        php bin/run.php -o csv -t OPERATION_TYPE --currencies [CURRENCY[,CURRENCY...]]
        php bin/run.php -o screen -t OPERATION_TYPE -c CURRENCY --column COLUMN_NAME(default: data/temp_price) [--lt VALUE] [--gt VALUE]

DESCRIPTION
        Get Localbitcoin dealings information.

OPTIONS
        -h, --help
            Output usage information

        -o, --out
            Output format. Allowed: csv, screen

        -t, --type
            Operation type: %1\$s

        --currencies
            Currencies: %2\$s

        -c, --currency
            Currency. Allowed: %2\$s

        --column
            LocalBitcoins column name

        --lt VALUE
            Less-than VALUE

        --gt VALUE
            Greater-than VALUE
END;
        $usageString = sprintf($usageString,
            implode(', ', PublicApi::REQUEST_TYPES),
            implode(', ', PublicApi::REQUEST_CURRENCIES));

        throw new \Exception($usageString, 1);
    }
}

# run
try {
    (new Runner())->processParams();
} catch (\Exception $e) {
    print $e->getMessage() . PHP_EOL;
    exit($e->getCode());
}
