<?php

use PayPal,
	PayPal\Components\PayPalButton,
	PayPal\API;

use Nette\Diagnostics\Debugger;



final class InstantPaymentPresenter extends BasePresenter
{

	public function createComponentPaypalButton()
	{
		$button = $this->context->createButtonInstant();

		// EURO is default options (see documentation for other currencies)
		//$button->setCurrencyCode(API::CURRENCY_EURO);

		// Default option is 'Sale'
		//$button->setPaymentType('Sale');

		// It's neccessary provide price!
		$button->setAmount(34.67);

		/**
		 * @todo Dodelat tyto moznosti i instantni platby
		 */
		// It's possible to set shipping and tax options
		//$button->addItem(...);
		//$button->shipping = 4.3;
		//$button->tax = 8.32;

		// If order success, call processOrder function
		$button->onSuccessBuy[] = callback($this, 'processBuy');
		$button->onSuccessPayment[] = callback($this, 'processPayment');

		$button->onCancel[] = callback($this, 'cancelOrder');
		$button->onError[] = callback($this, 'errorOccurred');

		return $button;
	}



	public function processPayment($data)
	{
		Debugger::firelog('Processing payment ...');
		Debugger::firelog($data);

		dump($data);
		exit;
	}



	public function errorOccurred($errors)
	{
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



	public function processBuy($data)
	{
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



	public function cancelOrder($data)
	{
		Debugger::firelog('Order was canceled.');
		Debugger::firelog($data);

		dump($data);
		exit;
	}

}
