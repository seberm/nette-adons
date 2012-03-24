<?php

final class PayPalPresenter extends BasePresenter {


    public function createComponentPaypalForm() {

        $form = new PayPal\PayPalForm;

        $credentials = $this->context->params['paypal']['api'];
        $form->setCredentials($credentials)
             ->setSandBox(); // enables paypal sandbox mode (http://developer.paypal.com)

        return $form;
    }
}
