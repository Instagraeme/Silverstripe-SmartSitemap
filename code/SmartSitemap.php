<?php

class SmartSitemap extends Controller {

	/**
	 * @var boolean
	 */
	protected static $enabled = true;
	protected static $can_ping = true;
	protected static $styleSheet = true;

	/**
	 * @var DataObjectSet
	 */
	protected $Pages;

	protected static $search_engine_notification = array(
		'ask'		=> array(
			'enabled'	=> true,
			'host'		=> 'submissions.ask.com',
			'path'		=> 'ping',
			'query'		=> 'sitemap='
		),
		'bing'		=> array(
			'enabled'	=> true,
			'host'		=> 'www.bing.com',
			'path'		=> 'webmaster/ping.aspx',
			'query'		=> 'siteMap='
		),
		'google'	=> array(
			'enabled'	=> true,
			'host'		=> 'www.google.com',
			'path'		=> 'webmasters/tools/ping',
			'query'		=> 'sitemap='
		),
		'yahoo'		=> array(
			'enabled'	=> true,
			'host'		=> 'search.yahooapis.com',
			'path'		=> 'SiteExplorerService/V1/ping',
			'query'		=> 'sitemap='
		)
	);

	//	@param	$engine		string			- if in system adjust settings, otherwise add to list
	//	@param	$settings - boolean|array	- if Boolean adjust enabled, otherwise merge settings
	static function set_search_engine_notification($engine, $settings) {
		if (isset(self::$search_engine_notification["$engine"])) {
			if (is_bool($settings)) {
				self::$search_engine_notification["$engine"]['enabled'] = $settings;
				return;
			}
		}
		else {
			self::$search_engine_notification["$engine"] = array(
				'enabled'	=> false,
				'host'		=> '',
				'path'		=> '',
				'query'		=> ''
			);
		}
		if (is_array($settings)) {
			self::$search_engine_notification["$engine"] = $settings;
		}
	}

	static function get_search_engine_notification($engine = null) {
		if (null === $engine)
			return self::$search_engine_notification;
		if (isset(self::$search_engine_notification["$engine"]))
			return self::$search_engine_notification["$engine"];

		return false;
	}

	public function Items() {
		$this->Pages = Versioned::get_by_stage('SiteTree', 'Live', '', 'ClassName ASC');
		$SitemapItems = new ArrayList();

		$now_seconds = date('U');
		foreach($this->Pages as $page) {
			if ('ErrorPage' == $page->ClassName)
				continue;

			if ($page->ExcludeFromXML)
				continue;

			//We prefix $_SERVER['HTTP_HOST'] with 'http://' so that parse_url to help parse_url identify the host name component; we could use another protocol (like 
			// 'ftp://' as the prefix and the code would work the same. 
			if (parse_url($page->AbsoluteLink(), PHP_URL_HOST) == parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST)) {
				if ($page->canView()) {
					$created_seconds = date('U', strtotime($page->Created));
					$timediff = $now_seconds - $created_seconds;

					$page->ChangeFreq = $this->change_frequency($timediff, $page->Version + 1);
					$SitemapItems->push($page);
				}
			}
		}

		$this->extend('updateSitemapItems', $SitemapItems);

		return $SitemapItems;
	}

	function change_frequency($timediff, $versions) {
		//	Check how many revisions have been made over the lifetime of the
		//	Page/Object for a rough estimate of it's changing frequency.

		$period = $timediff / ($versions + 1);

		if ($period > 31536000) {		//	> 1 year	(60*60*24*365)
			$change_freq = 'yearly';
		}
		elseif ($period > 2592000) {	//	> ~1 month	(60*60*24*30)
			$change_freq = 'monthly';
		}
		elseif ($period > 604800) {		//	> 1 week	(60*60*24*7)
			$change_freq = 'weekly';
		}
		elseif ($period > 86400) {		//	> 1 day		(60*60*24)
			$change_freq = 'daily';
		}
		elseif ($period > 3600) {		//	> 1 hour	(60*60)
			$change_freq = 'hourly';
		}
		else {							//	< 1 hour
			$change_freq = 'always';
		}

		return $change_freq;
	}

	/**
	 * Notifies Search Engine about changes to your sitemap.
	 * Triggered automatically on every publish/unpublish of a page.
	 * This behaviour is disabled by default, enable with:
	 * GoogleSitemap::enable_google_notificaton();
	 * 
	 * If the site is in "dev-mode", no ping will be sent regardless wether
	 * the Google notification is enabled.
	 * 
	 * @return string Response text
	 */
	static function ping($engine) {
		if (! self::$enabled || ! self::$can_ping)
			return false;

		$sen = self::get_search_engine_notification($engine);

		//	Don't ping if the site has disabled it, or if the site is in dev mode
		if ((! $sen['enabled']) || Director::isDev())
			return;

		$location = urlencode(Director::absoluteBaseURL() . '/sitemap.xml');

		$response = HTTP::sendRequest($sen['host'], $sen['path'], $sen['query'] . $location);

		return $response;
	}

	function index($url) {
		if (self::$enabled) {
			SSViewer::set_source_file_comments(false);
			// We need to override the default content-type
			ContentNegotiator::disable();
			$this->getResponse()->addHeader('Content-Type', 'application/xml; charset="utf-8"');

			// But we want to still render.
			return array();
		} else {
			return new SS_HTTPResponse('Not allowed', 405);
		}
	}

	static function styleSheet() {
		if (! self::$enabled || ! self::$styleSheet)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public static function enable() {
		self::$enabled = true;
	}

	public static function disable() {
		self::$enabled = false;
	}

	public static function enable_ping() {
		self::$can_ping = true;
	}

	public static function disable_ping() {
		self::$can_ping = false;
	}

	public static function enableStyleSheet() {
		self::$styleSheet = true;
	}

	public static function disableStyleSheet() {
		self::$styleSheet = false;
	}
}

