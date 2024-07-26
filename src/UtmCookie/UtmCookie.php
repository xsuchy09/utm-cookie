<?php
/******************************************************************************
 * Author: Petr Suchy (xsuchy09) <suchy@wamos.cz> <http://www.wamos.cz>
 * Subject: WAMOS <http://www.wamos.cz>
 * Project: utmcookie
 * Copyright: (c) Petr Suchy (xsuchy09) <suchy@wamos.cz> <http://www.wamos.cz>
 *****************************************************************************/

namespace UtmCookie;

use DateInterval;
use DateTime;
use Exception;
use stdClass;
use UnexpectedValueException;

/**
 * Utm-Cookie saves utm parameters from url into cookie with defined lifetime (default 7 days).
 * Than cookie (utm) can be used later without parsing google or any other cookies.
 *
 * @package UtmCookie
 * @version 2.0.2
 * @author Petr Suchy (xsuchy09) <suchy@wamos.cz> <http://www.wamos.cz>
 * @license Apache License 2.0
 * @link https://github.com/xsuchy09/utm-cookie
 */
class UtmCookie
{

	/**
	 * Default utm cookie lifetime.
	 */
	public const DEFAULT_UTM_COOKIE_LIFETIME = 'P7D';

	/**
	 * Which names can utm cookie keys have. Others are ignored ...
	 */
	public const DEFAULT_ALLOWED_UTM_COOKIE_KEYS = [
		'utm_campaign',
		'utm_medium',
		'utm_source',
		'utm_term',
		'utm_content'
	];
	
	/**
	 * Name of cookie where will be saved utm params.
	 * 
	 * @var string
	 */
	private static $utmCookieName;
	
	/**
	 * @var array|null
	 */
	private static $utmCookie;
	
	/**
	 * Lifetime of utmCookie
	 * 
	 * @var DateInterval
	 */
	private static $lifetime;
	
	/**
	 * If overwrite all utm values when even one is set in get. Default true.
	 * 
	 * @var bool
	 */
	private static $overwrite;
	
	/**
	 * Path for cookie. Default "/" so not empty like in setcookie PHP function!
	 *
	 * @var string
	 */
	private static $path = '/';
	
	/**
	 * Domain for cookie.
	 *
	 * @var string
	 */
	private static $domain = '';
	
	/**
	 * If cookie should be secured (same as $secure parameter in setcookie PHP function).
	 *
	 * @var bool
	 */
	private static $secure = false;
	
	/**
	 * If cookie should be http only (same as $httponly parameter in setcookie PHP function).
	 *
	 * @var bool
	 */
	private static $httpOnly = false;

	/**
	 * @var array
	 */
	private static $allowedUtmCookieKeys;
	
	/**
	 * Constructor - private.
	 */
	private function __construct()
	{
		
	}
	
	/**
	 * Initialize. Get values from _GET and _COOKIES and save to UtmCookie. Init self::$utmCookie value.
	 * 
	 * @return void
	 */
	public static function init(): void
	{
		// if initializated, just return
		if (self::$utmCookie !== null) {
			return;
		}
		
		self::initStaticValues();

		// utm from _COOKIE
		$utmCookieFilter = filter_input(INPUT_COOKIE, self::$utmCookieName, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
		if (false === is_array($utmCookieFilter)) {
			$utmCookieFilter = [];
		}
		$utmCookie = self::removeNullValues($utmCookieFilter);

		// utm from _GET
		$utmGetFilter = filter_input_array(
				INPUT_GET, 
				[
					'utm_campaign' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 
					'utm_medium'   => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 
					'utm_source'   => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 
					'utm_term'     => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 
					'utm_content'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS
				]
		);
		if (false === is_array($utmGetFilter)) {
			$utmGetFilter = [];
		}
		$utmGet = self::removeNullValues($utmGetFilter);
		
		if (self::$overwrite === true && count($utmGet) !== 0) {
			$utmCookieSave = array_merge(self::$utmCookie, $utmGet);
		} else {
			$utmCookieSave = array_merge(self::$utmCookie, $utmCookie, $utmGet);
		}

		if (count($utmGet) !== 0) {
			self::save($utmCookieSave);
		} else {
			self::$utmCookie = $utmCookieSave;
		}
	}
	
	/**
	 * Initialize static values to default (or empty) values.
	 */
	private static function initStaticValues(): void
	{
		if (self::$utmCookieName === null) {
			self::$utmCookieName = 'utm';
		}

		self::$utmCookie = [
			'utm_campaign' => null, 
			'utm_medium'   => null, 
			'utm_source'   => null, 
			'utm_term'     => null, 
			'utm_content'  => null
		];

		
		if (self::$lifetime === null) {
			self::$lifetime = new DateInterval(self::DEFAULT_UTM_COOKIE_LIFETIME);
		}

		if (self::$overwrite === null) {
			self::$overwrite = true;
		}

		if (self::$allowedUtmCookieKeys === null) {
			self::$allowedUtmCookieKeys = self::DEFAULT_ALLOWED_UTM_COOKIE_KEYS;
		}
	}
	
	/**
	 * Remove elements with null values from array.
	 * 
	 * @param array|null $array
	 * 
	 * @return array
	 */
	private static function removeNullValues(array $array = null): array
	{
		// null (undefined) or false (filter failed)
		if ($array === null || $array === false) {
			return [];
		}
		return array_filter(
				$array, 
				static function($value) {
					return $value !== null;
				}
		);
	}
	
	/**
	 * Set name of cookie where will be saved utm params.
	 * 
	 * @param string $utmCookieName
	 */
	public static function setName(string $utmCookieName): void
	{
		self::$utmCookieName = $utmCookieName;
		// cancel previous init
		self::$utmCookie = null;
	}
	
	/**
	 * Set lifetime of utm cookie.
	 * 
	 * @param DateInterval $lifetime
	 */
	public static function setLifetime(DateInterval $lifetime): void
	{
		self::$lifetime = $lifetime;
	}
	
	/**
	 * Set if even one utm value in _GET will overwrite all utm values or not.
	 * 
	 * @param bool $overwrite
	 */
	public static function setOverwrite(bool $overwrite): void
	{
		self::$overwrite = $overwrite;
		// cancel previous init
		self::$utmCookie = null;
	}
	
	/**
	 * Set path for cookie.
	 * 
	 * @param string $path
	 */
	public static function setPath(string $path): void
	{
		self::$path = $path;
	}
	
	/**
	 * Set domain for cookie.
	 * 
	 * @param string $domain
	 */
	public static function setDomain(string $domain): void
	{
		self::$domain = $domain;
	}
	
	/**
	 * Set secure for cookie.
	 * 
	 * @param bool $secure
	 */
	public static function setSecure(bool $secure): void
	{
		self::$secure = $secure;
	}
	
	/**
	 * Set httponly for cookie.
	 * 
	 * @param bool $httpOnly
	 */
	public static function setHttpOnly(bool $httpOnly): void
	{
		self::$httpOnly = $httpOnly;
	}

	/**
	 * Set allowed keys for utm cookie array. Default is UtmCookie::DEFAULT_ALLOWED_UTM_COOKIE_KEYS.
	 *
	 * @param array $allowedUtmCookieKeys
	 */
	public static function setAllowedUtmCookieKeys(array $allowedUtmCookieKeys)
	{
		self::$allowedUtmCookieKeys = $allowedUtmCookieKeys;
	}
	
	/**
	 * Get all utm values or just value of utm with specific key.
	 * 
	 * @param string|null $key Default null (return all values as array).
	 * 
	 * @return array|mixed|null Return string value, array or null if not set.
	 * @throws UnexpectedValueException
	 */
	public static function get(?string $key = null)
	{
		self::init();
		
		if ($key === null) {
			return self::$utmCookie;
		}
		if (strpos($key, 'utm_') !== 0 && true === in_array('utm_' . $key, self::DEFAULT_ALLOWED_UTM_COOKIE_KEYS, true)) {
			$key = 'utm_' . $key;
		}
		if (false === array_key_exists($key, self::$utmCookie)) {
			throw new UnexpectedValueException(sprintf('Argument $key has unexpecte value "%s". Utm value with key "%s" does not exists.', $key, $key));
		}
		return self::$utmCookie[$key];
	}
	
	/**
	 * Return all utm values as stdClass.
	 * 
	 * @return stdClass
	 */
	public static function getObject(): stdClass
	{
		return (object)self::get();
	}
	
	/**
	 * Save utmCookie value into _COOKIE and set actual self::$utmCookie value.
	 * It is public method since version 2.0.2.
	 * Can be called with own values to rewrite these cookies, but keys for utm cookie has to be allowed by UtmCookie::$allowedUtmCookieKeys.
	 * To set UtmCookie::$allowedUtmCookieKeys use UtmCookie::setAllowedUtmCookieKeys method.
	 *
	 * @param array $utmCookieSave
	 *
	 * @return bool
	 */
	public static function save(array $utmCookieSave): bool
	{
		try {
			$expire = new DateTime();
			$expire->add(self::$lifetime);
		} catch (Exception $ex) {
			return false;
		}
		
		foreach ($utmCookieSave as $key => $value) {
			if (true === in_array($key, self::$allowedUtmCookieKeys, false)) {
				setcookie(self::$utmCookieName . '[' . $key . ']', $value, $expire->getTimestamp(), self::$path, self::$domain, self::$secure, self::$httpOnly);
			}
		}

		self::$utmCookie = $utmCookieSave;

		return true;
	}
}
