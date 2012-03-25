<?php

namespace PayPal;


class PayPal extends \Nette\Object {

    const VERSION = '66';

    // SANDBOX
    const SANDBOX_END_POINT = 'https://api-3t.sandbox.paypal.com/nvp';
    const SANDBOX_PAYPAL_URL = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=';

    const END_POINT = 'https://api-3t.paypal.com/nvp';
    const PAYPAL_URL = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';


    private $sandbox = false;
    private $useProxy = false;
    
    private $proxyHost = '127.0.0.1';
    private $proxyPort = '808';

    private $username = '';
    private $password = '';
    private $signature = '';

    private $sbnCode = 'PP-ECWizard';
private $token;

    private $endPoint;
    private $paypalURL;

    private $error = false;

    private $errors = array();

    
    public function __construct($opts = array()) {

        $this->setPaypalCommunication($opts);
    }


    private function setPaypalCommunication() {

        if ($this->sandbox) {

            $this->endPoint = self::SANDBOX_END_POINT;
            $this->paypalURL = self::SANDBOX_PAYPAL_URL;
        } else {

            $this->endPoint = self::END_POINT;
            $this->paypalURL = self::PAYPAL_URL;
        }

        /* An express checkout transaction starts with a token, that
           identifies to PayPal your transaction
           In this example, when the script sees a token, the script
           knows that the buyer has already authorized payment through
           paypal.  If no token was found, the action is to send the buyer
           to PayPal to first authorize payment
       */
    }


    /**
     * Prepares the parameters for the SetExpressCheckout API Call.
     */
    public function doExpressCheckout($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL, $ses) { 

        $query = array('PAYMENTREQUEST_0_AMT' => $paymentAmount,
                       'PAYMENTREQUEST_0_PAYMENTACTION' => $paymentType,
                       'RETURNURL' => $returnURL,
                       'CANCELURL' => $cancelURL,
                       'PAYMENTREQUEST_0_CURRENCYCODE' => $currencyCodeType,
                     );
        

        $resArray = $this->call('SetExpressCheckout', $query);

        $status = strtoupper($this->value('ACK', $resArray));

        if ($status === 'SUCCESS' || $status === 'SUCCESSWITHWARNING') {

            $ses->token = $this->value('TOKEN', $resArray);
            $this->token = $ses->token;
            
        } else {

            $this->err($this->value('L_LONGMESSAGE0', $resArray));
            return false;
        }
           
        return $resArray;
    }


    public function getShippingDetails($ses) {

        $query = array('TOKEN' => $ses->token);

        $resArray = $this->call('GetExpressCheckoutDetails', $query);
        $status = strtoupper($this->value('ACK', $resArray));

        if($status != 'SUCCESS' && $status != 'SUCCESSWITHWARNING') {

            $this->err($this->value('L_LONGMESSAGE0', $resArray));
            return false;
        }

        return $resArray;
    }
    


    /**
     * Prepares the parameters for the SetExpressCheckout API Call.
     */
    /*
    public function prepareExpressCheckout($paymentAmount, $currencyCodeType, $paymentType, $returnURL, 
                                      $cancelURL, $shipToName, $shipToStreet, $shipToCity, $shipToState,
                                      $shipToCountryCode, $shipToZip, $shipToStreet2, $phoneNum) {

        // Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation
        $nvpstr='&PAYMENTREQUEST_0_AMT='. $paymentAmount;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_PAYMENTACTION=' . $paymentType;
        $nvpstr = $nvpstr . '&RETURNURL=' . $returnURL;
        $nvpstr = $nvpstr . '&CANCELURL=' . $cancelURL;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_CURRENCYCODE=' . $currencyCodeType;
        $nvpstr = $nvpstr . '&ADDROVERRIDE=1';
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTONAME=' . $shipToName;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTOSTREET=' . $shipToStreet;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTOSTREET2=' . $shipToStreet2;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTOCITY=' . $shipToCity;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTOSTATE=' . $shipToState;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=' . $shipToCountryCode;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTOZIP=' . $shipToZip;
        $nvpstr = $nvpstr . '&PAYMENTREQUEST_0_SHIPTOPHONENUM=' . $phoneNum;
        
        $_SESSION['currencyCodeType'] = $currencyCodeType;      
        $_SESSION['PaymentType'] = $paymentType;

        $resArray = $this->call('SetExpressCheckout', $nvpstr);
        $ack = strtoupper($resArray['ACK']);

        if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {

            $token = urldecode($resArray['TOKEN']);
            $_SESSION['TOKEN'] = $token;
        }
           
        return $resArray;
    }
     */
    

    /**
     * Prepares the parameters for the GetExpressCheckoutDetails API Call.
     */
    /*
    public function confirmPayment($FinalPaymentAmt) {

        // Format the other parameters that were stored in the session from the previous calls    
        $token                 = urlencode($_SESSION['TOKEN']);
        $paymentType         = urlencode($_SESSION['PaymentType']);
        $currencyCodeType     = urlencode($_SESSION['currencyCodeType']);
        $payerID             = urlencode($_SESSION['payer_id']);

        $serverName         = urlencode($_SERVER['SERVER_NAME']);

        $nvpstr  = '&TOKEN=' . $token . '&PAYERID=' . $payerID . '&PAYMENTREQUEST_0_PAYMENTACTION=' . $paymentType . '&PAYMENTREQUEST_0_AMT=' . $FinalPaymentAmt;
        $nvpstr .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . $currencyCodeType . '&IPADDRESS=' . $serverName; 

     */
         /* Make the call to PayPal to finalize payment
            If an error occured, show the resulting errors
            */
    //    $resArray = $this->call('DoExpressCheckoutPayment', $nvpstr);

        /* Display the API response back to the browser.
           If the response from PayPal was a success, display the response parameters'
           If the response was an error, display the errors received using APIError.php.
           */
    //    $ack = strtoupper($resArray['ACK']);

    //    return $resArray;
    //}
    
    /**
     * This function makes a DoDirectPayment API call.
     */
//!todo pouzit pro registrovaneho uzivatele!
/*
    public function directPayment($paymentType, $paymentAmount, $creditCardType, $creditCardNumber,
                            $expDate, $cvv2, $firstName, $lastName, $street, $city, $state, $zip, 
                            $countryCode, $currencyCode) {

        //Construct the parameter string that describes DoDirectPayment
        $nvpstr = '&AMT=' . $paymentAmount;
        $nvpstr = $nvpstr . '&CURRENCYCODE=' . $currencyCode;
        $nvpstr = $nvpstr . '&PAYMENTACTION=' . $paymentType;
        $nvpstr = $nvpstr . '&CREDITCARDTYPE=' . $creditCardType;
        $nvpstr = $nvpstr . '&ACCT=' . $creditCardNumber;
        $nvpstr = $nvpstr . '&EXPDATE=' . $expDate;
        $nvpstr = $nvpstr . '&CVV2=' . $cvv2;
        $nvpstr = $nvpstr . '&FIRSTNAME=' . $firstName;
        $nvpstr = $nvpstr . '&LASTNAME=' . $lastName;
        $nvpstr = $nvpstr . '&STREET=' . $street;
        $nvpstr = $nvpstr . '&CITY=' . $city;
        $nvpstr = $nvpstr . '&STATE=' . $state;
        $nvpstr = $nvpstr . '&COUNTRYCODE=' . $countryCode;
        $nvpstr = $nvpstr . '&IPADDRESS=' . $_SERVER['REMOTE_ADDR'];

        $resArray = $this->call('DoDirectPayment', $nvpstr);

        return $resArray;
    }
*/


    private function call($method, $data) {

        $ch = curl_init($this->endPoint);

        // Set up verbose mode
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // We should check if paypal has valid certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Just do normal POST
        curl_setopt($ch, CURLOPT_POST, true);
        
        if ($this->useProxy)
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyHost. ':' . $this->proxyPort); 

        // NVP Request
        $controlData = array('METHOD' => $method,
                             'VERSION' => self::VERSION,
                             'PWD' => $this->password,
                             'USER' => $this->username,
                             'SIGNATURE' => $this->signature,
                             'BUTTONSOURCE' => $this->sbnCode,
                            );
        
        $resData = array_merge($data, $controlData);
        $request = $this->buildQuery($resData);

        // POST data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        // Execute
        $response = curl_exec($ch);

        if (curl_errno($ch))
            $this->err(curl_error($ch));

        curl_close($ch);

        return \Nette\ArrayHash::from($this->deformatQuery($response));
    }


    public function getURL() {

        return $this->paypalURL . $this->token;
    }

    
    private function deformatQuery($query) {

        parse_str($query, $data);
        return $data;
    }


    public function buildQuery(array $data) {

        //foreach ($data as $key => $value)
            //$data[$key] = urlencode($value);

        return http_build_query($data, '', '&');
    }


    public function setSignature($signature) {

        $this->signature = $signature;
        return $this;
    }


    public function isError() {

        return (bool) count($this->errors);
    }


    public function getErrors() {

        return $this->errors;
    }


    public function setPassword($password) {

        $this->password = $password;
        return $this;
    }


    public function getUsername() {

        return $this->username;
    }


    public function setUsername($username) {

        $this->username = $username;
        return $this;
    }


    public function getPort() {

        return $this->port;
    }


    public function setPort(int $port) {

        $this->port = $port;
        return $this;
    }

    public function getHost() {

        return $this->host;
    }


    public function setHost($host) {

        $this->host = $host;
        return $this;
    }


    public function setSandBox($opt = true) {

        $this->sandbox = $opt;
        $this->setPaypalCommunication();

        return $this;
    }


    private function err($message) {

        $this->errors[] = $message;
    }

    
    private function value($key, \Nette\ArrayHash $arr) {

        return $arr->offsetExists($key) ? $arr->offsetGet($key) : '';
    }


public function getToken() {return $this->token;}
}
