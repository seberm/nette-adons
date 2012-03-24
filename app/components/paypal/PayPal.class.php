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
    
	private $host = '127.0.0.1';
	private $port = '808';

	private $username = '';
	private $password = '';
	private $signature = '';

	private $sbnCode = 'PP-ECWizard';
private $token;

    private $endPoint = '';
    private $paypalURL = '';

    private $error = false;

    /**
     * @var Nette\ArrayHash
     */
    private $errors;

	
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
	public function doExpressCheckout($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL) { 
//string(230) "&PAYMENTREQUEST_0_AMT=12&PAYMENTREQUEST_0_PAYMENTACTION=Order&RETURNURL=http://localhost/Projects/nette-adons/www/pay-pal/process&CANCELURL=http://localhost/Projects/nette-adons/www/pay-pal/cancel&PAYMENTREQUEST_0_CURRENCYCODE=CZK" 
        $data = array('PAYMENTREQUEST_0_AMT' => $paymentAmount,
                      'PAYMENTREQUEST_0_PAYMENTACTION' => $paymentType,
                      'RETURNURL' => $returnURL,
                      'CANCELURL' => $cancelURL,
                      'PAYMENTREQUEST_0_CURRENCYCODE' => $currencyCodeType,
                     );
		
        // Remember session
//! todo je nutne si toto pamatovat? nestaci jen token?
		//$session->currencyCodeType = $currencyCodeType;	  
		//$session->PaymentType = $paymentType;

        $query = http_build_query($data);

	    $resArray = $this->hash_call('SetExpressCheckout', $query);
        if (!count($resArray))
            return false;

		$ack = strtoupper($resArray['ACK']);
		if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {
			$token = urldecode($resArray['TOKEN']);
			$this->token = $token;
		}
		   
	    return $resArray;
	}


    /**
     * Prepares the parameters for the SetExpressCheckout API Call.
	 */
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

	    $resArray = $this->hash_call('SetExpressCheckout', $nvpstr);
		$ack = strtoupper($resArray['ACK']);

		if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {

			$token = urldecode($resArray['TOKEN']);
			$_SESSION['TOKEN'] = $token;
		}
		   
	    return $resArray;
	}
	

	public function getShippingDetails($token) {

	    $nvpstr='&TOKEN=' . $token;

	    $resArray = $this->hash_call('GetExpressCheckoutDetails', $nvpstr);
	    $ack = strtoupper($resArray['ACK']);
		if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING')
			$_SESSION['payer_id'] =	$resArray['PAYERID'];

		return $resArray;
	}

	
    /**
     * Prepares the parameters for the GetExpressCheckoutDetails API Call.
	 */
	public function confirmPayment($FinalPaymentAmt) {

		// Format the other parameters that were stored in the session from the previous calls	
		$token 				= urlencode($_SESSION['TOKEN']);
		$paymentType 		= urlencode($_SESSION['PaymentType']);
		$currencyCodeType 	= urlencode($_SESSION['currencyCodeType']);
		$payerID 			= urlencode($_SESSION['payer_id']);

		$serverName 		= urlencode($_SERVER['SERVER_NAME']);

		$nvpstr  = '&TOKEN=' . $token . '&PAYERID=' . $payerID . '&PAYMENTREQUEST_0_PAYMENTACTION=' . $paymentType . '&PAYMENTREQUEST_0_AMT=' . $FinalPaymentAmt;
		$nvpstr .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . $currencyCodeType . '&IPADDRESS=' . $serverName; 

		 /* Make the call to PayPal to finalize payment
		    If an error occured, show the resulting errors
		    */
		$resArray = $this->hash_call('DoExpressCheckoutPayment', $nvpstr);

		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */
		$ack = strtoupper($resArray['ACK']);

		return $resArray;
	}
	
	/**
     * This function makes a DoDirectPayment API call.
	 */
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

		$resArray = $this->hash_call('DoDirectPayment', $nvpstr);

		return $resArray;
	}


	/**
      * hash_call: Function to perform the API call to PayPal using API signature
      * @methodName is name of API  method.
      * @nvpStr is nvp string.
      * returns an associtive array containing the response from the server.
	*/
	private function hash_call($methodName, $nvpStr) {

		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->endPoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		
	    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
	   //Set proxy name to host and port number to port in constants.php 
		if($this->useProxy)
			curl_setopt ($ch, CURLOPT_PROXY, $host. ':' . $port); 

		//NVPRequest for submitting to server
		$nvpreq = 'METHOD=' . urlencode($methodName) . '&VERSION=' . urlencode(self::VERSION) . '&PWD=' . urlencode($this->password) . '&USER=' . urlencode($this->username) . '&SIGNATURE=' . urlencode($this->signature) . '&'. $nvpStr . '&BUTTONSOURCE=' . urlencode($this->sbnCode);

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		//getting response from server
		$response = curl_exec($ch);

		//convrting NVPResponse to an Associative Array
		$nvpResArray = $this->deformatNVP($response);
		$nvpReqArray = $this->deformatNVP($nvpreq);
		$_SESSION['nvpReqArray'] = $nvpReqArray;

		if (curl_errno($ch)) {

			// moving to display page to display curl errors
			  $_SESSION['curl_error_no'] = curl_errno($ch) ;
			  $_SESSION['curl_error_msg'] = curl_error($ch);

			  //Execute the Error handling module to display errors. 
		} else 
		  	curl_close($ch);

		return $nvpResArray;
	}


	public function getURL() {

		return $this->paypalURL . $this->token;
	}

	
	/*'----------------------------------------------------------------------------------
	 * This function will take NVPString and convert it to an Associative Array and it will decode the response.
	  * It is usefull to search for a particular key and displaying arrays.
	  * @nvpstr is NVPString.
	  * @nvpArray is Associative Array.
	   ----------------------------------------------------------------------------------
	  */
	private function deformatNVP($nvpstr) {

		$intial = 0;
	 	$nvpArray = array();

		while(strlen($nvpstr)) {

			//postion of Key
			$keypos= strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	    }

		return $nvpArray;
	}


    public function setSignature($signature) {

        $this->signature = $signature;
        return $this;
    }


    public function isError() {

        return $this->error;
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


    public function setPort($port) {

        $this->port = (int)$port;
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

        $this->sandbox = true;
        $this->setPaypalCommunication();

        return $this;
    }

}
