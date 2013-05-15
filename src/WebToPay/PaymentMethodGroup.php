<?php

/**
 * Wrapper class to group payment methods. Each country can have several payment method groups, each of them
 * have one or more payment methods.
 */
class WebToPay_PaymentMethodGroup {
    /**
     * Some unique (in the scope of country) key for this group
     *
     * @var string
     */
    protected $groupKey;

    /**
     * Translations array for this group. Holds associative array of group title by country codes.
     *
     * @var array
     */
    protected $translations;

    /**
     * Holds actual payment methods
     *
     * @var WebToPay_PaymentMethod[]
     */
    protected $paymentMethods;

    /**
     * Default language for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Constructs object
     *
     * @param string $groupKey
     * @param array  $translations
     * @param string $defaultLanguage
     */
    public function __construct($groupKey, array $translations = array(), $defaultLanguage = 'lt') {
        $this->groupKey = $groupKey;
        $this->translations = $translations;
        $this->defaultLanguage = $defaultLanguage;
        $this->paymentMethods = array();
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return WebToPay_PaymentMethodGroup
     */
    public function setDefaultLanguage($language) {
        $this->defaultLanguage = $language;
        foreach ($this->paymentMethods as $paymentMethod) {
            $paymentMethod->setDefaultLanguage($language);
        }
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
     * Gets title of the group. Tries to get title in specified language. If it is not found or if language is not
     * specified, uses default language, given to constructor.
     *
     * @param string [Optional] $languageCode
     *
     * @return string
     */
    public function getTitle($languageCode = null) {
        if ($languageCode !== null && isset($this->translations[$languageCode])) {
            return $this->translations[$languageCode];
        } elseif (isset($this->translations[$this->defaultLanguage])) {
            return $this->translations[$this->defaultLanguage];
        } else {
            return $this->groupKey;
        }
    }

    /**
     * Returns group key
     *
     * @return string
     */
    public function getKey() {
        return $this->groupKey;
    }

    /**
     * Returns available payment methods for this group
     *
     * @return WebToPay_PaymentMethod[]
     */
    public function getPaymentMethods() {
        return $this->paymentMethods;
    }


    /**
     * Adds new payment method for this group.
     * If some other payment method with specified key was registered earlier, overwrites it.
     * Returns given payment method
     *
     * @param WebToPay_PaymentMethod $paymentMethod
     *
     * @return WebToPay_PaymentMethod
     */
    public function addPaymentMethod(WebToPay_PaymentMethod $paymentMethod) {
        return $this->paymentMethods[$paymentMethod->getKey()] = $paymentMethod;
    }

    /**
     * Gets payment method object with key. If no payment method with such key is found, returns null.
     *
     * @param string $key
     *
     * @return null|WebToPay_PaymentMethod
     */
    public function getPaymentMethod($key) {
        return isset($this->paymentMethods[$key]) ? $this->paymentMethods[$key] : null;
    }

    /**
     * Returns new group instance with only those payment methods, which are available for provided amount.
     *
     * @param integer $amount
     * @param string  $currency
     *
     * @return WebToPay_PaymentMethodGroup
     */
    public function filterForAmount($amount, $currency) {
        $group = new WebToPay_PaymentMethodGroup($this->groupKey, $this->translations, $this->defaultLanguage);
        foreach ($this->getPaymentMethods() as $paymentMethod) {
            if ($paymentMethod->isAvailableForAmount($amount, $currency)) {
                $group->addPaymentMethod($paymentMethod);
            }
        }
        return $group;
    }

    /**
     * Returns new country instance with only those payment methods, which are returns or not iban number after payment
     *
     * @param boolean $isIban
     *
     * @return WebToPay_PaymentMethodGroup
     */
    public function filterForIban($isIban = true) {
        $group = new WebToPay_PaymentMethodGroup($this->groupKey, $this->translations, $this->defaultLanguage);
        foreach ($this->getPaymentMethods() as $paymentMethod) {
            if ($paymentMethod->isIban() == $isIban) {
                $group->addPaymentMethod($paymentMethod);
            }
        }
        return $group;
    }

    /**
     * Returns whether this group has no payment methods
     *
     * @return boolean
     */
    public function isEmpty() {
        return count($this->paymentMethods) === 0;
    }

    /**
     * Loads payment methods from given XML node
     *
     * @param SimpleXMLElement $groupNode
     */
    public function fromXmlNode($groupNode) {
        foreach ($groupNode->payment_type as $paymentTypeNode) {
            $key = (string) $paymentTypeNode->attributes()->key;
            $titleTranslations = array();
            foreach ($paymentTypeNode->title as $titleNode) {
                $titleTranslations[(string) $titleNode->attributes()->language] = (string) $titleNode;
            }
            $logoTranslations = array();
            foreach ($paymentTypeNode->logo_url as $logoNode) {
                if ((string) $logoNode !== '') {
                    $logoTranslations[(string) $logoNode->attributes()->language] = (string) $logoNode;
                }
            }
            $minAmount = null;
            $maxAmount = null;
            $currency = null;
            $isIban = false;
            $baseCurrency = null;
            if (isset($paymentTypeNode->min)) {
                $minAmount = (int) $paymentTypeNode->min->attributes()->amount;
                $currency = (string) $paymentTypeNode->min->attributes()->currency;
            }
            if (isset($paymentTypeNode->max)) {
                $maxAmount = (int) $paymentTypeNode->max->attributes()->amount;
                $currency = (string) $paymentTypeNode->max->attributes()->currency;
            }

            if (isset($paymentTypeNode->is_iban)) {
                $isIban = (int) $paymentTypeNode->is_iban;
            }
            if (isset($paymentTypeNode->base_currency)) {
                $baseCurrency = (string) $paymentTypeNode->base_currency;
            }
            $this->addPaymentMethod($this->createPaymentMethod(
                $key, $minAmount, $maxAmount, $currency, $logoTranslations, $titleTranslations, $isIban, $baseCurrency
            ));
        }
    }

    /**
     * Method to create new payment method instances. Overwrite if you have to use some other subclass.
     *
     * @param string $key
     * @param integer $minAmount
     * @param integer $maxAmount
     * @param string $currency
     * @param array $logoList
     * @param array $titleTranslations
     * @param bool $isIban
     * @param null $baseCurrency
     *
     * @return WebToPay_PaymentMethod
     */
    protected function createPaymentMethod(
        $key, $minAmount, $maxAmount, $currency, array $logoList = array(), array $titleTranslations = array(),
        $isIban = false, $baseCurrency = null
    ) {
        return new WebToPay_PaymentMethod(
            $key, $minAmount, $maxAmount, $currency, $logoList, $titleTranslations, $this->defaultLanguage,
            $isIban, $baseCurrency
        );
    }
}