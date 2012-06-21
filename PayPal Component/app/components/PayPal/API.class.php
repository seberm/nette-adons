<?php
/**
 * @class PayPal\API
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use \Nette\Utils\Arrays,
    \Nette\Http\Url;


class API extends \Nette\Object
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
    private $errors = array();

    private $cart = array();


	public function __construct($opts = array())
	{

		if (count($opts)) {
			$this->setData($opts);
		}
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

        $query = array(
                       'PAYMENTREQUEST_0_PAYMENTACTION' => $paymentType,
                       'RETURNURL' => $returnURL,
                       'CANCELURL' => $cancelURL,
                       'PAYMENTREQUEST_0_CURRENCYCODE' => $currencyCodeType,
                     );

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

        $query['PAYMENTREQUEST_0_ITEMAMT'] = $itemsPrice;
        $query['PAYMENTREQUEST_0_TAXAMT'] = $tax;
        $query['PAYMENTREQUEST_0_SHIPPINGAMT'] = $shipping;
        $query['PAYMENTREQUEST_0_AMT'] = $query['PAYMENTREQUEST_0_ITEMAMT'] + $query['PAYMENTREQUEST_0_TAXAMT'] + $query['PAYMENTREQUEST_0_SHIPPINGAMT'];

        $resArray = $this->call('SetExpressCheckout', $query);
        $status = strtoupper($this->value('ACK', $resArray));

        if (strcasecmp($status, 'success') === 0 || strcasecmp($status, 'successwithwarning') === 0) {

            $ses->token = $this->value('TOKEN', $resArray);
            $ses->paymentType = $paymentType;
            $ses->currencyCodeType = $currencyCodeType;
            $ses->amount = $query['PAYMENTREQUEST_0_AMT'];

            $this->token = $ses->token;

        } else {

            $this->err($this->value('L_LONGMESSAGE0', $resArray));
            return false;
        }
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

		$resArray = $this->call('SetExpressCheckout', $query);

		$status = strtoupper($this->value('ACK', $resArray));


		if (strcasecmp($status, 'success') === 0 || strcasecmp($status, 'successwithwarning') === 0) {
			$ses->token = $this->value('TOKEN', $resArray);
			$this->token = $ses->token;

		} else {

			$this->err($this->value('L_LONGMESSAGE0', $resArray));
			return FALSE;
		}

		return $resArray;
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

        $resArray = $this->call('DoExpressCheckoutPayment', $query);

        $status = strtoupper($this->value('ACK', $resArray));

        if (strcasecmp($status, 'success') === 0 || strcasecmp($status, 'successwithwarning') === 0) {

            $ses->remove();
        } else {

            $this->err($this->value('L_LONGMESSAGE0', $resArray));
            return false;
        }
           
        return $resArray;
    }


	public function getShippingDetails($ses)
	{
		$query = array('TOKEN' => $ses->token);

		$resArray = $this->call('GetExpressCheckoutDetails', $query);
		$status = strtoupper($this->value('ACK', $resArray));

		if (strcasecmp($status, 'success') != 0 && strcasecmp($status, 'successwithwarning') != 0) {

			$this->err($this->value('L_LONGMESSAGE0', $resArray));
			return FALSE;
		}
    
        $ses->payerID = $this->value('PAYERID', $resArray);

        return $resArray;
    }
    

	public function doPayment($paymentType, $ses)
	{
		$details = $this->getShippingDetails($ses);

		if (!$details) {
			return FALSE;
		}

		$query = array(
			'PAYMENTACTION' => $paymentType,
			'PAYERID' => $details['PAYERID'],
			'TOKEN' => $ses->token,
			'AMT' => $details['AMT'],
			'CURRENCYCODE' => $details['CURRENCYCODE'],
			//'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD' => 'InstantPaymentOnly'
		);

		$resArray = $this->call('DoExpressCheckoutPayment', $query);
		$status = strtoupper($this->value('ACK', $resArray));

		if (strcasecmp($status, 'success') != 0 && strcasecmp($status, 'successwithwarning') != 0) {
			$this->err($this->value('L_LONGMESSAGE0', $resArray));
			return FALSE;
		}

		return $resArray;
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

		return $this->deformatQuery($response);
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
		return \Nette\ArrayHash::from($data);
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


	/**
	 * If some error, true is returned.
	 * @return bool
	 */
	public function isError()
	{
		return (bool)count($this->errors);
	}


	public function getErrors()
	{
		return $this->errors;
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


	private function err($message)
	{
		$this->errors[] = $message;
	}


	private function value($key, \Nette\ArrayHash $arr)
	{
		return $arr->offsetExists($key) ? $arr->offsetGet($key) : '';
	}
}
