<?php

/**
 * Payment method configuration for some country
 */
class WebToPay_PaymentMethodCountry {
    /**
     * @var string
     */
    protected $countryCode;

    /**
     * Holds available payment types for this country
     *
     * @var WebToPay_PaymentMethodGroup[]
     */
    protected $groups;

    /**
     * Default language for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Translations array for this country. Holds associative array of country title by language codes.
     *
     * @var array
     */
    protected $titleTranslations;

    /**
     * Constructs object
     *
     * @param string $countryCode
     * @param array  $titleTranslations
     * @param string $defaultLanguage
     */
    public function __construct($countryCode, $titleTranslations, $defaultLanguage = 'lt') {
        $this->countryCode = $countryCode;
        $this->defaultLanguage = $defaultLanguage;
        $this->titleTranslations = $titleTranslations;
        $this->groups = array();
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return WebToPay_PaymentMethodCountry
     */
    public function setDefaultLanguage($language) {
        $this->defaultLanguage = $language;
        foreach ($this->groups as $group) {
            $group->setDefaultLanguage($language);
        }
        return $this;
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
        if ($languageCode !== null && isset($this->titleTranslations[$languageCode])) {
            return $this->titleTranslations[$languageCode];
        } elseif (isset($this->titleTranslations[$this->defaultLanguage])) {
            return $this->titleTranslations[$this->defaultLanguage];
        } else {
            return $this->countryCode;
        }
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
     * Gets country code
     *
     * @return string
     */
    public function getCode() {
        return $this->countryCode;
    }

    /**
     * Adds new group to payment methods for this country.
     * If some other group was registered earlier with same key, overwrites it.
     * Returns given group
     *
     * @param WebToPay_PaymentMethodGroup $group
     *
     * @return WebToPay_PaymentMethodGroup
     */
    public function addGroup(WebToPay_PaymentMethodGroup $group) {
        return $this->groups[$group->getKey()] = $group;
    }

    /**
     * Gets group object with specified group key. If no group with such key is found, returns null.
     *
     * @param string $groupKey
     *
     * @return null|WebToPay_PaymentMethodGroup
     */
    public function getGroup($groupKey) {
        return isset($this->groups[$groupKey]) ? $this->groups[$groupKey] : null;
    }

    /**
     * Returns payment method groups registered for this country.
     *
     * @return WebToPay_PaymentMethodGroup[]
     */
    public function getGroups() {
        return $this->groups;
    }

    /**
     * Gets payment methods in all groups
     *
     * @return WebToPay_PaymentMethod[]
     */
    public function getPaymentMethods() {
        $paymentMethods = array();
        foreach ($this->groups as $group) {
            $paymentMethods = array_merge($paymentMethods, $group->getPaymentMethods());
        }
        return $paymentMethods;
    }

    /**
     * Returns new country instance with only those payment methods, which are available for provided amount.
     *
     * @param integer $amount
     * @param string  $currency
     *
     * @return WebToPay_PaymentMethodCountry
     */
    public function filterForAmount($amount, $currency) {
        $country = new WebToPay_PaymentMethodCountry($this->countryCode, $this->titleTranslations, $this->defaultLanguage);
        foreach ($this->getGroups() as $group) {
            $group = $group->filterForAmount($amount, $currency);
            if (!$group->isEmpty()) {
                $country->addGroup($group);
            }
        }
        return $country;
    }

    /**
     * Returns new country instance with only those payment methods, which are returns or not iban number after payment
     *
     * @param boolean $isIban
     *
     * @return WebToPay_PaymentMethodCountry
     */
    public function filterForIban($isIban = true) {
        $country = new WebToPay_PaymentMethodCountry($this->countryCode, $this->titleTranslations, $this->defaultLanguage);
        foreach ($this->getGroups() as $group) {
            $group = $group->filterForIban($isIban);
            if (!$group->isEmpty()) {
                $country->addGroup($group);
            }
        }
        return $country;
    }

    /**
     * Returns whether this country has no groups
     *
     * @return boolean
     */
    public function isEmpty() {
        return count($this->groups) === 0;
    }

    /**
     * Loads groups from given XML node
     *
     * @param SimpleXMLElement $countryNode
     */
    public function fromXmlNode($countryNode) {
        foreach ($countryNode->payment_group as $groupNode) {
            $key = (string) $groupNode->attributes()->key;
            $titleTranslations = array();
            foreach ($groupNode->title as $titleNode) {
                $titleTranslations[(string) $titleNode->attributes()->language] = (string) $titleNode;
            }
            $this->addGroup($this->createGroup($key, $titleTranslations))->fromXmlNode($groupNode);
        }
    }

    /**
     * Method to create new group instances. Overwrite if you have to use some other group subtype.
     *
     * @param string $groupKey
     * @param array  $translations
     *
     * @return WebToPay_PaymentMethodGroup
     */
    protected function createGroup($groupKey, array $translations = array()) {
        return new WebToPay_PaymentMethodGroup($groupKey, $translations, $this->defaultLanguage);
    }
}