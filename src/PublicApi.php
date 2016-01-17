<?php

namespace LocalBtc;

use \Curl\Curl;

class PublicApi
{
    const REQUEST_TYPES = ['buy', 'sell'];
    const REQUEST_CURRENCIES = ['uah', 'usd', 'rub'];

    const LINK_OPERATION_TYPE_AND_CURRENCY = 'https://localbitcoins.com/%s-bitcoins-online/%s/.json';

    /**
     * @param $type
     * @param $currency
     *
     * @return AdsStorage
     *
     * @throws \Exception
     */
    public function getByOperationTypeAndCurrency($type, $currency)
    {
        $adsStorage = new AdsStorage();

        $curl = new Curl();

        $link = sprintf(self::LINK_OPERATION_TYPE_AND_CURRENCY, $type, $currency);
        do {
            $response = $curl->get($link);

            if (false === $response) {
                throw new \Exception('Error response', 100);
            }

            $adsList = $this->getAdsList($response);
            if (false === empty($adsList)) {
                foreach ($adsList as $ad) {
                    $adObject = new Ad($ad);
                    $adsStorage->add($adObject);
                }
            }

            $link = $this->getNextLink($response);
        } while ($link);

        return $adsStorage;
    }

    protected function getNextLink($response)
    {
        $link = false;
        if (isset($response->pagination->next)) {
            $link = $response->pagination->next;
        }

        return $link;
    }

    protected function getAdsList($response)
    {
        $adsList = null;
        if (isset($response->data->ad_list)) {
            $adsList = $response->data->ad_list;
        }

        return $adsList;
    }

    public function checkRequestType($type)
    {
        return in_array($type, self::REQUEST_TYPES);
    }

    public function checkCurrency($currency)
    {
        return in_array($currency, self::REQUEST_CURRENCIES);
    }
}