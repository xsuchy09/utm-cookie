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
use stdClass;
use UnexpectedValueException;

/**
 * Utm-Cookie saves utm parameters from url into cookie with defined lifetime (default 7 days).
 * Than cookie (utm) can be used later without parsing google or any other cookies.
 *
 * @package UtmCookie
 * @version 1.0.1
 * @author Petr Suchy (xsuchy09) <suchy@wamos.cz> <http://www.wamos.cz>
 * @license Apache License 2.0
 * @link https://github.com/xsuchy09/utm-cookie
 */
class UtmCookie
{
	
	
	const DEFAULT_UTM_COOKIE_LIFETIME = 'P7D';
	
	/**
	 * Name of cookie where will be saved utm params.
	 * 
	 * @var string
	 */
	private static $utmCookieName;
	
	/**
	 * @var array
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
	public static function init()
	{
		// if initializated, just return
		if (self::$utmCookie !== null) {
			return;
		}
		
		self::initStaticValues();

		// utm from _COOKIE
		$utmCookieFilter = filter_input(INPUT_COOKIE, self::$utmCookieName, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
		if (false === is_array($utmCookieFilter)) {
			$utmCookieFilter = [];
		}
		$utmCookie = self::removeNullValues($utmCookieFilter);

		// utm from _GET
		$utmGetFilter = filter_input_array(
				INPUT_GET, 
				[
					'utm_campaign' => FILTER_SANITIZE_STRING, 
					'utm_medium'   => FILTER_SANITIZE_STRING, 
					'utm_source'   => FILTER_SANITIZE_STRING, 
					'utm_term'     => FILTER_SANITIZE_STRING, 
					'utm_content'  => FILTER_SANITIZE_STRING
				]
		);
		if (false === is_array($utmGetFilter)) {
			$utmGetFilter = [];
		}
		$utmGet = self::removeNullValues($utmGetFilter);
		
		if (count($utmGet) !== 0 && self::$overwrite === true) {
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
	private static function initStaticValues()
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
	}
	
	/**
	 * Remove elements with null values from array.
	 * 
	 * @param array|null $array
	 * 
	 * @return array
	 */
	private static function removeNullValues(array $array = null)
	{
		// null (undefined) or false (filter failed)
		if ($array === null || $array === false) {
			return [];
		}
		return array_filter(
				$array, 
				function($value) {
					return $value !== null;
				}
		);
	}
	
	/**
	 * Set name of cookie where will be saved utm params.
	 * 
	 * @param string $utmCookieName
	 */
	public static function setName($utmCookieName)
	{
		self::$utmCookieName = $utmCookieName;
		// cancel previos init
		self::$utmCookie = null;
	}
	
	/**
	 * Set lifetime of utm cookie.
	 * 
	 * @param DateInterval $lifetime
	 */
	public static function setLifetime(DateInterval $lifetime)
	{
		self::$lifetime = $lifetime;
	}
	
	/**
	 * Set if even one utm value in _GET will overwrite all utm values or not. Have to be call first (before init method).
	 * 
	 * @param bool $overwrite
	 */
	public static function setOverwrite($overwrite)
	{
		self::$overwrite = (bool)$overwrite;
	}
	
	/**
	 * Get all utm values or just value of utm with specific key.
	 * 
	 * @param string $key Default null (return all values as array).
	 * 
	 * @return string|null Return string value or null if not set.
	 */
	public static function get($key = null)
	{
		self::init();
		
		if ($key === null) {
			return self::$utmCookie;
		} else {
			if (mb_strpos($key, 'utm_') !== 0) {
				$key = 'utm_' . $key;
			}
			if (false === array_key_exists($key, self::$utmCookie)) {
				throw new UnexpectedValueException(sprintf('Argument $key has unexpecte value "%s". Utm value with key "%s" does not exists.', $key, $key));
			} else {
				return self::$utmCookie[$key];
			}
		}
	}
	
	/**
	 * Return all utm values as stdClass.
	 * 
	 * @return stdClass
	 */
	public static function getObject()
	{
		return (object)self::get();
	}
	
	/**
	 * Save utmCookie value into _COOKIE and set actual self::$utmCookie value (call only from init).
	 * 
	 * @param array $utmCookieSave
	 */
	private static function save(array $utmCookieSave)
	{
		$expire = new DateTime();
		$expire->add(self::$lifetime);
		
		foreach ($utmCookieSave as $key => $value) {
			setcookie(self::$utmCookieName . '[' . $key . ']', $value, $expire->getTimestamp(), '/');
		}

		self::$utmCookie = $utmCookieSave;
	}
}
