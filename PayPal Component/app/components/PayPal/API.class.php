<?php
/**
 * @class PayPal\API
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use PayPal\Response,
    PayPal\Request;

use \Nette;
use Nette\Utils\Arrays,
    Nette\Object,
    Nette\Http\Url;


class API extends Object
{

    /**
     * Tells which version of PayPay API we want use
     */
    //const VERSION = '66';
    const VERSION = '72.0';

    // PayPal SandBox URLs
    const SANDBOX_END_POINT = 'https://api-3t.sandbox.paypal.com/nvp';

    const SANDBOX_PAYPAL_URL = 'https://www.sandbox.paypal.com/webscr';

    // Direct PayPal URLs
    const END_POINT = 'https://api-3t.paypal.com/nvp';

    const CURRENCY_CROUND = 'CZK';
    const CURRENCY_EURO = 'EUR';

    /** @deprecated */
    private $cart = array();

    // Options
    private $data = array(
                          'proxyHost' => '127.0.0.1',
                          'proxyPort' => '808',
                          'username'  => '',
                          'password'  => '',
                          'signature' => '',
                         );

    const PAYPAL_URL = 'https://www.paypal.com/cgi-bin/webscr';

    private $sandbox = FALSE;

    private $useProxy = FALSE;

    private $token;
    //public $invoiceValue = NULL;

    public function __construct($opts = array()) {

        if (count($opts))
            $this->setData($opts);
    }


    /**
     * Sets object data
     * @var string|array $opts
     * @var mixed $val
     * @return PayPal\API (supports fluent interface)
     */
    public function setData($opts = array(), $val = NULL)
    {
        if (is_string($opts)) {
            $this->data[$opts] = $val;
        } elseif (is_array($opts)) {
            $this->data = array_merge($this->data, $opts);
        }

        return $this;
    }


    public function getData($key = NULL)
    {
        if (is_string($key)) {

            if (array_key_exists($key, $this->data)) {
                return $this->data[$key];
            } else {
                return NULL;
            }
        }

        return $this->data;
    }


    /**
     * Adds new item to PayPals Cart
     */
    public function addItem($name, $description, $price, $quantity) {

        $this->cart[] = array(
                                'L_PAYMENTREQUEST_0_NAME' => $name,
                                'L_PAYMENTREQUEST_0_DESC' => $description,
                                'L_PAYMENTREQUEST_0_AMT' => $price,
                                'L_PAYMENTREQUEST_0_QTY' => $quantity,
                            );
    }


    /**
     * Prepares the parameters for the SetExpressCheckout API Call.
     */
    public function setExpressCheckout($shipping, $tax, $currencyCodeType, $paymentType, $returnURL, $cancelURL, $ses) { 

        $data = array(
                       'paymentAction' => $paymentType,
                       'returnUrl' => $returnURL,
                       'cancelUrl' => $cancelURL,
                       'currencyCode' => $currencyCodeType,
                     );

        $request = new Request($data);

        $id = 0;
        $itemsPrice = 0;
        foreach ($this->cart as $item) {
            foreach ($item as $key => $val) {

                if ($key == 'L_PAYMENTREQUEST_0_AMT')
                    $itemsPrice += $val;

                $query[$key.$id] = $val;
            }

            $id++;
        }

        $query['amount'] = $itemsPrice;
        $query['PAYMENTREQUEST_0_TAXAMT'] = $tax;
        $query['PAYMENTREQUEST_0_SHIPPINGAMT'] = $shipping;
        $query['PAYMENTREQUEST_0_AMT'] = $query['PAYMENTREQUEST_0_ITEMAMT'] + $query['PAYMENTREQUEST_0_TAXAMT'] + $query['PAYMENTREQUEST_0_SHIPPINGAMT'];

        $response = $this->call('SetExpressCheckout', $query);

        if ($response->success) {

            $ses->token = $response->token;
            $ses->paymentType = $paymentType;
            $ses->currencyCodeType = $currencyCodeType;
            $ses->amount = $query['PAYMENTREQUEST_0_AMT'];

            $this->token = $ses->token;

        } 

        return $response;
    }



    /**
     * Prepares the parameters for the SetExpressCheckout API Call.
     */
    public function doExpressCheckout($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL, $ses)
    {


        $query = array(
            'PAYMENTREQUEST_0_AMT' => $paymentAmount,
            'PAYMENTREQUEST_0_PAYMENTACTION' => $paymentType,
            'RETURNURL' => $returnURL,
            'CANCELURL' => $cancelURL,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $currencyCodeType,
            'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD' => 'InstantPaymentOnly',
        );

        $response = $this->call('SetExpressCheckout', $query);

        if ($response->success) {
            $ses->token = $response->token;
            $this->token = $ses->token;

        } 

        return $response;
    }


    /**
     * Confirmation of paypal payment
     */
    public function confirmExpressCheckout(\Nette\Http\SessionSection $ses) { 

        $query = array('PAYMENTREQUEST_0_AMT' => $ses->amount,
                       'PAYERID' => $ses->payerID,
                       'TOKEN' => $ses->token,
                       'PAYMENTREQUEST_0_PAYMENTACTION' => $ses->paymentType,
                       'PAYMENTREQUEST_0_CURRENCYCODE' => $ses->currencyCodeType,
                       'IPADDRESS' => urlencode($_SERVER['SERVER_NAME']),
                     );

        $response = $this->call('DoExpressCheckoutPayment', $query);

        if ($response->success)
            $ses->remove();
           
        return $response;
    }


    public function getShippingDetails($ses)
    {
        $query = array('TOKEN' => $ses->token);

        $response = $this->call('GetExpressCheckoutDetails', $query);

        if ($response->success)
            $ses->payerID = $response->responseData->payerID;

        return $response;
    }
    

    public function doPayment($paymentType, $ses)
    {
        $details = $this->getShippingDetails($ses);

        if (!$details)
            return false;

        $query = array(
            'PAYMENTACTION' => $paymentType,
            'PAYERID' => $details['PAYERID'],
            'TOKEN' => $ses->token,
            'AMT' => $details['AMT'],
            'CURRENCYCODE' => $details['CURRENCYCODE'],
            //'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD' => 'InstantPaymentOnly'
        );

        return $this->call('DoExpressCheckoutPayment', $query);
    }


    public function getEndPoint()
    {
        return $this->sandbox ? self::SANDBOX_END_POINT : self::END_POINT;
    }


    private function call($method, $data)
    {

        $ch = curl_init($this->endPoint);

        // Set up verbose mode
        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // We should check if paypal has valid certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        // Just do normal POST
        curl_setopt($ch, CURLOPT_POST, TRUE);

        if ($this->useProxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyHost . ':' . $this->proxyPort);
        }

        // NVP Request
        $resData = array_merge($data, array('METHOD' => $method));
        $request = $this->buildQuery($resData);

        // POST data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        // Execute
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->err(curl_error($ch));
        }

        curl_close($ch);

        return new Response($this->deformatQuery($response));
    }


    /**
     * Generates URL to PayPal for redirection.
     * @return Nette\Http\Url
     */
    public function getUrl()
    {
        $url = new Url($this->sandbox ? self::SANDBOX_PAYPAL_URL : self::PAYPAL_URL);

        $query = array(
            'cmd' => '_express-checkout',
            'token' => $this->token,
        );

        $url->setQuery($query);

        return $url;
    }


    public function deformatQuery($query)
    {
        parse_str($query, $data);
        return $data;
    }


    /**
     * Builds basic query to paypal.
     * @var array $data
     * @return string query
     */
    public function buildQuery(array $data)
    {
        $controlData = array(
            'VERSION' => self::VERSION,
            'PWD' => $this->password,
            'USER' => $this->username,
            'SIGNATURE' => $this->signature,
        );

        $resData = array_merge($data, $controlData);

        //foreach ($data as $key => $value)
        //$data[$key] = urlencode($value);

        return http_build_query($resData, '', '&');
    }


    public function setSignature($signature)
    {
        return $this->setData('signature', (string)$signature);
    }


    public function getSignature()
    {
        return $this->getData('signature');
    }


    public function setPassword($password)
    {
        return $this->setData('password', (string)$password);
    }


    public function getPassword()
    {
        return $this->getData('password');
    }


    public function getUsername()
    {
        return $this->getData('username');
    }


    public function setUsername($username)
    {
        return $this->setData('username', (string)$username);
    }


    public function getProxyPort()
    {
        return $this->getData('proxyPort');
    }


    public function setPort($proxyPort)
    {
        $this->data['proxyPort'] = (int)$proxyPort;
        return $this;
    }

    public function getProxyHost()
    {
        return $this->getData('proxyHost');
    }


    public function setHost($proxyHost)
    {
        return $this->setData('proxyHost', (string)$proxyHost);
    }


    public function setSandBox($opt = TRUE)
    {
        $this->sandbox = (bool)$opt;
        return $this;
    }
}
