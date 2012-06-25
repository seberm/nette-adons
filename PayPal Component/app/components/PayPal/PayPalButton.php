<?php
/**
 * @class PayPalButton (Nette 2.0 Component)
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use Nette,
    Nette\Application\UI\Form;

abstract class PayPalButton extends Nette\Application\UI\Control
{

	/**
	 * PayPal's image source
	 */
	const PAYPAL_IMAGE = 'https://www.paypalobjects.com/en_US/i/btn/btn_xpressCheckout.gif';

    public $currencyCode = API::CURRENCY_EURO;
	public $paymentType;
	public $amount;

	/**
	 * @var API
	 */
	protected $api = NULL;

	/**
	 * @var Nette\Localization\ITranslator
	 */
	protected $translator = NULL;

    // Handlers
	public $onSuccessPayment;
	public $onCancel;
	public $onError;


	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->api = new API;
	}


	public function setTranslator(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}


	final public function getTranslator()
	{
		return $this->translator;
	}


    public function getErrors() {

        return $this->api->errors;
    }


	public function renderBuy()
	{
		$this->template->setFile(__DIR__ . '/buy.latte')
			->render();
	}


	public function setCredentials(array $params)
	{
		$this->api->setData($params);
		return $this;
	}


	public function setSandBox($stat = TRUE)
	{
		$this->api->setSandbox($stat);
		return $this;
	}


	abstract protected function createComponentPaypalBuyForm();
        /*
	{
		$form = new Form;

		if ($this->translator) {
			$form->setTranslator($this->translator);
		}

		$form->addImage('paypalCheckOut', self::PAYPAL_IMAGE, 'Check out with PayPal');

		$form->onSuccess[] = callback($this, 'initPayment');

		return $form;
	}
         */


        /*
	public function initPayment(Form $paypalBuyForm)
	{
		$this->api->doExpressCheckout($this->amount,
			$this->currencyCode,
			$this->paymentType,
			$this->buildUrl('processBuy'),
			$this->buildUrl('cancel'),
			$this->presenter->session->getSection('paypal'));

		if ($this->api->isError()) {
			$this->onError($this->api->errors);
			return;
		}

		$this->redirectToPaypal();
	}
        $this->api->setExpressCheckout(   $this->shipping,
                                          $this->tax,
                                          $this->currencyCode,
                                          $this->paymentType,
                                          $this->buildUrl('confirmation'),
                                          $this->buildUrl('cancel'),
                                          $this->presenter->session->getSection('paypal'));

        if ($this->api->isError()) {

            $this->onError($this->api->errors);
            return;
        }
     */


    /*
	public function handleProcessBuy()
	{
		$data = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

		$component = $this->getComponent('paypalBuyForm');
		if ($this->api->isError()) {
			foreach ($this->api->errors as $error) {
				$component->addError($error);
			}
			return;
		}

		// Callback
		$this->onSuccessBuy($data);
	}
     */


    public function getShippingDetails(Nette\Http\SessionSection $section) {

        return $this->api->getShippingDetails($section);
    }


	protected function redirectToPaypal()
	{
		$url = $this->api->url;
		$this->presenter->redirectUrl($url);
	}


	public function loadState(array $params)
	{
		parent::loadState($params);
	}


	protected function buildUrl($signal)
	{
		$url = $this->presenter->link($this->name . ":${signal}!");

		// Some better way to do it in Nette?
		return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $url;
	}


	public function setAmount($amount)
	{
		$this->amount = $amount;
		return $this;
	}


	public function setCurrencyCode($currency)
	{
		$this->currencyCode = $currency;
		return $this;
	}


	public function setPaymentType($type)
	{
		$this->paymentType = $type;
		return $this;
	}

    










    /**
     * Finds out if all keys in $keys are included in $arr.
     *
     * @param $arr Source array
     * @param $keys Array of keys
     * @return boolean
     */
    public static function array_keys_exist($arr, $keys) {

        /*
        foreach ($keys as $key)
            if (!array_key_exists($key, $arr))
                return $key;

        return true;
        */

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
    public static function array_keys_by_ereg($arr, $pattern) {

        $subArray = array();

        $matches = preg_grep($pattern, array_keys($arr));
        foreach ($matches as $match)
            $subArray[$match] = $arr[$match];

        return $subArray;
    }


    // Pocet polozek (hodnot), ktere ma jedna PayPal polozka
    const ITEM_VALUES_COUNT = 9;
            // We have to check if all these keys exist in array 
    public static $ITEM_KEYS = array(
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
     * Returns PayPal's cart items in Nette\ArrayHash.
     *
     * @param $data Data from PayPal response
     * @return Nette\ArrayHash
     */
    public static function parsePayPalItems($data) {

        $itemsData = self::array_keys_by_ereg($data, '/^L_[A-Z]+[0-9]+/');

        $items = array();
        $itemsCount = count($itemsData) / count(self::$ITEM_KEYS);

        assert(is_int($itemsCount));


        for ($i = 0; $i < $itemsCount; ++$i) {

            $keys = array();
            foreach (self::$ITEM_KEYS as $key)
                $keys[] = $key . $i;

            if (self::array_keys_exist($itemsData, $keys)) {

                $items[] = array(
                    'name'          => $itemsData['L_NAME'.$i],
                    'quantity'      => $itemsData['L_QTY'.$i],
                    'taxAmount'     => $itemsData['L_TAXAMT'.$i],
                    'amount'        => $itemsData['L_AMT'.$i],
                    'description'   => $itemsData['L_DESC'.$i],
                    'weightValue'   => $itemsData['L_ITEMWEIGHTVALUE'.$i],
                    'lengthValue'   => $itemsData['L_ITEMLENGTHVALUE'.$i],
                    'widthValue'    => $itemsData['L_ITEMWIDTHVALUE'.$i],
                    'heightValue'   => $itemsData['L_ITEMHEIGHTVALUE'.$i],
                );
            }
        }

        return \Nette\ArrayHash::from($items);
    }

};
