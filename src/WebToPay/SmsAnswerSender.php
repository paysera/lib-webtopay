<?php

/**
 * Sends answer to SMS payment if it was not provided with response to callback
 */
class WebToPay_SmsAnswerSender {

    /**
     * @var string
     */
    protected $password;

    /**
     * @var WebToPay_WebClient
     */
    protected $webClient;

    /**
     * Constructs object
     *
     * @param string             $password
     * @param WebToPay_WebClient $webClient
     */
    public function __construct($password, WebToPay_WebClient $webClient) {
        $this->password = $password;
        $this->webClient = $webClient;
    }

    /**
     * Sends answer by sms ID get from callback. Answer can be send only if it was not provided
     * when responding to callback
     *
     * @param integer $smsId
     * @param string  $text
     *
     * @throws WebToPayException
     */
    public function sendAnswer($smsId, $text) {
        $content = $this->webClient->get(WebToPay::SMS_ANSWER_URL, array(
            'id' => $smsId,
            'msg' => $text,
            'transaction' => md5($this->password . '|' . $smsId),
        ));
        if (strpos($content, 'OK') !== 0) {
            throw new WebToPayException(
                sprintf('Error: %s', $content),
                WebToPayException::E_SMS_ANSWER
            );
        }
    }
}
