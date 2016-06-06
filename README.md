# UtmCookie

PHP library to save utm parameters from url into cookie for later use.

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
UTMCookie::save(); // save all utm parameters into cookie, overwrite only changed
UtmCookie::save(true); // save all utm parameters into cookie, overwrite all utm even if just one is detected
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

More examples can be found in the examples/ directory.