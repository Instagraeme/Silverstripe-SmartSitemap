<?php

class SmartSitemapPage extends Page {
}


class SmartSitemapPage_Controller extends Page_Controller {

	function init() {
		parent::init();

		Requirements::themedCSS('SmartSitemap');
	}

	function SitemapItems() {
		$smartsitemap = new SmartSitemap();

		$obj = singleton('SiteTree');
		$obj->markPartialTree(10);

		$eval = '"<li id=\"selector-' . $this->name . '-$child->ID\" class=\"$child->class closed" . ($child->isExpanded() ? "" : " unexpanded") . "\"><a>" . $child->Title . "</a>"';

		return $obj->getChildrenAsUL("class=\"tree\"", $eval, null, true, 'SitemapChildren');
	}

}

