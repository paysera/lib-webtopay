<?php
/**
 * PHP Library for WebToPay provided services.
 * Copyright (C) 2010  http://www.webtopay.com/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    WebToPay
 * @author     Mantas Zimnickas <mantas@evp.lt>
 * @license    http://www.gnu.org/licenses/lgpl.html
 * @version    1.0
 * @link       http://www.webtopay.com/
 */


class WebToPay {

    /**
     * Returns list of supported payment types.
     *
     * Array structure:
     *     0 - Country code
     *     1 - Payment type code
     *     2 - Minimal possible amount that can be transfered in cents.
     *     3 - Maximal possible amount that can be transfered in cents.
     *     4 - Human readable description.
     *
     * Min/max amount equal to 0 means unlimited.
     *
     * @return array
     */
    public static function getPaymentTypes() {
        return array(
            array(
                    'LT', 'hanza', 200, 0,
                    'Swedbanko el. banko sistema. (swedbank.lt)'
                ),
            array(
                    'LT', 'vb2', 500, 0,
                    'SEB banko el. banko sistema'
                ),
            array(
                    'LT', 'nord', 500, 0,
                    'DnB Nord banko el. banko sistema. (I-linija)'
                ),
            array(
                    'LT', 'snoras', 100, 0,
                    'Snoras banko el. banko sistema (Bankas Internetu+)'
                ),
            array(
                    'LT', 'sampo', 100, 0,
                    'Danske banko el. banko sistema'
                ),
            array(
                    'LT', 'parex', 100, 0,
                    'Parex banko el.banko sistema'
                ),
            array(
                    'LT', 'ukio', 100, 0,
                    'Ūkio banko el. banko sistema Eta Bankas'
                ),
            array(
                    'LV', 'nordealv', 100, 0,
                    'Nordea Bank Filnland Plc Internetinės bankininkystės '.
                    'sistema.'
                ),
            array(
                    'LT', 'nordealt', 100, 0,
                    'Nordea Bank Filnland Plc Internetinės bankininkystės '.
                    'sistema.'
                ),
            array(
                    'LT', 'sb', 100, 0,
                    'Šiaulių banko SB Linija'
                ),
            array(
                    'LT', 'barcode', 1000, 200000,
                    'Apmokėjimas Lietuvos spaudos kioskuose'
                ),
            array(
                    'LV', 'hanzalv', 800, 0,
                    'Swedbanko el. banko sistema Latvijoje'
                ),
            array(
                    'EE', 'nordeaee', 100, 0,
                    'Nordea banko Net bank sistema Estijoje'
                ),
            array(
                    'EE', 'hanzaee', 1200, 0,
                    'Swedbanko el. banko sistema Estijoje'
                ),
            array(
                    'LT', 'maximalt', 100, 1000000,
                    'Atsiskaitymas visose Maxima parduotuvių kasose Lietuvoje'
                ),
            array(
                    'LV', 'maximalv', 100, 1000000,
                    'Atsiskaitymas visose Maxima parduotuvių kasose Latvijoje '.
                    '(jau greitai)'
                ),
            array(
                    '', 'paypal', 5000, 1000000,
                    'Paypal sistema (tik pagal atskirą susitarimą)'
                ),
            array(
                    '', 'webmoney', 100, 0,
                    'Atsiskaitymas virtualių pinigų sistema webmoney.ru'
                ),
            array(
                    'LT', 'wap', 100, 1000,
                    'Atsiskaitymas wap svetainėse. Norėdami naudoti šį mokėjimo '.
                    'būdą jus turite pasirašyti Mikro mokėjimų sutartį.'
                ),
            array(
                    '', 'sms', 1, 15000,
                    'Atsiskaitymas padidinto tarifo trumposiomis žinutėmis (SMS '.
                    'Bank).  Norėdami naudoti ši mokėjimo būda jus turite '.
                    'pasirašyti Mikro mokėjimų sutartį.'
                ),
            array(
                    'LT', 'EME', 1, 0,
                    'Atsiskaitymas virtualiais mokėjimai.lt dovanų pinigais '.
                    '(jau greitai)'
                ),
            array(
                    'LT', 'coupon', 1, 0,
                    'Atsiskaitymas mokėjimai.lt dovanų kuponais'
                ),
        );
    }


    /**
     * Builds form data array.
     *
     * This method checks all parameters and generates safe array with correct
     * form data or raises WebToPayException.
     *
     * Method accepts single parameter of array type. All possible array keys
     * are described here:
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * @param array $data Information about current payment request.
     * @return array
     */
    public static function getFormData($data) {
    }

    /**
     * Checks and validates callback parameters.
     *
     * First parameter mostly should by $_GET array.
     *
     * Description of $data parametre can be found here:
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * Return value is true if all validations are ok and false if something
     * went wrong.
     *
     * @param array $request Request array.
     * @param bool
     */
    public static function checkTransactionData($request, $data) {
    }

}

