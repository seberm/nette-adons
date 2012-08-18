<?php

use PayPal\API\API;

use Nette\Application\UI\Form,
	Nette\Diagnostics\Debugger,
	Nette\Security\AuthenticationException;


final class OrderPresenter extends BasePresenter
{

	private $orderButton = NULL;


	protected function startup()
	{
		parent::startup();
		$this->orderButton = $this->context->createButtonOrder();

        $this->orderButton->setSessionSection($this->context->session->getSection('paypal'));

		// Called after successful confirmation
		$this->orderButton->onSuccessPayment[] = callback($this, 'processPayment');
	}



	public function createComponentPaypalButton()
	{
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

		// Called If payment inicialization success
		$button->onConfirmation[] = callback($this, 'confirmOrder');

		// Called if user cancel order
		$button->onCancel[] = callback($this, 'cancelOrder');

		// Called if some error occure (for example error in communication)
		$button->onError[] = callback($this, 'errorOccurred');

		return $button;
	}



	public function processPayment($data)
	{
		/** HERE **
		 * ========
		 * Here you can save details about user to the database ...
		 *
		 * For example:
		 * $dbData = array(
		 *                  'payerID => $data->payerID,
		 *                  'firstName => $data->firstName,
		 *                  'lastName => $data->lastName,
		 *                  'email' => $data->email,
		 *                );
		 *
		 * $user = $this->context->model->createUser($dbData);
		 * if (!$user)
		 *      $this->flashMessage('Err ...');
		 *
		 * ...
		 * ..
		 * .
		 *
		 * And send him email with information about order:
		 * $temp = $this->createTemplate();
		 * $temp->setFile('...../emailBody.phtml')
		 *      ->registerFilter(new Nette\Latte\Engine);
		 *
		 * $temp->details = $data;
		 *
		 * $msg = new Message;
		 * $msg->setHtmlBody($temp);
		 *
		 * $msg->from = 'Robot <robot@foo.tld>';
		 * $msg->subject = 'New order';
		 * $msg->send;
		 * ...
		 * ..
		 * .
		 *
		 * $this->flashMessage('Transaction was successful.');
		 * $this->redirect('Somewhere:');
		 */

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

		dump($data);
		exit;

		/** Gets data:
		 * ===========================
        Nette\ArrayHash(83) {
           token => "EC-7KK176951G0142148" (20)
           checkoutStatus => "PaymentActionNotInitiated" (25)
           timestamp => "2012-08-18T17:36:59Z" (20)
           correlationID => "d7fb1414a95" (11)
           ack => "Success" (7)
           version => "72.0" (4)
           build => "3516191" (7)
           email => "seberm_1332081517_per@gmail.com" (31)
           payerID => "NFQ4ZGK82FNXS" (13)
           payerStatus => "verified" (8)
           firstName => "Otto" (4)
           lastName => "Sabart" (6)
           countryCode => "CA" (2)
           shipToName => "Otto Sabart" (11)
           shipToStreet => "1 Maire-Victorin" (16)
           shipToCity => "Toronto" (7)
           shipToState => "Ontario" (7)
           shipToZip => "M5A 1E1" (7)
           shipToCountryCode => "CA" (2)
           shipToCountryName => "Canada" (6)
           addressStatus => "Confirmed" (9)
           currencyCode => "CZK" (3)
           amount => "30.50" (5)
           itemamt => "23.10" (5)
           shippingAmount => "4.30" (4)
           handlingAmount => "0.00" (4)
           taxAmount => "3.10" (4)
           insuranceAmount => "0.00" (4)
           shipDiscauntAmount => "0.00" (4)
           l_name0 => "Item1" (5)
           l_name1 => "Item2" (5)
           l_qty0 => "1"
           l_qty1 => "1"
           l_taxamt0 => "0.00" (4)
           l_taxamt1 => "0.00" (4)
           l_amt0 => "12.30" (5)
           l_amt1 => "10.80" (5)
           l_desc0 => "This is item one!" (17)
           l_desc1 => "Item 2 - Yeah ..." (17)
           l_itemweightvalue0 => "   0.00000" (10)
           l_itemweightvalue1 => "   0.00000" (10)
           l_itemlengthvalue0 => "   0.00000" (10)
           l_itemlengthvalue1 => "   0.00000" (10)
           l_itemwidthvalue0 => "   0.00000" (10)
           l_itemwidthvalue1 => "   0.00000" (10)
           l_itemheightvalue0 => "   0.00000" (10)
           l_itemheightvalue1 => "   0.00000" (10)
           paymentrequest_0_currencycode => "CZK" (3)
           paymentrequest_0_amt => "30.50" (5)
           paymentrequest_0_itemamt => "23.10" (5)
           paymentrequest_0_shippingamt => "4.30" (4)
           paymentrequest_0_handlingamt => "0.00" (4)
           paymentrequest_0_taxamt => "3.10" (4)
           paymentrequest_0_insuranceamt => "0.00" (4)
           paymentrequest_0_shipdiscamt => "0.00" (4)
           paymentrequest_0_insuranceoptionoffered => "false" (5)
           paymentrequest_0_shiptoname => "Otto Sabart" (11)
           paymentrequest_0_shiptostreet => "1 Maire-Victorin" (16)
           paymentrequest_0_shiptocity => "Toronto" (7)
           paymentrequest_0_shiptostate => "Ontario" (7)
           paymentrequest_0_shiptozip => "M5A 1E1" (7)
           paymentrequest_0_shiptocountrycode => "CA" (2)
           paymentrequest_0_shiptocountryname => "Canada" (6)
           paymentrequest_0_addressstatus => "Confirmed" (9)
           l_paymentrequest_0_name0 => "Item1" (5)
           l_paymentrequest_0_name1 => "Item2" (5)
           l_paymentrequest_0_qty0 => "1"
           l_paymentrequest_0_qty1 => "1"
           l_paymentrequest_0_taxamt0 => "0.00" (4)
           l_paymentrequest_0_taxamt1 => "0.00" (4)
           l_paymentrequest_0_amt0 => "12.30" (5)
           l_paymentrequest_0_amt1 => "10.80" (5)
           l_paymentrequest_0_desc0 => "This is item one!" (17)
           l_paymentrequest_0_desc1 => "Item 2 - Yeah ..." (17)
           l_paymentrequest_0_itemweightvalue0 => "   0.00000" (10)
           l_paymentrequest_0_itemweightvalue1 => "   0.00000" (10)
           l_paymentrequest_0_itemlengthvalue0 => "   0.00000" (10)
           l_paymentrequest_0_itemlengthvalue1 => "   0.00000" (10)
           l_paymentrequest_0_itemwidthvalue0 => "   0.00000" (10)
           l_paymentrequest_0_itemwidthvalue1 => "   0.00000" (10)
           l_paymentrequest_0_itemheightvalue0 => "   0.00000" (10)
           l_paymentrequest_0_itemheightvalue1 => "   0.00000" (10)
           paymentrequestinfo_0_errorcode => "0"
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



	public function confirmOrder($data)
	{
		//unused($data);
		$this->redirect('confirm');
	}



	public function renderConfirm()
	{
		$response = $this->orderButton->getShippingDetails();

		if ($response->error) {
			foreach ($response->errors as $error) {
				$this->flashMessage($error, 'warning');
			}

			$this->redirect('Order:');
		}

		$this->template->data = $response->responseData;
		$this->template->cartItems = $response->cartItems;

		Debugger::firelog($response);
	}



	public function createComponentConfirmButton()
	{
		$form = new Form;

		if ($this->context->hasService('translator')) {
			$form->setTranslator($this->context->translator);
		}

		$form->addProtection('It\'s neccessary to resend this form. Security token expired.');

		$form->addSubmit('confirm', 'Confirm payment');
		$form->onSuccess[] = callback($this, 'confirmPaymentFormSubmitted');

		return $form;
	}



	public function confirmPaymentFormSubmitted($form)
	{
		// Called if some error occure (for example error in communication)
		$this->orderButton->onError[] = callback($this, 'errorOccurred');

		try {
			$this->orderButton->confirmExpressCheckout();
			//$this->redirect('Order:');

		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}



	public function createComponentCancelButton()
	{
		$form = new Form;

		$form->addSubmit('cancel', 'Cancel payment');
		$form->onSuccess[] = callback($this, 'cancelFormSubmitted');

		return $form;
	}



	public function cancelFormSubmitted($form)
	{
		$this->flashMessage('Payment was canceled', 'warning');
		$this->redirect(':' . $this->name . ':');
	}

}
