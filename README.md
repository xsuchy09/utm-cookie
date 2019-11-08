# UtmCookie

PHP library to save utm parameters from url into cookie for later use.
PHP 7.1 is required for version 2.0.0+. If you need PHP 5.4+ compatibility use 1.0.6 version.

Authors:
 - Petr Suchy (xsuchy09 - www.wamos.cz)

## Overview

UtmCookie saves utm parameters from url into cookie with defined lifetime (default 7 days). Than cookie (utm) can be used later without parsing google or any other cookies.

It handles utm parameters:
- utm_campaign
- utm_medium
- utm_source
- utm_term
- utm_content

You can get them with original name or wihout "utm_" (for example just "source" for "utm_source" - you can use both) - see examples.

Since version 2.0.2 you can rewrite these cookies just with call ``UtmCookie::save($array)`` where ``$array`` should contains keys allowed by ``UtmCookie::$allowedUtmCookieKeys`` (default are allowed utm parameters names). 

## Installation (via composer)

[Get composer](http://getcomposer.org/doc/00-intro.md) and add this in your requires section of the composer.json:

```
{
    "require": {
        "xsuchy09/utm-cookie": "*"
    }
}
```

and then

```
composer install
```

## Usage

### Basic Example

```php
UtmCookie::init(); // just init - read utm params and cookie and save new values (is auto called by first call of UtmCookie::get method)
UtmCookie::get(); // get all utm cookies as array
UtmCookie::getObject(); // get all utm cookies as object (stdClass)
UtmCookie::get('utm_source'); // get utm_source
UtmCookie::get('source'); // get utm_source
```

### Set lifetime of utm cookie

```php
$dateInterval = DateInterval::createFromDateString('7 days');
UtmCookie::setLifetime($dateInterval);
```

### Set name of utm cookie

```php
UtmCookie::setName('utm');
```

### Set if overwrite all utm values even if only one detected.
Default TRUE. If set to false, utm value is overwite only if set (others will stay).

```php
UtmCookie::setOverwrite(false);
```

More examples can be found in the examples/ directory.