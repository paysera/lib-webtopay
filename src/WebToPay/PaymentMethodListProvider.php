<?php

declare(strict_types=1);

/**
 * Loads data about payment methods and constructs payment method list object from that data
 * You need SimpleXML support to use this feature
 */
class WebToPay_PaymentMethodListProvider
{
    protected int $projectId;

    protected WebToPay_WebClient $webClient;

    /**
     * Holds constructed method lists by currency
     *
     * @var WebToPay_PaymentMethodList[]
     */
    protected array $methodListCache = [];

    /**
     * Builds various request URLs
     */
    protected WebToPay_UrlBuilder $urlBuilder;

    /**
     * Constructs object
     *
     * @throws WebToPayException if SimpleXML is not available
     */
    public function __construct(
        int $projectId,
        WebToPay_WebClient $webClient,
        WebToPay_UrlBuilder $urlBuilder
    ) {
        $this->projectId = $projectId;
        $this->webClient = $webClient;
        $this->urlBuilder = $urlBuilder;

        if (!WebToPay_Functions::function_exists('simplexml_load_string')) {
            throw new WebToPayException('You have to install libxml to use payment methods API');
        }
    }

    /**
     * Gets payment method list for specified currency
     *
     * @throws WebToPayException
     */
    public function getPaymentMethodList(?float $amount, ?string $currency): WebToPay_PaymentMethodList
    {
        if (!isset($this->methodListCache[$currency])) {
            $xmlAsString = $this->webClient->get(
                $this->urlBuilder->buildForPaymentsMethodList($this->projectId, $amount, $currency)
            );
            $useInternalErrors = libxml_use_internal_errors(false);
            $rootNode = simplexml_load_string($xmlAsString);
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
            if (!$rootNode) {
                throw new WebToPayException('Unable to load XML from remote server');
            }
            $methodList = new WebToPay_PaymentMethodList($this->projectId, $currency);
            $methodList->fromXmlNode($rootNode);
            $this->methodListCache[$currency] = $methodList;
        }

        return $this->methodListCache[$currency];
    }
}
