<?php

if (class_exists('GoogleSitemapDecorator'))
	Object::remove_extension('SiteTree', 'GoogleSitemapDecorator');

DataObject::add_extension('SiteTree', 'SmartSitemapDecorator');

Director::addRules(15, array(
	'sitemap.xml'	=> 'SmartSitemap',
));



//	SmartSitemap::enable() / ::disable()
//	SmartSitemap::enable_ping() / ::disable_ping()

/*
	//	Control Search Engine Settings

	SmartSitemap::set_search_engine_notification('ask', false);

	SmartSitemap::set_search_engine_notification('yahoo', array());

	SmartSitemap::set_search_engine_notification('<new_engine>', array(
		'enabled'	=> true,
		'host'		=> 'www.example.com',
		'path'		=> 'path_for_submissions',
		'query'		=> 'sitemap_query='
	));
