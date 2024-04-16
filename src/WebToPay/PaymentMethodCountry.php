<?php

declare(strict_types=1);

/**
 * Payment method configuration for some country
 */
class WebToPay_PaymentMethodCountry
{
    protected string $countryCode;

    /**
     * Holds available payment types for this country
     *
     * @var WebToPay_PaymentMethodGroup[]
     */
    protected array $groups;

    /**
     * Default language for titles
     */
    protected string $defaultLanguage;

    /**
     * Translations array for this country. Holds associative array of country title by language codes.
     *
     * @var array<string, string>
     */
    protected array $titleTranslations;

    /**
     * Constructs object
     *
     * @param string $countryCode
     * @param array<string, string> $titleTranslations
     * @param string $defaultLanguage
     */
    public function __construct(string $countryCode, array $titleTranslations, string $defaultLanguage = 'lt')
    {
        $this->countryCode = $countryCode;
        $this->defaultLanguage = $defaultLanguage;
        $this->titleTranslations = $titleTranslations;
        $this->groups = [];
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     */
    public function setDefaultLanguage(string $language): WebToPay_PaymentMethodCountry
    {
        $this->defaultLanguage = $language;
        foreach ($this->groups as $group) {
            $group->setDefaultLanguage($language);
        }

        return $this;
    }

    /**
     * Gets title of the group. Tries to get title in specified language. If it is not found or if language is not
     * specified, uses default language, given to constructor.
     */
    public function getTitle(?string $languageCode = null): string
    {
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
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /**
     * Gets country code
     */
    public function getCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Adds new group to payment methods for this country.
     * If some other group was registered earlier with same key, overwrites it.
     * Returns given group
     */
    public function addGroup(WebToPay_PaymentMethodGroup $group): WebToPay_PaymentMethodGroup
    {
        return $this->groups[$group->getKey()] = $group;
    }

    /**
     * Gets group object with specified group key. If no group with such key is found, returns null.
     */
    public function getGroup(string $groupKey): ?WebToPay_PaymentMethodGroup
    {
        return $this->groups[$groupKey] ?? null;
    }

    /**
     * Returns payment method groups registered for this country.
     *
     * @return WebToPay_PaymentMethodGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Gets payment methods in all groups
     *
     * @return WebToPay_PaymentMethod[]
     */
    public function getPaymentMethods(): array
    {
        $paymentMethods = [];
        foreach ($this->groups as $group) {
            $paymentMethods = array_merge($paymentMethods, $group->getPaymentMethods());
        }

        return $paymentMethods;
    }

    /**
     * Returns new country instance with only those payment methods, which are available for provided amount.
     */
    public function filterForAmount(int $amount, string $currency): WebToPay_PaymentMethodCountry
    {
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
     */
    public function filterForIban(bool $isIban = true): WebToPay_PaymentMethodCountry
    {
        $country = new WebToPay_PaymentMethodCountry(
            $this->countryCode,
            $this->titleTranslations,
            $this->defaultLanguage
        );

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
     */
    public function isEmpty(): bool
    {
        return count($this->groups) === 0;
    }

    /**
     * Loads groups from given XML node
     */
    public function fromXmlNode(SimpleXMLElement $countryNode): void
    {
        foreach ($countryNode->payment_group as $groupNode) {
            $key = (string) $groupNode->attributes()->key;
            $titleTranslations = [];
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
     * @param array<string, string> $translations
     *
     * @return WebToPay_PaymentMethodGroup
     */
    protected function createGroup(string $groupKey, array $translations = []): WebToPay_PaymentMethodGroup
    {
        return new WebToPay_PaymentMethodGroup($groupKey, $translations, $this->defaultLanguage);
    }
}
