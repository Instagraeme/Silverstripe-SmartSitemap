<?php

class SmartSitemapDecorator extends DataObjectDecorator {

	function extraStatics() {
		return array(
			'db'		=> array(
				'ExcludeFromHTML'	=> 'Boolean',
				'ExcludeFromXML'	=> 'Boolean',
				'Priority'			=> 'Float'
			)
		);
	}

	function updateCMSFields(&$fields) {
		$pagePriorities = array(
			''		=> _t('SiteTree.PRIORITYAUTOSET','Auto-set based on page depth'),
			//	We set this to negative one because a blank value implies auto-generation of Priority
			'-1'	=> _t('SiteTree.PRIORITYNOTINDEXED', "Not indexed"),
			'1.0'	=> '1 - ' . _t('SiteTree.PRIORITYMOSTIMPORTANT', "Most important"),
			'0.9'	=> '2',
			'0.8'	=> '3',
			'0.7'	=> '4',
			'0.6'	=> '5',
			'0.5'	=> '6',
			'0.4'	=> '7',
			'0.3'	=> '8',
			'0.2'	=> '9',
			'0.1'	=> '10 - ' . _t('SiteTree.PRIORITYLEASTIMPORTANT', "Least important")
		);

		$content_field = $fields->dataFieldByName('Content');
		if ($content_field instanceof Tab) {
			$tabset = $fields->findOrMakeTab('Root.Content');
		}
		else {
			$tabset = $fields->findOrMakeTab('Root');
		}
		$tabset->push(
			$addTab = new Tab(
				'Sitemap',
				_t('SmartSitemap.SITEMAP', 'Sitemap'),
				new LiteralField(
					'SitemapIntro', 
					'<p>' . _t('SmartSitemap.PRIORITYNOTE', 'Manually specify a relative Priority for this page') . '</p>'
				),
				new DropdownField('Priority', $this->owner->fieldLabel('Priority'), $pagePriorities),
				new CheckboxField('ExcludeFromXML', _t('SmartSitemap.EXCLUDEFROMXML', 'Exclude From XML Sitemap'))
			)
		);

		//	if there is a SmartSitemapPage being used on the site, allow exclusion per page
		if (DataObject::get_one('SiteTree', "`ClassName` = 'SmartSitemapPage'")) {
			$addTab->insertBefore(
				new CheckboxField('ExcludeFromHTML', _t('SmartSitemap.EXCLUDEFROMHTML', 'Exclude From HTML Sitemap')),
				'ExcludeFromXML'
			);
		}

		$this->owner->extend('updateSitemapCMSFields', $fields);
	}

	function updateFieldLabels(&$labels) {
		parent::updateFieldLabels($labels);

		$labels['Priority'] = _t('SmartSitemap.PAGEPRIORITY', 'Page Priority');
	}

	function onAfterPublish() {
		foreach (SmartSitemap::get_search_engine_notification() as $engine => $value) {
			if ($value['enabled'])
				SmartSitemap::ping($engine);
		}
	}

	function onAfterUnpublish() {
		foreach (SmartSitemap::get_search_engine_notification() as $engine => $value) {
			if ($value['enabled'])
				SmartSitemap::ping($engine);
		}
	}

	/**
	 * The default value of the priority field depends on the depth of the page in
	 * the site tree, so it must be calculated dynamically.
	 */
	function getPriority() {
		//	objects w/ Hierarchy
		if (! $this->owner->getField('Priority') && $this->owner->hasMethod('parentStack')) {
			$parentStack = $this->owner->parentStack();
			$numParents = is_array($parentStack) ? count($parentStack) - 1: 0;
			return max(0.1, 1.0 - ($numParents / 10));
		}
		//	objects w/o Hierarchy
		elseif (! $this->owner->getField('Priority')) {
			return 1;
		}
		//	not indexed
		elseif (-1 == $this->owner->getField('Priority')) {
			return 0;
		}
		else {
			return $this->owner->getField('Priority');
		}
	}

	/**
	 * Return children from the stage site for an html sitemap
	 * 
	 * @return DataObjectSet
	 */
	public function SitemapChildren($showAll = false) {
		$extraFilter = " AND \"ShowInMenus\" = 1 AND \"ExcludeFromHTML\" != 1";

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$sitemap = DataObject::get($baseClass, "\"{$baseClass}\".\"ParentID\" = " 
			. (int)$this->owner->ID . " AND \"{$baseClass}\".\"ID\" != " . (int)$this->owner->ID
			. $extraFilter, "");

		if (! $sitemap)
			$sitemap = new DataObjectSet();
		$this->owner->extend("augmentSitemapChildren", $sitemap, $showAll);

		return $sitemap;
	}

}

