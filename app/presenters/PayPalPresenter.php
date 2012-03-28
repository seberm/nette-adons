<?php

use PayPal\PayPalButton,
    PayPal\API;

final class PayPalPresenter extends BasePresenter {


    public function createComponentPaypalButton() {

        $button = $this->context->createPayPalButton();

        //$button->setPaymentMethod(API::CHECKOUT);

        // If order success, call processOrder function
        $button->onSuccess[] = callback($this, 'processOrder');
        $button->onCancel[] = callback($this, 'cancelOrder');
        $button->onError[] = callback($this, 'errorOccurred');

        return $button;
    }


    public function errorOccurred($errors) {

        dump($errors);
        exit(1);
    }


    public function processOrder($data) {

        dump($data);
        exit;
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

    public function cancelOrder($data) {

        dump($data);
        exit;
    }
}
