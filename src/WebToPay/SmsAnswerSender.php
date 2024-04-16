<?php

declare(strict_types=1);

/**
 * Sends answer to SMS payment if it was not provided with response to callback
 */
class WebToPay_SmsAnswerSender
{
    protected string $password;

    protected WebToPay_WebClient $webClient;

    protected WebToPay_UrlBuilder $urlBuilder;

    /**
     * Constructs object
     */
    public function __construct(string $password, WebToPay_WebClient $webClient, WebToPay_UrlBuilder $urlBuilder)
    {
        $this->password = $password;
        $this->webClient = $webClient;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Sends answer by sms ID get from callback. Answer can be sent only if it was not provided
     * when responding to the callback
     *
     * @throws WebToPayException
     */
    public function sendAnswer(int $smsId, string $text): void
    {
        $content = $this->webClient->get($this->urlBuilder->buildForSmsAnswer(), [
            'id' => $smsId,
            'msg' => $text,
            'transaction' => md5($this->password . '|' . $smsId),
        ]);

        if (strpos($content, 'OK') !== 0) {
            throw new WebToPayException(
                sprintf('Error: %s', $content),
                WebToPayException::E_SMS_ANSWER
            );
        }
    }
}
