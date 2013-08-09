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
     * @var WebToPay_UrlBuilder $urlBuilder
     */
    protected $urlBuilder;

    /**
     * Constructs object
     *
     * @param string             $password
     * @param WebToPay_WebClient $webClient
     * @param WebToPay_UrlBuilder $urlBuilder
     */
    public function __construct(
        $password,
        WebToPay_WebClient $webClient,
        WebToPay_UrlBuilder $urlBuilder
    ) {
        $this->password = $password;
        $this->webClient = $webClient;
        $this->urlBuilder = $urlBuilder;
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
        $content = $this->webClient->get($this->urlBuilder->buildForSmsAnswer(), array(
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
