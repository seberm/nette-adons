<?php
/**
 * @class PayPal\PayPalButton (Nette 2.0 Component)
 * @author Otto Sabart <seberm[at]gmail[dot]com> (www.seberm.com)
 */

namespace PayPal;

use Nette,
    Nette\Application\UI\Form;

class PayPalButton extends Nette\Application\UI\Control {
    
    /**
     * PayPal's image source
     */
    const PAYPAL_IMAGE = 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif';


    public $currencyCode = API::CURRENCY_CROUND;
    public $paymentType = 'Order';
    public $shipping = 0;
    public $tax = 0;


    /**
     * @var PayPal\API
     */
    public $api = NULL;

    /**
     * @var Nette\Localization\ITranslator
     */
    private $translator = NULL;

    public $onConfirmation;
    public $onCancel;
    public $onError;


    public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

        parent::__construct($parent, $name);

        $this->api = new API;
    }


    public function setTranslator(Nette\Localization\ITranslator $translator) {

        $this->translator = $translator;
    }


    final public function getTranslator() {

        return $this->translator;
    }


    public function render() {

        $this->template->setFile(__DIR__ . '/default.latte')
                       ->render();
    }


    public function setCredentials(array $params) {

        $this->api->setData($params);

        return $this;
    }


    public function setSandBox($stat = true) {

        $this->api->setSandbox($stat);
        return $this;
    }


    public function createComponentPaypalForm() {

        $form = new Form;

        if ($this->translator)
            $form->setTranslator($this->translator);

        $form->addImage('paypalCheckOut', self::PAYPAL_IMAGE, 'Check out with PayPal');

        $form->onSuccess[] = callback($this, 'initPayment');

        return $form;
    }


    public function initPayment(Form $paypalForm) {

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

        $this->redirectToPaypal();
    }


    // Gets shipping information and wait for payment confirmation
    public function handleConfirmation() {

        $data = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

        $component = $this->getComponent('paypalForm');
        if ($this->api->error) {

            foreach ($this->api->errors as $error)
               $component->addError($error); 

            return;
        }

        // Callback
        $this->onConfirmation($data);
    }


    public function handleCancel() {

        $data = $this->api->getShippingDetails($this->presenter->session->getSection('paypal'));

        $component = $this->getComponent('paypalForm');
        if ($this->api->error) {

            foreach ($this->api->errors as $error)
               $component->addError($error); 

            return;
        }

        // Callback
        $this->onCancel($data);
    }


    private function redirectToPaypal() {

        $url = $this->api->url;
        $this->presenter->redirectUrl($url);
    }


    public function loadState(array $params) {

        parent::loadState($params);
    }


    private function buildUrl($signal) {

        $url = $this->presenter->link($this->name . ":${signal}!");

        // Some better way to do it in Nette?
        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']. $url;
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


    public function setCurrencyCode($currencyCode) {

        $this->currencyCode = $currencyCode;
        return $this;
    }


    public function setPaymentType($type) {

        $this->paymentType = $type;
        return $this;
    }


    public function addItemToCart($name, $description, $price, $quantity = 1) {

        $this->api->addItem($name, $description, $price, $quantity);
    }

};
