Version history
===============
Version 3.0.2   - 2024-06-27

    * addded possibility to define Paysera routes in environment variables

Version 3.0.0   - 2024-04-30

    * increased minimal PHP version to 7.4
    * the code was brought up to date with the latest PHP versions and standards
    * removed deprecated methods
    * removed unused code
    * fixed some bugs

Version 2.0.1   - 2023-10-19

    * fixed versioning texts at changelog

Version 2.0     - 2023-10-13

    * added amount support for payment method list request

Version 1.8.1   - 2023-05-09

    * fixed TLS at certificate URLs

Version 1.8     - 2022-04-13

    * added callback data decryption functionality

Version 1.7     - 2022-04-11

    * added support for PHP 8.0+

Version 1.6     - 2012-04-12

    * refactored WebToPay class to separate non-static classes
    * changed request signing and callback processing - now using base64 encoding
    * removed some public WebToPay methods

Version 1.5     - 2012-02-08

    * added API for getting available payment methods
    * refactored old example and added another one

Version 1.5		- 2011-10-13

	* updated SS1 to SS1 v2 (SS1 obsolete and not available in 1.5)
	* force to use SS2 if OpenSSL available
	* added time_limit parameter

Version 1.4		- 2011-07-01

	* added WebToPay::getPaymentUrl()

Version 1.4		- 2011-06-15

	* WebToPay::getXML() fix

Version 1.4		- 2011-05-30

	* small refactoring

Version 1.4		- 2011-05-26

	* added methods which enables user to download payment method list

Version 1.4		- 2011-03-28

	* added 'RequestID'

Version 1.3.2   - 2010-08-18

    * country validation fix

Version 1.3.1   - 2010-08-16

    * projectid is required in checkResponse() in 1.3.1 version

Version 1.3     - 2010-06-14

    * 'sign' parameter now supports utf-8
    * WebToPay::buildRepeatRequest() added
    * more unit-tests added

Version 1.2.5   - 2010-06-14

    * removed response checking by parameters specifications, now response is
      checked only against SS1/SS2 security checks


Version 1.2.4   - 2010-05-20

    * 0x14 error code added
    * sign is required

Version 1.2.3   - 2010-05-13

    * OpenSSL verification fixes to work correctly with different OpenSSL versions
    * PHP5 syntax fixes: non-static method changed to static as they should be
    * demo fixes to handle correctly magic quotes

Version 1.2.2   - 2010-05-11

    * ignore third version number when comparing version numbers
    * checks for openssl library version, use SS2 only with 0.9.8 and greather version of openssl
    * demo response url fix

Version 1.2.1   - 2010-05-10

    * orderid, amount and currency are not required in checkResponse()
    * orderid no longer is restricted to only digits, now characters ar accepted
    * posibility to toggle SS2 checking
    * improved unit testing

Version 1.2     - 2010-04-14

    * introduced library versioning
    * mikro payments support

Version 1.1     - 2010-04-14

    * merchantid is deprecated, projectid should be used instead
    * SS1 checking added

Version 1.0     - 2010-01-21

    * Initial release
