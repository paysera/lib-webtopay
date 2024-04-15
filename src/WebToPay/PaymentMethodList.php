<?php

/**
 * Class with all information about available payment methods for some project, optionally filtered by some amount.
 */
class WebToPay_PaymentMethodList
{
    /**
     * Holds available payment countries
     *
     * @var WebToPay_PaymentMethodCountry[]
     */
    protected array $countries;

    /**
     * Default language for titles
     */
    protected string $defaultLanguage;

    /**
     * Project ID, to which this method list is valid
     */
    protected int $projectId;

    /**
     * Currency for min and max amounts in this list
     */
    protected string $currency;

    /**
     * If this list is filtered for some amount, this field defines it
     */
    protected ?int $amount;

    /**
     * Constructs object
     *
     * @param int $projectId
     * @param string $currency currency for min and max amounts in this list
     * @param string $defaultLanguage
     * @param int|null $amount null if this list is not filtered by amount
     */
    public function __construct(int $projectId, string $currency, string $defaultLanguage = 'lt', ?int $amount = null)
    {
        $this->projectId = $projectId;
        $this->countries = [];
        $this->defaultLanguage = $defaultLanguage;
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     */
    public function setDefaultLanguage(string $language): WebToPay_PaymentMethodList
    {
        $this->defaultLanguage = $language;
        foreach ($this->countries as $country) {
            $country->setDefaultLanguage($language);
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
     * Gets project ID for this payment method list
     */
    public function getProjectId(): int
    {
        return $this->projectId;
    }

    /**
     * Gets currency for min and max amounts in this list
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Gets whether this list is already filtered for some amount
     */
    public function isFiltered(): bool
    {
        return $this->amount !== null;
    }

    /**
     * Returns available countries
     *
     * @return WebToPay_PaymentMethodCountry[]
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * Adds new country to payment methods. If some other country with same code was registered earlier, overwrites it.
     * Returns added country instance
     */
    public function addCountry(WebToPay_PaymentMethodCountry $country): WebToPay_PaymentMethodCountry
    {
        return $this->countries[$country->getCode()] = $country;
    }

    /**
     * Gets country object with specified country code. If no country with such country code is found, returns null.
     */
    public function getCountry(string $countryCode): ?WebToPay_PaymentMethodCountry
    {
        return isset($this->countries[$countryCode]) ? $this->countries[$countryCode] : null;
    }

    /**
     * Returns new payment method list instance with only those payment methods, which are available for provided
     * amount.
     * Returns itself, if list is already filtered and filter amount matches the given one.
     *
     * @throws WebToPayException    if this list is already filtered and not for provided amount
     */
    public function filterForAmount(int $amount, string $currency): WebToPay_PaymentMethodList
    {
        if ($currency !== $this->currency) {
            throw new WebToPayException(
                'Currencies do not match. Given currency: '
                . $currency
                . ', currency in list: '
                . $this->currency
            );
        }
        if ($this->isFiltered()) {
            if ($this->amount === $amount) {
                return $this;
            } else {
                throw new WebToPayException('This list is already filtered, use unfiltered list instead');
            }
        } else {
            $list = new WebToPay_PaymentMethodList($this->projectId, $currency, $this->defaultLanguage, $amount);
            foreach ($this->getCountries() as $country) {
                $country = $country->filterForAmount($amount, $currency);
                if (!$country->isEmpty()) {
                    $list->addCountry($country);
                }
            }

            return $list;
        }
    }

    /**
     * Loads countries from given XML node
     */
    public function fromXmlNode(SimpleXMLElement $xmlNode): void
    {
        foreach ($xmlNode->country as $countryNode) {
            $titleTranslations = [];
            foreach ($countryNode->title as $titleNode) {
                $titleTranslations[(string)$titleNode->attributes()->language] = (string)$titleNode;
            }
            $this->addCountry($this->createCountry((string)$countryNode->attributes()->code, $titleTranslations))
                ->fromXmlNode($countryNode);
        }
    }

    /**
     * Method to create new country instances. Overwrite if you have to use some other country subtype.
     *
     * @param string $countryCode
     * @param array<string, string> $titleTranslations
     *
     * @return WebToPay_PaymentMethodCountry
     */
    protected function createCountry(string $countryCode, array $titleTranslations = []): WebToPay_PaymentMethodCountry
    {
        return new WebToPay_PaymentMethodCountry($countryCode, $titleTranslations, $this->defaultLanguage);
    }
}
