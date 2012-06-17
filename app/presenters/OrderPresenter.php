<?php

use PayPal\PayPalButton,
    PayPal\API;

use Nette\Application\UI\Form,
    Nette\Diagnostics\Debugger,
    Nette\Security\AuthenticationException;


final class OrderPresenter extends BasePresenter {

    private $orderButton = NULL;


    protected function startup() {

        parent::startup();
        $this->orderButton = $this->context->createButtonOrder();
    }


    public function createComponentPaypalButton() {

        $button = $this->orderButton;

        $button->setCurrencyCode(API::CURRENCY_CROUND);

        /** @todo Item quantity */
        // Add items to our cart
        $button->addItemToCart('Item1', 'This is item one!', 12.3, 1); 
        $button->addItemToCart('Item2', 'Item 2 - Yeah ...', 10.8, 1);

        // Default payment type is Order
        //$button->setPaymentType('Order');

        // Is there any shipping?
        $button->shipping = 4.3;

        // It's possible to set tax
        $button->tax = 3.1;

        // If order success, call processOrder function
        $button->onConfirmation[] = callback($this, 'confirmOrder');
        $button->onSuccessBuy[] = callback($this, 'processBuy');
		$button->onSuccessPayment[] = callback($this, 'processPayment');

        $button->onCancel[] = callback($this, 'cancelOrder');
        $button->onError[] = callback($this, 'errorOccurred');

        return $button;
    }


	public function processPayment($data) {

        Debugger::firelog('Processing payment ...');
        Debugger::firelog($data);

		dump($data);
		exit;
	}


    public function errorOccurred($errors) {

        Debugger::firelog('PayPal error occured!');
        Debugger::firelog($errors);

        // It's possible to show errors this way:
        /*
        foreach ($errors as $err)
            $this->orderButton->addError($err);
        */

        dump($errors);
        exit(1);
    }


    public function processBuy($data) {

        Debugger::firelog('Processing buy ...');
        Debugger::firelog($data);

		$this->redirect('pay');


        /** Gets data:
         * ===========================
            Nette\ArrayHash(44) {
               TOKEN => "EC-9MN91989MA8265705" (20)
               CHECKOUTSTATUS => "PaymentActionNotInitiated" (25)
               TIMESTAMP => "2012-06-16T22:52:57Z" (20)
               CORRELATIONID => "727c2305e5c75" (13)
               ACK => "Success" (7)
               VERSION => "72.0" (4)
               BUILD => "3067390" (7)
               EMAIL => "seberm_1332081517_per@gmail.com" (31)
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
               CURRENCYCODE => "EUR" (3)
               AMT => "34.67" (5)
               SHIPPINGAMT => "0.00" (4)
               HANDLINGAMT => "0.00" (4)
               TAXAMT => "0.00" (4)
               INSURANCEAMT => "0.00" (4)
               SHIPDISCAMT => "0.00" (4)
               PAYMENTREQUEST_0_CURRENCYCODE => "EUR" (3)
               PAYMENTREQUEST_0_AMT => "34.67" (5)
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
            }
        */
    }

 
    public function cancelOrder($data) {

        Debugger::firelog('Order was canceled.');
        Debugger::firelog($data);

        dump($data);
        exit;
    }


    public function confirmOrder($data) {
        //unused($data);

        $this->redirect('confirm');
    }


    public function renderConfirm() {

        $data = $this->orderButton->getShippingDetails($this->presenter->session->getSection('paypal'));

        $this->template->details = $data;

        Debugger::firelog($data);
    }


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

            if ($data = $this->orderButton->confirmExpressCheckout($this->presenter->session->getSection('paypal'))) {
                
                $this->flashMessage('Transaction was successful.');
            } else {

                foreach ($this->orderButton->errors as $error)
                    $this->flashMessage($error, 'warning');
            }


        } catch (AuthenticationException $e) {

            $form->addError($e->getMessage());
        }

    }


    public function createComponentCancelButton() {

        $form = new Form;

        $form->addSubmit('cancel', 'Cancel payment');
        $form->onSuccess[] = callback($this, 'cancelFormSubmitted');

        return $form;
    }


    public function cancelFormSubmitted($form) {

        $this->flashMessage('Payment was canceled', 'warning');
        $this->redirect(':'.$this->name.':');
    }
}
