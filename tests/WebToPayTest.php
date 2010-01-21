<?php

require_once 'WebToPay.php';

require_once 'PHPUnit/Framework.php';
class WebToPayTest extends PHPUnit_Framework_TestCase {

    public function testFormData() {
        $form_data = WebToPay::getFormData(array(
            ));
    }

    public function testTransactionData() {
        $get = array(
            );
        $result = WebToPay::checkTransactionData($get, array(
            ));
    }

    public function testPaymentTypes() {
        $types = WebToPay::getPaymentTypes();
        foreach ($types as $type) {
            $this->assertEquals(5, sizeof($type));

            list(
                    $country_code, $payment_code,
                    $min_amount, $max_amount, $describtion
                ) = $type;

            if ('' != $country_code) {
                $this->assertEquals(2, strlen($country_code));
            }
            $this->assertTrue(is_int($min_amount));
            $this->assertTrue(is_int($max_amount));
        }
    }


    public function testRequestSpec() {
        $specs = WebToPay::getRequestSpec();
        foreach ($specs as $spec) {
            $this->assertEquals(4, sizeof($spec));

            list(
                    $name, $maxlen, $required, $match
                ) = $spec;

            $this->assertTrue(is_int($maxlen));
            $this->assertTrue(is_bool($required));
        }
    }

}

