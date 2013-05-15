<?php

/**
 * Class to hold information about payment method
 */
class WebToPay_PaymentMethod {
    /**
     * Assigned key for this payment method
     *
     * @var string
     */
    protected $key;

    /**
     * Logo url list by language. Usually logo is same for all languages, but exceptions exist
     *
     * @var array
     */
    protected $logoList;

    /**
     * Title list by language
     *
     * @var array
     */
    protected $titleTranslations;

    /**
     * Default language to use for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * @var boolean
     */
    protected $isIban;

    /**
     * @var string
     */
    protected $baseCurrency;

    /**
     * Constructs object
     *
     * @param string  $key
     * @param integer $minAmount
     * @param integer $maxAmount
     * @param string  $currency
     * @param array   $logoList
     * @param array   $titleTranslations
     * @param string  $defaultLanguage
     * @param bool    $isIban
     * @param string  $baseCurrency
     */
    public function __construct(
        $key, $minAmount, $maxAmount, $currency, array $logoList = array(), array $titleTranslations = array(),
        $defaultLanguage = 'lt', $isIban = false, $baseCurrency = null
    ) {
        $this->key = $key;
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
        $this->currency = $currency;
        $this->logoList = $logoList;
        $this->titleTranslations = $titleTranslations;
        $this->defaultLanguage = $defaultLanguage;
        $this->isIban = $isIban;
        $this->baseCurrency = $baseCurrency;
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return WebToPay_PaymentMethod
     */
    public function setDefaultLanguage($language) {
        $this->defaultLanguage = $language;
        return $this;
    }

    /**
     * Gets default language for titles
     *
     * @return string
     */
    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    /**
     * Get assigned payment method key
     *
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Gets logo url for this payment method. Uses specified language or default one.
     * If logotype is not found for specified language, null is returned.
     *
     * @param string [Optional] $languageCode
     *
     * @return string|null
     */
    public function getLogoUrl($languageCode = null) {
        if ($languageCode !== null && isset($this->logoList[$languageCode])) {
            return $this->logoList[$languageCode];
        } elseif (isset($this->logoList[$this->defaultLanguage])) {
            return $this->logoList[$this->defaultLanguage];
        } else {
            return null;
        }
    }

    /**
     * Gets title for this payment method. Uses specified language or default one.
     *
     * @param string [Optional] $languageCode
     *
     * @return string
     */
    public function getTitle($languageCode = null) {
        if ($languageCode !== null && isset($this->titleTranslations[$languageCode])) {
            return $this->titleTranslations[$languageCode];
        } elseif (isset($this->titleTranslations[$this->defaultLanguage])) {
            return $this->titleTranslations[$this->defaultLanguage];
        } else {
            return $this->key;
        }
    }

    /**
     * Checks if this payment method can be used for specified amount.
     * Throws exception if currency checked is not the one, for which payment method list was downloaded.
     *
     * @param integer $amount
     * @param string  $currency
     *
     * @return boolean
     *
     * @throws WebToPayException
     */
    public function isAvailableForAmount($amount, $currency) {
        if ($this->currency !== $currency) {
            throw new WebToPayException(
                'Currencies does not match. You have to get payment types for the currency you are checking. Given currency: '
                    . $currency . ', available currency: ' . $this->currency
            );
        }
        return (
            ($this->minAmount === null || $amount >= $this->minAmount)
            && ($this->maxAmount === null || $amount <= $this->maxAmount)
        );
    }

    /**
     * Returns min amount for this payment method. If no min amount is specified, returns empty string.
     *
     * @return string
     */
    public function getMinAmountAsString() {
        return $this->minAmount === null ? '' : ($this->minAmount . ' ' . $this->currency);
    }

    /**
     * Returns max amount for this payment method. If no max amount is specified, returns empty string.
     *
     * @return string
     */
    public function getMaxAmountAsString() {
        return $this->maxAmount === null ? '' : ($this->maxAmount . ' ' . $this->currency);
    }

    /**
     * Set if this method returns IBAN number after payment
     *
     * @param boolean $isIban
     */
    public function setIsIban($isIban) {
        $this->isIban = $isIban == 1;
    }

    /**
     * Get if this method returns IBAN number after payment
     *
     * @return bool
     */
    public function isIban() {
        return $this->isIban;
    }

    /**
     * Setter of BaseCurrency
     *
     * @param string $baseCurrency
     */
    public function setBaseCurrency($baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;
    }

    /**
     * Getter of BaseCurrency
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }
}
