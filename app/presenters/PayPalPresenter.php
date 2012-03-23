<?php

final class PayPalPresenter extends BasePresenter {


    public function createComponentPaypalForm() {

        $form = new PayPal\PayPalForm;

        $credentials = $this->context->params['paypal']['api'];
        $form->setCredentials($credentials);

        return $form;
    }
}
