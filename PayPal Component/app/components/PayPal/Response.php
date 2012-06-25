<?php

/**
 * @class PayPal\Response
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use \Nette;
use Nette\Object,
    Nette\ArrayHash;


class Response extends Object {

    // Number of items of one PayPal cart item
    const ITEM_VALUES_COUNT = 9;

    // We have to check if all these keys exist in array 
    public $ITEM_KEYS = array(
                'L_NAME',
                'L_QTY',
                'L_TAXAMT',
                'L_AMT',
                'L_DESC',
                'L_ITEMWEIGHTVALUE',
                'L_ITEMLENGTHVALUE',
                'L_ITEMWIDTHVALUE',
                'L_ITEMHEIGHTVALUE',
            );


    /**
     * Finds out if all keys in $keys are included in $arr.
     *
     * @param $arr Source array
     * @param $keys Array of keys
     * @return boolean
     */
    public function array_keys_exist($arr, $keys) {

        if (count(array_intersect($keys, array_keys($arr))) == count($keys))
            return true;

        return false;
    }


    /**
     * Returns subarray from array.
     * New array is created only from keys which matches the reqular expression.
     *
     * @param $arr Source array
     * @param $pattern Regular expression
     * @return array Subarray
     */
    public function array_keys_by_ereg($arr, $pattern) {

        $subArray = array();

        $matches = preg_grep($pattern, array_keys($arr));
        foreach ($matches as $match)
            $subArray[$match] = $arr[$match];

        return $subArray;
    }



    /**
     * Returns PayPal's cart items in Nette\ArrayHash.
     *
     * @param $data Data from PayPal response
     * @return Nette\ArrayHash
     */
    public function parsePayPalItems($data) {

        $itemsData = $this->array_keys_by_ereg($data, '/^L_[A-Z]+[0-9]+/');

        $items = array();
        $itemsCount = count($itemsData) / count($this->ITEM_KEYS);

        assert(is_int($itemsCount));

        for ($i = 0; $i < $itemsCount; ++$i) {

            $keys = array();
            foreach (self::$ITEM_KEYS as $key)
                $keys[] = $key . $i;

            if ($this->array_keys_exist($itemsData, $keys)) {

                $items[] = array(
                    'name'          => $itemsData['L_NAME'            .$i],
                    'quantity'      => $itemsData['L_QTY'             .$i],
                    'taxAmount'     => $itemsData['L_TAXAMT'          .$i],
                    'amount'        => $itemsData['L_AMT'             .$i],
                    'description'   => $itemsData['L_DESC'            .$i],
                    'weightValue'   => $itemsData['L_ITEMWEIGHTVALUE' .$i],
                    'lengthValue'   => $itemsData['L_ITEMLENGTHVALUE' .$i],
                    'widthValue'    => $itemsData['L_ITEMWIDTHVALUE'  .$i],
                    'heightValue'   => $itemsData['L_ITEMHEIGHTVALUE' .$i],
                );
            }
        }

        return ArrayHash::from($items);
    }
}
