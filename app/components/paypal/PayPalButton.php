<?php
/**
 * @class PayPal\PayPalButton (Nette 2.0 Component)
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
	const PAYPAL_IMAGE = 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif';

	public $currencyCode = 'CZK';

	public $paymentType = 'Order';

	public $amount;

	/**
	 * @var API
	 */
	private $paypal = NULL;

	/**
	 * @var Nette\Localization\ITranslator
	 */
	private $translator = NULL;

	public $onSuccess;

	public $onCancel;

	public $onError;


	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->paypal = new API;
	}


	public function setTranslator(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}


	final public function getTranslator()
	{
		return $this->translator;
	}


	public function render()
	{
		$this->template->setFile(__DIR__ . '/default.latte')
			->render();
	}


	public function setCredentials(array $params)
	{
		$this->paypal->setData($params);
		return $this;
	}


	public function setSandBox($stat = TRUE)
	{
		$this->paypal->setSandbox($stat);
		return $this;
	}


	public function createComponentPaypalForm()
	{
		$form = new Form;

		if ($this->translator) {
			$form->setTranslator($this->translator);
		}

		$form->addImage('paypalCheckOut', self::PAYPAL_IMAGE, 'Check out with PayPal');

		$form->onSuccess[] = callback($this, 'initPayment');

		return $form;
	}


	public function initPayment(Form $paypalForm)
	{
		$this->paypal->doExpressCheckout($this->amount,
			$this->currencyCode,
			$this->paymentType,
			$this->buildUrl('process'),
			$this->buildUrl('cancel'),
			$this->presenter->session->getSection('paypal'));

		if ($this->paypal->isError()) {
			$this->onError($this->paypal->errors);
			return;
		}

		$this->redirectToPaypal();
	}


	public function handleProcess()
	{
		$data = $this->paypal->getShippingDetails($this->presenter->session->getSection('paypal'));

		$component = $this->getComponent('paypalForm');
		if ($this->paypal->isError()) {
			foreach ($this->paypal->errors as $error) {
				$component->addError($error);
			}
			return;
		}

		// Callback
		$this->onSuccess($data);
	}


	public function handleCancel()
	{
		$data = $this->paypal->getShippingDetails($this->presenter->session->getSection('paypal'));

		$component = $this->getComponent('paypalForm');
		if ($this->paypal->isError()) {
			foreach ($this->paypal->errors as $error) {
				$component->addError($error);
			}
			return;
		}

		// Callback
		$this->onCancel($data);
	}


	private function redirectToPaypal()
	{
		$url = $this->paypal->url;
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
}

