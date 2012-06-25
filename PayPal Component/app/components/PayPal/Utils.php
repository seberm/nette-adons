<?php

/**
 * @class PayPal\Request
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

class Utils {

    public static function translateKeys(array $data, $translationTable = array()) {

        $normalized = array();

        foreach ($data as $key => $value) {

            if (array_key_exists($key, $translationTable))
                $normalized[$translationTable[$key]] = $value;
            else $normalized[strtolower($key)] = $value;
        }

        return $normalized;
    }
}
