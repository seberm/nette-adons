<?php
/**
 * @class PayPalButton (Nette 2.0 Component)
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use Nette,
    Nette\Application\UI\Form;

class PayPalButton extends Nette\Application\UI\Control
{

	/**
	 * PayPal's image source
	 */
	const PAYPAL_IMAGE = 'https://www.paypalobjects.com/en_US/i/btn/btn_xpressCheckout.gif';

	public $payImage = 'https://www.paypalobjects.com/en_US/i/btn/x-click-but3.gif';

    public $currencyCode = API::CURRENCY_EURO;
    //public $shipping = 0.0;
    //public $tax = 0.0;

	public $paymentType = 'Sale'; // keep Sale for instant payment


	public $amount;

	/**
	 * @var API
	 */
	private $api = NULL;

	/**
	 * @var Nette\Localization\ITranslator
	 */
	private $translator = NULL;

    // Handlers
    //public $onConfirmation;
	public $onSuccessBuy;
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


	public function renderBuy()
	{
		$this->template->setFile(__DIR__ . '/buy.latte')
			->render();
	}


	public function renderPay()
	{
		$this->template->setFile(__DIR__ . '/pay.latte')
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


	protected function createComponentPaypalBuyForm()
	{
		$form = new Form;

		if ($this->translator) {
			$form->setTranslator($this->translator);
		}

		$form->addImage('paypalCheckOut', self::PAYPAL_IMAGE, 'Check out with PayPal');

		$form->onSuccess[] = callback($this, 'initPayment');

		return $form;
	}


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
    /*
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


	protected function createComponentPaypalPayForm()
	{
		$form = new Form;

		if ($this->translator) {
			$form->setTranslator($this->translator);
		}

		$form->addImage('paypalPay', $this->payImage, 'Pay with PayPal');

		$form->onSuccess[] = callback($this, 'processPayment');

		return $form;
	}

    /*
    // Gets shipping information and wait for payment confirmation
    public function handleConfirmation() {

        $data = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

        $component = $this->getComponent('paypalForm');
        if ($this->api->error) {

            foreach ($this->api->errors as $error)
               $component->addError($error); 

        // Callback
        $this->onConfirmation($data);


        $data = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

        $component = $this->getComponent('paypalForm');
        if ($this->api->error) {

            foreach ($this->api->errors as $error)
               $component->addError($error); 
    }


    */


	public function processPayment(Form $form)
	{
		$data = $this->api->doPayment(
			$this->paymentType,
			$this->presenter->session->getSection('paypal')
		);


		if ($this->api->isError()) {
			$this->onError($this->api->errors);
			return;
		}

		// Callback
		$this->onSuccessPayment($data);
	}


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


	public function handleCancel()
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
		$this->onCancel($data);
	}


	private function redirectToPaypal()
	{
		$url = $this->api->url;
		$this->presenter->redirectUrl($url);
	}


	public function loadState(array $params)
	{
		parent::loadState($params);
	}


	private function buildUrl($signal)
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


	public function setCurrency($currency)
	{
		$this->currencyCode = $currency;
		return $this;
	}


	public function setPaymentType($type)
	{
		$this->paymentType = $type;
		return $this;
	}

/*
    public function setShipping($shipping) {

        $this->shipping = $shipping;
        return $this;
    }


    public function setTax($tax) {

        $this->tax = $tax;
        return $this;
    }


    public function setInvoiceValue($value) {

        $this->api->invoiceValue = $value;
        return $this;
    }


    public function addItemToCart($name, $description, $price, $quantity = 1) {

        $this->api->addItem($name, $description, $price, $quantity);
    }
 */

};
