<?php
/******************************************************************************
 * Author: Petr Suchy (xsuchy09) <suchy@wamos.cz> <http://www.wamos.cz>
 * Subject: WAMOS <http://www.wamos.cz>
 * Project: utmcookie
 * Copyright: (c) Petr Suchy (xsuchy09) <suchy@wamos.cz> <http://www.wamos.cz>
 *****************************************************************************/

require_once __DIR__ . '/../src/UtmCookie/UtmCookie.php';

use UtmCookie\UtmCookie;

// just init (read utm params and cookie and save new values)
UtmCookie::init();

// set name of utm cookie (this cookie will be created and used for saving all utm values)
UtmCookie::setName('my_utm');

// prevent overwrite all values if even one utm param in _GET will be recognised
// when utm_medium will be in _GET and utm_source in cookie, utm_medium will be just adedd
// utm_cookie will have both values ... default values are overwrited (every single utm param init new utm values)
UtmCookie::setOverwrite(false);

// set lifetime to 1 month
UtmCookie::setLifetime(new DateInterval('P1M'));

// get all utm cookie values
$utmCookie = UtmCookie::get();
// get all utm cookie values as object/stdClass
$utmCookieObject = UtmCookie::getObject();

// get just utm_source
$utmCookieSource = UtmCookie::get('utm_source');
// or only (return utm_source)
$utmCookieSource = UtmCookie::get('source');
