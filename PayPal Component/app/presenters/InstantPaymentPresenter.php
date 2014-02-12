<?php

namespace App;

use Nette\Diagnostics\Debugger;



final class InstantPaymentPresenter extends BasePresenter
{

	public function createComponentPaypalButton()
	{
		$button = $this->context->createButtonInstant();

		// EURO is default option (see documentation for other currencies)
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
        Nette\ArrayHash(24) {
           token => "EC-7JS09257KX6095449" (20)
           successpageredirectrequested => "false" (5)
           timestamp => "2012-08-18T17:39:20Z" (20)
           correlationID => "ba6f3091f5c4" (12)
           ack => "Success" (7)
           version => "72.0" (4)
           build => "3516191" (7)
           insuranceoptionselected => "false" (5)
           shippingoptionisdefault => "false" (5)
           paymentinfo_0_transactionid => "2D050568MR560291A" (17)
           transactionType => "expresscheckout" (15)
           paymentType => "instant" (7)
           paymentinfo_0_ordertime => "2012-08-18T17:39:17Z" (20)
           paymentinfo_0_amt => "34.67" (5)
           paymentinfo_0_taxamt => "0.00" (4)
           paymentinfo_0_currencycode => "EUR" (3)
           paymentinfo_0_paymentstatus => "Pending" (7)
           paymentinfo_0_pendingreason => "multicurrency" (13)
           paymentinfo_0_reasoncode => "None" (4)
           paymentinfo_0_protectioneligibility => "Eligible" (8)
           paymentinfo_0_protectioneligibilitytype => "ItemNotReceivedEligible,UnauthorizedPaymentEligible" (51)
           paymentinfo_0_securemerchantaccountid => "3BQUMDNDV8FWW" (13)
           paymentinfo_0_errorcode => "0"
           paymentinfo_0_ack => "Success" (7)
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
