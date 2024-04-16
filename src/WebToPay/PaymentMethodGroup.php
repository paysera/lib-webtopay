<?php

declare(strict_types=1);

/**
 * Wrapper class to group payment methods. Each country can have several payment method groups, each of them
 * have one or more payment methods.
 */
class WebToPay_PaymentMethodGroup
{
    /**
     * Some unique (in the scope of country) key for this group
     */
    protected string $groupKey;

    /**
     * Translations array for this group. Holds associative array of group title by country codes.
     *
     * @var array<string, string>
     */
    protected array $translations;

    /**
     * Holds actual payment methods
     *
     * @var WebToPay_PaymentMethod[]
     */
    protected array $paymentMethods;

    /**
     * Default language for titles
     */
    protected string $defaultLanguage;

    /**
     * Constructs object
     *
     * @param string $groupKey
     * @param array<string, string> $translations
     * @param string $defaultLanguage
     */
    public function __construct(string $groupKey, array $translations = [], string $defaultLanguage = 'lt')
    {
        $this->groupKey = $groupKey;
        $this->translations = $translations;
        $this->defaultLanguage = $defaultLanguage;
        $this->paymentMethods = [];
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     */
    public function setDefaultLanguage(string $language): WebToPay_PaymentMethodGroup
    {
        $this->defaultLanguage = $language;
        foreach ($this->paymentMethods as $paymentMethod) {
            $paymentMethod->setDefaultLanguage($language);
        }

        return $this;
    }

    /**
     * Gets default language for titles
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /**
     * Gets title of the group. Tries to get title in specified language. If it is not found or if language is not
     * specified, uses default language, given to constructor.
     */
    public function getTitle(?string $languageCode = null): string
    {
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
     */
    public function getKey(): string
    {
        return $this->groupKey;
    }

    /**
     * Returns available payment methods for this group
     *
     * @return WebToPay_PaymentMethod[]
     */
    public function getPaymentMethods(): array
    {
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
    public function addPaymentMethod(WebToPay_PaymentMethod $paymentMethod): WebToPay_PaymentMethod
    {
        return $this->paymentMethods[$paymentMethod->getKey()] = $paymentMethod;
    }

    /**
     * Gets payment method object with key. If no payment method with such key is found, returns null.
     */
    public function getPaymentMethod(string $key): ?WebToPay_PaymentMethod
    {
        return $this->paymentMethods[$key] ?? null;
    }

    /**
     * Returns new group instance with only those payment methods, which are available for provided amount.
     *
     * @throws WebToPayException
     */
    public function filterForAmount(int $amount, string $currency): WebToPay_PaymentMethodGroup
    {
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
     */
    public function filterForIban(bool $isIban = true): WebToPay_PaymentMethodGroup
    {
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
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->paymentMethods) === 0;
    }

    /**
     * Loads payment methods from given XML node
     */
    public function fromXmlNode(SimpleXMLElement $groupNode): void
    {
        foreach ($groupNode->payment_type as $paymentTypeNode) {
            $key = (string)$paymentTypeNode->attributes()->key;
            $titleTranslations = [];
            foreach ($paymentTypeNode->title as $titleNode) {
                $titleTranslations[(string)$titleNode->attributes()->language] = (string)$titleNode;
            }
            $logoTranslations = [];
            foreach ($paymentTypeNode->logo_url as $logoNode) {
                if ((string)$logoNode !== '') {
                    $logoTranslations[(string)$logoNode->attributes()->language] = (string)$logoNode;
                }
            }
            $minAmount = null;
            $maxAmount = null;
            $currency = null;
            $isIban = false;
            $baseCurrency = null;
            if (isset($paymentTypeNode->min)) {
                $minAmount = (int)$paymentTypeNode->min->attributes()->amount;
                $currency = (string)$paymentTypeNode->min->attributes()->currency;
            }
            if (isset($paymentTypeNode->max)) {
                $maxAmount = (int)$paymentTypeNode->max->attributes()->amount;
                $currency = (string)$paymentTypeNode->max->attributes()->currency;
            }

            if (isset($paymentTypeNode->is_iban)) {
                $isIban = (bool)$paymentTypeNode->is_iban;
            }
            if (isset($paymentTypeNode->base_currency)) {
                $baseCurrency = (string)$paymentTypeNode->base_currency;
            }
            $this->addPaymentMethod($this->createPaymentMethod(
                $key,
                $minAmount,
                $maxAmount,
                $currency,
                $logoTranslations,
                $titleTranslations,
                $isIban,
                $baseCurrency
            ));
        }
    }

    /**
     * Method to create new payment method instances. Overwrite if you have to use some other subclass.
     *
     * @param string $key
     * @param int|null $minAmount
     * @param int|null $maxAmount
     * @param string|null $currency
     * @param array<string, string> $logoList
     * @param array<string, string> $titleTranslations
     * @param bool $isIban
     * @param mixed $baseCurrency
     *
     * @return WebToPay_PaymentMethod
     */
    protected function createPaymentMethod(
        string $key,
        ?int $minAmount,
        ?int $maxAmount,
        ?string $currency,
        array $logoList = [],
        array $titleTranslations = [],
        bool $isIban = false,
        $baseCurrency = null
    ): WebToPay_PaymentMethod {
        return new WebToPay_PaymentMethod(
            $key,
            $minAmount,
            $maxAmount,
            $currency,
            $logoList,
            $titleTranslations,
            $this->defaultLanguage,
            $isIban,
            $baseCurrency
        );
    }
}
