<?php
/**
 * @class PayPalButton (Nette 2.0 Component)
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use Nette,
    Nette\Application\UI\Form;

class ButtonOrder extends PayPalButton
{

    public $shipping = 0.0;
    public $tax = 0.0;

    // Handlers
    public $onConfirmation;


	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

        $this->paymentType = 'Order';
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

        $response = $this->api->setExpressCheckout($this->shipping,
                                       $this->tax,
                                       $this->currencyCode,
                                       $this->paymentType,
                                       $this->buildUrl('confirmation'),
                                       $this->buildUrl('cancel'),
                                       $this->presenter->session->getSection('paypal'));

		if ($response->error) {
			$this->onError($response->errors);
			return;
		}

		$this->redirectToPaypal();
	}



    // Gets shipping information and wait for payment confirmation
    public function handleConfirmation() {

        $response = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

        if ($response->error) {

            $this->onError($response->errors);
            return;
        }

        // Callback
        $this->onConfirmation($response);
    }


    /*
	public function processPayment(Form $form)
	{
		$data = $this->api->doPayment(
			$this->paymentType,
			$this->presenter->session->getSection('paypal')
		);


		if ($this->api->error) {
			$this->onError($this->api->errors);
			return;
		}

		// Callback
		$this->onSuccessBuy($data);
	}
     */


    public function confirmExpressCheckout(Nette\Http\SessionSection $section) {

        // We have to get data before confirmation!
        // It's because the PayPal token destroyed after payment confirmation
        // (Session section is destroyed)
        $responseDetails = $this->api->getShippingDetails($section);
        if ($responseDetails->error) {

            $this->onError($responseDetails->errors);
            return;
        }

        $responseConfirm = $this->api->confirmExpressCheckout($section);

        if ($responseConfirm->error) {
            $this->onError($responseConfirm->errors);
			return;
        }

        // Callback
        $this->onSuccessPayment($responseDetails->responseData);
    }


    /*
	public function handleProcessBuy()
	{
		$data = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

		if ($this->api->error) {
            $this->onError($this->api->errors);
			return;
		}

		// Callback
		$this->onSuccessBuy($data);
	}
    */


	public function handleCancel()
	{
		$response = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

		if ($response->error) {
            $this->onError($response->errors);
			return;
		}

		// Callback
		$this->onCancel($response);
	}


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

};
