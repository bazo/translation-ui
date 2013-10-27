<?php

namespace Caching;

use Nette\Caching\Cache;
use Nette;

/**
 * APC caching storage
 * @author Michael Moravec
 */
class ApcStorage implements Nette\Caching\IStorage
{
	/*	 * #@+ @internal cache structure */

	const META_CALLBACKS = 'callbacks';
	const META_DATA = 'data';
	const META_DELTA = 'delta';


	/*	 * #@- */

	private $prefix;


	/**
	 * Checks if APC extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('apc');
	}


	public function __construct($prefix = '')
	{
		if (!self::isAvailable()) {
			throw new Nette\NotSupportedException("PHP extension 'apc' is not loaded.");
		}

		$this->prefix = (string) $prefix;
	}


	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		$this->_normalizeKey($key);
		$meta = apc_fetch($key);
		if (!$meta)
			return NULL;

		// meta structure:
		// array(
		//     data => stored data
		//     delta => relative (sliding) expiration
		//     callbacks => array of callbacks (function, args)
		// )
		// verify dependencies
		if (!empty($meta[self::META_CALLBACKS]) && !Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
			apc_delete($key);
			return NULL;
		}

		if (!empty($meta[self::META_DELTA])) {
			apc_delete($key);
			apc_store($key, $meta, 0, $meta[self::META_DELTA] + time());
		}

		return $meta[self::META_DATA];
	}


	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dp)
	{
		if (!empty($dp[Cache::TAGS]) || isset($dp[Cache::PRIORITY]) || !empty($dp[Cache::ITEMS])) {
			throw new Nette\NotSupportedException('Tags, priority and dependent items are not supported by ApcStorage.');
		}

		$this->_normalizeKey($key);

		$meta = array(
			self::META_DATA => $data,
		);

		$expire = 0;
		if (!empty($dp[Cache::EXPIRE])) {
			$expire = (int) $dp[Cache::EXPIRE];
			if (!empty($dp[Cache::SLIDING])) {
				$meta[self::META_DELTA] = $expire; // sliding time
			}
		}

		if (!empty($dp[Cache::CALLBACKS])) {
			$meta[self::META_CALLBACKS] = $dp[Cache::CALLBACKS];
		}

		apc_store($key, $meta, $expire);
	}


	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		$this->_normalizeKey($key);
		apc_delete($key);
	}


	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conds)
	{
		if (!empty($conds[Cache::ALL])) {
			apc_clear_cache('user');
		} elseif (isset($conds[Cache::TAGS]) || isset($conds[Cache::PRIORITY])) {
			throw new Nette\NotSupportedException('Tags and priority is not supported by ApcStorage.');
		}
	}


	private function _normalizeKey(&$key)
	{
		$key = $this->prefix . str_replace("\x00", '~', $key);
	}


	function lock($key)
	{
		
	}


}

