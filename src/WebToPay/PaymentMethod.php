<?php

declare(strict_types=1);

/**
 * Class to hold information about payment method
 */
class WebToPay_PaymentMethod
{
    /**
     * Assigned key for this payment method
     */
    protected string $key;

    protected ?int $minAmount;

    protected ?int $maxAmount;

    protected ?string $currency;

    /**
     * Logo url list by language. Usually logo is same for all languages, but exceptions exist
     *
     * @var array<string, string>
     */
    protected array $logoList;

    /**
     * Title list by language
     *
     * @var array<string, string>
     */
    protected array $titleTranslations;

    /**
     * Default language to use for titles
     */
    protected string $defaultLanguage;

    protected bool $isIban;

    protected ?string $baseCurrency;

    /**
     * Constructs object
     *
     * @param string $key
     * @param integer|null $minAmount
     * @param integer|null $maxAmount
     * @param string|null $currency
     * @param array<string, string> $logoList
     * @param array<string, string> $titleTranslations
     * @param string $defaultLanguage
     * @param bool $isIban
     * @param string|null $baseCurrency
     */
    public function __construct(
        string $key,
        ?int $minAmount,
        ?int $maxAmount,
        ?string $currency,
        array $logoList = [],
        array $titleTranslations = [],
        string $defaultLanguage = 'lt',
        bool $isIban = false,
        ?string $baseCurrency = null
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
     */
    public function setDefaultLanguage(string $language): WebToPay_PaymentMethod
    {
        $this->defaultLanguage = $language;

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
     * Get assigned payment method key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Gets logo url for this payment method. Uses specified language or default one.
     * If logotype is not found for specified language, null is returned.
     */
    public function getLogoUrl(?string $languageCode = null): ?string
    {
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
     */
    public function getTitle(?string $languageCode = null): string
    {
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
     * @throws WebToPayException
     */
    public function isAvailableForAmount(int $amount, string $currency): bool
    {
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
     */
    public function getMinAmountAsString(): string
    {
        return $this->minAmount === null ? '' : ($this->minAmount . ' ' . $this->currency);
    }

    /**
     * Returns max amount for this payment method. If no max amount is specified, returns empty string.
     */
    public function getMaxAmountAsString(): string
    {
        return $this->maxAmount === null ? '' : ($this->maxAmount . ' ' . $this->currency);
    }

    /**
     * Set if this method returns IBAN number after payment
     */
    public function setIsIban(bool $isIban): void
    {
        $this->isIban = $isIban == 1;
    }

    /**
     * Get if this method returns IBAN number after payment
     */
    public function isIban(): bool
    {
        return $this->isIban;
    }

    /**
     * Setter of BaseCurrency
     */
    public function setBaseCurrency(string $baseCurrency): void
    {
        $this->baseCurrency = $baseCurrency;
    }

    /**
     * Getter of BaseCurrency
     */
    public function getBaseCurrency(): ?string
    {
        return $this->baseCurrency;
    }
}
