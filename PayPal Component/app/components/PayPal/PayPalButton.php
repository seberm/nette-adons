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

    
















    // Pocet polozek (hodnot), ktere ma jedna PayPal polozka
    const ITEM_VALUES_COUNT = 9;
    public static function parsePayPalItems() {
        $testData = array (
                           'L_NAME0' => "Item1",
                           'L_NAME1' => "Item2",
                           'L_QTY0' => "1",
                           'L_QTY1' => "1",
                           'L_TAXAMT0' => "0.00",
                           'L_TAXAMT1' => "0.00",
                           'L_AMT0' => "12.30",
                           'L_AMT1' => "10.80",
                           'L_DESC0' => "This is item one!",
                           'L_DESC1' => "Item 2 - Yeah ...",
                           'L_ITEMWEIGHTVALUE0' => "   0.00000",
                           'L_ITEMWEIGHTVALUE1' => "   0.00000",
                           'L_ITEMLENGTHVALUE0' => "   0.00000",
                           'L_ITEMLENGTHVALUE1' => "   0.00000",
                           'L_ITEMWIDTHVALUE0' => "   0.00000" ,
                           'L_ITEMWIDTHVALUE1' => "   0.00000" ,
                           'L_ITEMHEIGHTVALUE0' => "   0.00000",
                           'L_ITEMHEIGHTVALUE1' => "   0.00000",
                        );

        $items = array();
        $itemsCount = count($testData % self::ITEM_VALUES_COUNT); // This constant is MAGIC!
        for ($j = 0; $j < $itemsCount; ++$j) {
            for ($i = 0; $i < self::ITEM_VALUES_COUNT; ++$i) {

                if (isset($testData['L_NAME'.$i]) && isset ($testData['L_QTY'.$i])) { // ...a tak dale..musi se otestovat jestli tam jsou vsechny ( multikey array test keys)
                    $items[] = array(
                        'name' => $testData['L_NAME'.$i],
                        'quantity' => $testData['L_QTY'.$i],
                        'taxAmount' => $testData['L_TAXAMT'.$i],
                        'amount' => $testData['L_AMT'.$i],
                        'description' => $testData['L_DESC'.$i],
                        'weightValue' => $testData['L_ITEMWEIGHTVALUE'.$i],
                        'lengthValue' => $testData['L_ITEMLENGTHVALUE'.$i],
                        'widthValue' => $testData['L_ITEMWIDTHVALUE'.$i],
                        'heightValue' => $testData['L_ITEMHEIGHTVALUE'.$i],
                    );

                }
            }
        }

        dump($items);
        exit;
        return \Nette\ArrayHash::from($items);
    }

};
