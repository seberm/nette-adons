<?php

namespace PayPal;

use Nette,
    Nette\Application\UI\Form;

class PayPalForm extends Nette\Application\UI\Control {
    
    const PAYPAL_IMAGE = 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif';
    /**
     * @var PayPal
     */
    private $paypal = NULL;

    /**
     * @var Nette\Localization\ITranslator
     */
    private $translator = NULL;

    /**
     * @var Nette\Http\Session
     */
    private $session;

    /**
     * @var Nette\ArrayHash
     */
    private $credentials;

    public $onSuccess;
    public $onError;


    public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

        parent::__construct($parent, $name);

        $this->paypal = new PayPal;
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

        $this->credentials = Nette\ArrayHash::from($params);

        return $this;
    }


    public function setSandBox($stat = true) {

        $this->paypal->setSandbox($stat);
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

        $amount = 12;
        $currencyCode = 'CZK';
        $paymentType = 'Order';

        $returnUrl = $this->buildUrl('process');
        $cancelUrl = $this->buildUrl('cancel');


        $res = $this->paypal->doExpressCheckout($amount, $currencyCode, $paymentType, $returnUrl, $cancelUrl, $this->presenter->session->getSection('paypal'));

        $this->redirectToPaypal();
    }


    public function handleProcess() {

        $data = $this->paypal->getShippingDetails($this->presenter->session->getSection('paypal'));

        $component = $this->getComponent('paypalForm');
        if ($this->paypal->isError()) {

            foreach ($this->paypal->errors as $error)
               $component->addError($error); 

            return;
        }

        // Callback
        $this->onSuccess($data);
    }


    public function handleCancel() {

        /** @todo
         */

        $this->onError();
    }


    private function redirectToPaypal() {

        $url = $this->paypal->getURL();
        $this->presenter->redirectUrl($url);
    }


    public function loadState(array $params) {

        parent::loadState($params);

        $this->paypal->username = $this->credentials->username;
        $this->paypal->password = $this->credentials->password;
        $this->paypal->signature = $this->credentials->signature;

        //$this->paypal->proxyHost = $this->credentials->proxyHost;
        //$this->paypal->proxyPort = $this->credentials->proxyPort;
    }


    private function buildUrl($signal) {

        $url = $this->presenter->link($this->name . ":${signal}!");

        // Some better way to do it in Nette?
        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']. $url;
    }
};
