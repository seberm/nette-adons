<?php

/**
 * @class PayPal\Response
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use PayPal\Utils;

use \Nette;
use Nette\Object,
    Nette\ArrayHash;


class Query extends Object {

    private $query;

    private $translationTable = array(
           'paymentAction' => 'PAYMENTREQUEST_0_PAYMENTACTION',
           'returnUrl' => 'RETURNURL',
           'cancelUrl' => 'CANCELURL',
           'currencyCode' => 'PAYMENTREQUEST_0_CURRENCYCODE',
           'itemsAmount' => 'PAYMENTREQUEST_0_ITEMAMT',
           'taxAmount' => 'PAYMENTREQUEST_0_TAXAMT',
           'shippingAmount' => 'PAYMENTREQUEST_0_SHIPPINGAMT',
           'amount' => 'PAYMENTREQUEST_0_AMT',
    );


    public function __construct(array $query) {

        $this->query = $query; //Utils::translateKeys($query, $this->translationTable);
    }


    public function has($key) {

        return array_key_exists($key, $this->query);
    }


    public function getData($key = NULL) {

        if (func_num_args() === 0)
            return ArrayHash::from($this->query);

        if ($this->has($key))
            return $this->query[$key];
    }


    public function appendQuery($query, $val = NULL) {

        /*
        if ($query instanceof Query) {

        }
        */
        if (isset($val))
            $this->query[$query] = $val;
        elseif (is_array($query))
            $this->query = array_merge($query, $this->query);
    }


    public function 
}
