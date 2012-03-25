<?php

namespace PayPal;


class API extends \Nette\Object {

    const VERSION = '66';

    // SANDBOX
    const SANDBOX_END_POINT = 'https://api-3t.sandbox.paypal.com/nvp';
    const SANDBOX_PAYPAL_URL = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=';

    const END_POINT = 'https://api-3t.paypal.com/nvp';
    const PAYPAL_URL = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';

    private $data = array(
                          'proxyHost' => '127.0.0.1',
                          'proxyPort' => '808',
                         );

    private $sandbox = false;
    private $useProxy = false;

    private $sbnCode = 'PP-ECWizard';
    private $token;

    private $endPoint;
    private $paypalURL;

    private $error = false;

    private $errors = array();

    
    public function __construct($opts = array()) {

        $this->setPaypalCommunication();
    }


    public function setPaypalCommunication() {

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

        if (strcasecmp($status, 'success') === 0 || strcasecmp($status, 'successwithwarning') === 0) {

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

        if(strcasecmp($status, 'success') != 0 && strcasecmp($status, 'successwithwarning') != 0) {

            $this->err($this->value('L_LONGMESSAGE0', $resArray));
            return false;
        }

        return $resArray;
    }
    


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
        $resData = array_merge($data, array('METHOD' => $method));
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


    public function getUrl() {

        return $this->paypalURL . $this->token;
    }

    
    public function deformatQuery($query) {

        parse_str($query, $data);
        return $data;
    }


    public function buildQuery(array $data) {

        $controlData = array(
                             'VERSION' => self::VERSION,
                             'PWD' => $this->password,
                             'USER' => $this->username,
                             'SIGNATURE' => $this->signature,
                             'BUTTONSOURCE' => $this->sbnCode,
                            );

        $resData = array_merge($data, $controlData);

        //foreach ($data as $key => $value)
            //$data[$key] = urlencode($value);

        return http_build_query($resData, '', '&');
    }



    public function isError() {

        return (bool) count($this->errors);
    }


    public function getErrors() {

        return $this->errors;
    }


    public function setSignature($signature) {

        $this->data['signature'] = $signature;
        return $this;
    }


    public function getSignature() {

        return isset($this->data['signature']) ? $this->data['signature'] : NULL;
    }


    public function setPassword($password) {

        $this->data['password'] = $password;
        return $this;
    }


    public function getPassword() {

        return isset($this->data['password']) ? $this->data['password'] : NULL;
    }


    public function getUsername() {

        return isset($this->data['username']) ? $this->data['username'] : NULL;
    }


    public function setUsername($username) {

        $this->data['username'] = $username;
        return $this;
    }


    public function getProxyPort() {

        return isset($this->data['proxyPort']) ? $this->data['proxyPort'] : NULL;
    }


    public function setPort(int $proxyPort) {

        $this->data['proxyPort'] = $proxyPort;
        return $this;
    }

    public function getProxyHost() {

        return isset($this->data['proxyHost']) ? $this->data['proxyHost'] : NULL;
    }


    public function setHost($proxyHost) {

        $this->data['proxyHost'] = $proxyHost;
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


};
