<?php

namespace LocalBtc;

class Ad
{
    protected $adData;

    public function __construct(\StdClass $adData)
    {
        if (false === isset($adData->data)) {
            throw new \Exception('Empty ad data field');
        }

        $objectDataArray = (array)$adData;
        $this->adData = $this->transform($objectDataArray);
    }

    protected function transform(array $data, $prefix = '')
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $objectDataArray = $value;

                $result = array_merge($result, $this->transform((array)$objectDataArray, $key));
                continue;
            }

            if (false === empty($prefix)) {
                $key = $prefix . '/' . $key;
            }
            $result[$key] = $value;
        }

        return $result;
    }

    public function getKeys()
    {
        return array_keys($this->adData);
    }

    public function getAdData(array $columns = [])
    {
        if (empty($columns)) {
            return $this->adData;
        }

        $columnsAsKeys = array_flip($columns);
        return array_merge($columnsAsKeys, array_intersect_key($this->adData, $columnsAsKeys));
    }
}