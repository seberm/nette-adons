<?php

use PayPal\PayPalButton,
    PayPal\API;

use \Nette\Application\UI\Form,
    \Nette\Security\AuthenticationException;


final class OrderPresenter extends BasePresenter {


    protected function startup() {

        parent::startup();
        $this->button = $this->context->createPayPalButton();
    }


    public function createComponentPaypalButton() {

        $button = $this->button;

        $button->setCurrencyCode(API::CURRENCY_CROUND);

        //$button->addItemToCart('Biofeedback HW', 'toto je super biofeedback hardware', 12, 1);
        //$button->addItemToCart('neco', 'bububu', 10, 1);
        //$button->setPaymentType('Sale');
        //$button->shipping = 4;
        //$button->tax = 3;
        //$button->setPaymentMethod(API::CHECKOUT);

        // If order success, call processOrder function
        $button->onSuccessBuy[] = callback($this, 'processBuy');
		$button->onSuccessPayment[] = callback($this, 'processPayment');

        //$button->onConfirmation[] = callback($this, 'confirmOrder');
        //
        $button->onCancel[] = callback($this, 'cancelOrder');
        $button->onError[] = callback($this, 'errorOccurred');

        return $button;
    }


	public function processPayment($data) {

		dump($data);
		exit;
	}


    public function errorOccurred($errors) {

        dump($errors);
        exit(1);
    }


    public function processBuy($data) {

		$this->redirect('pay');

//        dump($data);
//        exit;
    }

    /**
     * Gets data in PayPal's format:
     *

Nette\ArrayHash(44) {
   TOKEN => "EC-9LL59950JP171724H" (20)
   CHECKOUTSTATUS => "PaymentActionNotInitiated" (25)
   TIMESTAMP => "2012-03-25T00:23:16Z" (20)
   CORRELATIONID => "25fe0bc5e3cc0" (13)
   ACK => "Success" (7)
   VERSION => "66" (2)
   BUILD => "2649250" (7)
   EMAIL => "testing_mail@some.com" (31)
   PAYERID => "NFQ4ZGK82FNXS" (13)
   PAYERSTATUS => "verified" (8)
   FIRSTNAME => "Otto" (4)
   LASTNAME => "Sabart" (6)
   COUNTRYCODE => "CA" (2)
   SHIPTONAME => "Otto Sabart" (11)
   SHIPTOSTREET => "1 Maire-Victorin" (16)
   SHIPTOCITY => "Toronto" (7)
   SHIPTOSTATE => "Ontario" (7)
   SHIPTOZIP => "M5A 1E1" (7)
   SHIPTOCOUNTRYCODE => "CA" (2)
   SHIPTOCOUNTRYNAME => "Canada" (6)
   ADDRESSSTATUS => "Confirmed" (9)
   CURRENCYCODE => "CZK" (3)
   AMT => "12.00" (5)
   SHIPPINGAMT => "0.00" (4)
   HANDLINGAMT => "0.00" (4)
   TAXAMT => "0.00" (4)
   INSURANCEAMT => "0.00" (4)
   SHIPDISCAMT => "0.00" (4)
   PAYMENTREQUEST_0_CURRENCYCODE => "CZK" (3)
   PAYMENTREQUEST_0_AMT => "12.00" (5)
   PAYMENTREQUEST_0_SHIPPINGAMT => "0.00" (4)
   PAYMENTREQUEST_0_HANDLINGAMT => "0.00" (4)
   PAYMENTREQUEST_0_TAXAMT => "0.00" (4)
   PAYMENTREQUEST_0_INSURANCEAMT => "0.00" (4)
   PAYMENTREQUEST_0_SHIPDISCAMT => "0.00" (4)
   PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED => "false" (5)
   PAYMENTREQUEST_0_SHIPTONAME => "Otto Sabart" (11)
   PAYMENTREQUEST_0_SHIPTOSTREET => "1 Maire-Victorin" (16)
   PAYMENTREQUEST_0_SHIPTOCITY => "Toronto" (7)
   PAYMENTREQUEST_0_SHIPTOSTATE => "Ontario" (7)
   PAYMENTREQUEST_0_SHIPTOZIP => "M5A 1E1" (7)
   PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE => "CA" (2)
   PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME => "Canada" (6)
   PAYMENTREQUESTINFO_0_ERRORCODE => "0"
*/
    /*
    public function confirmOrder($data) {
        //unused($data);

        $this->redirect('confirm');
    }


     */
    public function processOrder($data) {

        // Review payment details
        dump($data);
        exit;
    }


    public function cancelOrder($data) {

        dump($data);
        exit;
    }


    /*
    public function renderConfirm() {

        $this->template->details = $this->button->api->getShippingDetails($this->presenter->session->getSection('paypal'));

        
        dump($this->template->details);
    }
     */


    public function createComponentConfirmButton () {

        $form = new Form;
        //$form->setTranslator($this->context->translator);
        $form->addProtection('It\'s neccessary to resend this form. Security token expired.');

        $form->addSubmit('confirm', 'Confirm payment');
        $form->onSuccess[] = callback($this, 'confirmPaymentFormSubmitted');

        return $form;
    }


    public function confirmPaymentFormSubmitted($form) {

        try {

            if ($data = $this->button->api->confirmExpressCheckout($this->presenter->session->getSection('paypal'))) {
                
                $this->flashMessage('Transaction was successful.');
            } else {

                foreach ($this->button->api->errors as $error)
                    $this->flashMessage($error, 'warning');
            }

            $this->redirect('paypal:');

        } catch (AuthenticationException $e) {

            $form->addError($e->getMessage());
        }

    }


    /*
    public function createComponentCancelButton() {

        $form = new Form;
        $form->onSuccess[] = callback($this, 'cancelFormSubmitted');

        return $form;
    }


    public function cancelFormSubmitted($form) {

        $this->redirect(':'.$this->name.':');
    }
     */
}
