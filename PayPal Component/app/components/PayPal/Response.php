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

    private $responseData = NULL;


    // We have to check if all these keys exist in array 
    public $CART_ITEM_KEYS = array(
                'l_name',
                'l_qty',
                'l_taxamt',
                'l_amt',
                'l_desc',
                'l_itemweightvalue',
                'l_itemlengthvalue',
                'l_itemwidthvalue',
                'l_itemheightvalue',
            );


    // Contents only items which we want to normalize
    private $translationTable = array(
       'CHECKOUTSTATUS' => 'checkoutStatus', 
       'CORRELATIONID' => 'correlationID',
       'PAYERID' => 'payerID',
       'PAYERSTATUS' => 'payerStatus',
       'FIRSTNAME' => 'firstName',
       'LASTNAME' => 'lastName',
       'COUNTRYCODE' => 'countryCode',
       'SHIPTONAME' => 'shipToName',
       'SHIPTOSTREET' => 'shipToStreet',
       /*
       SHIPTOCITY => 
       SHIPTOSTATE => 
       SHIPTOZIP => 
       SHIPTOCOUNTRYCODE => 
       SHIPTOCOUNTRYNAME => 
       ADDRESSSTATUS => 
       CURRENCYCODE => 
        */
       'AMT' => 'amount',
       'SHIPPINGAMT' => 'shippingAmount',
       //HANDLINGAMT => 
       'TAXAMT' => 'taxAmount',
       /*
       INSURANCEAMT => 
       SHIPDISCAMT => 
        */
       'PAYMENTREQUEST_0_CURRENCYCODE' => 'currencyCode',
       /*
       'PAYMENTREQUEST_0_AMT' 
       PAYMENTREQUEST_0_SHIPPINGAMT => "0.00" (4)
       PAYMENTREQUEST_0_HANDLINGAMT => "0.00" (4)
       PAYMENTREQUEST_0_TAXAMT => "0.00" (4)
       PAYMENTREQUEST_0_INSURANCEAMT => "0.00" (4)
       PAYMENTREQUEST_0_SHIPDISCAMT => "0.00" (4)
       PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED => "false" (5)
       PAYMENTREQUEST_0_SHIPTONAME => "Otto Sabart" (11)
       PAYMENTREQUEST_0_SHIPTOSTREET => "1 Maire-Victorin" (16)
       PAYMENTREQUEST_0_SHIPTOCITY => "Toronto" (7)
       PAYMENTREQUEST_0_SHIPTOSTATE => "Ontario" (7)
       PAYMENTREQUEST_0_SHIPTOZIP => "M5A 1E1" (7)
       PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE => "CA" (2)
       PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME => "Canada" (6)
       PAYMENTREQUESTINFO_0_ERRORCODE => "0"
        */
    );


    public function __construct($data) {

        $this->responseData = $this->normalizeKeys($data);
    }


    private function normalizeKeys(array $data) {

        $normalized = array();

        foreach ($data as $key => $value) {

            if (array_key_exists($key, $this->translationTable))
                $normalized[$this->translationTable[$key]] = $value;
            else $normalized[strtolower($key)] = $value;
        }

        return $normalized;
    }


    public function getResponseData($key = NULL) {

        if (isset($key))
            return array_key_exists($key, $this->responseData) ? $this->responseData[$key] : NULL;
        else
            return ArrayHash::from($this->responseData);
    }

    public function setResponseData($arr) {

        $this->responseData = $arr;
    }


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
     * Returns PayPal's cart items in Nette\ArrayHash or false if there are no items.
     *
     * @param $data Data from PayPal response
     * @return Nette\ArrayHash or boolean
     */
    public function getCartItems() {

        /**
         * @todo Edit regular expressin - it's neccessary to control right item keys
         */
        $itemsData = $this->array_keys_by_ereg($this->responseData, '/^l_[a-z]+[0-9]+$/');
        if (empty($itemsData))
            return false;

        $items = array();
        $itemsCount = count($itemsData) / count($this->CART_ITEM_KEYS);

        assert(is_int($itemsCount));

        for ($i = 0; $i < $itemsCount; ++$i) {

            $keys = array();
            foreach ($this->CART_ITEM_KEYS as $key)
                $keys[] = $key . $i;

            if ($this->array_keys_exist($itemsData, $keys)) {

                $items[] = array(
                    'name'          => $itemsData['l_name'            .$i],
                    'quantity'      => $itemsData['l_qty'             .$i],
                    'taxAmount'     => $itemsData['l_taxamt'          .$i],
                    'amount'        => $itemsData['l_amt'             .$i],
                    'description'   => $itemsData['l_desc'            .$i],
                    'weightValue'   => $itemsData['l_itemweightvalue' .$i],
                    'lengthValue'   => $itemsData['l_itemlengthvalue' .$i],
                    'widthValue'    => $itemsData['l_itemwidthvalue'  .$i],
                    'heightValue'   => $itemsData['l_itemheightvalue' .$i],
                );
            }
        }

        return ArrayHash::from($items);
    }


    public function getSuccess() {

        if (strcasecmp($this->getResponseData()->ack, 'success') === 0 ||
            strcasecmp($this->getResponseData()->ack, 'successwithwarning') === 0)
            return true;

        //if (strcmp($this->responseData['ACK'], 'success') === 0 ||
        //   strcmp($this->responseData['ACK'], 'successwithwarning') === 0)
        //    return true;

        return false;
    }


    /**
     * @todo Maybe it's not neccesary do it like this..
     */
    public function getToken() {

        return $this->getResponseData()->token;
    }


    public function getPayerID() {

        return $this->getResponseData()->payerID;
    }
    /* ******************************************** */


    public function getErrors() {

        return $this->array_keys_by_ereg($this->responseData, '/^L_LONGMESSAGE[0-9]+/');
    }


    /**
     * If some error, true is returned.
     * @return bool
     */
    public function isError() {

        return !empty($this->errors);
    }


    private function err($message) {

        $this->errors[] = $message;
    }

}
