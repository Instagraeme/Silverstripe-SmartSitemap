## SmartSitemap

Modified for SilverStripe 3.0 by Graeme Smith

## Additional Functionality

Setting SmartSitemap::enableStyleSheet() in _config will include /sitemap.xsl in /sitemap.xml  See http://yoast.com/xsl-stylesheet-xml-sitemap/ for more info.

## Original README:

* module                 SmartSitemap
* version                0.1.6
* developer              BLU42 Media LLC <blu42media@gmail.com>
* compatibility  SS 2.3, 2.4
* depends                []


##OVERVIEW

SmartSitemap is a clone of SilverStripe's GoogleSitemap with these additional features:

* Items() function can be extended to work with SmartMeta-related objects to get them into the SiteMap
* took multiplication out of ChangeFreq comparisons
* took $now out of foreach loop
* Pings:
  * Ask.com
  * Bing
  * Google
  * Yahoo!

## USAGE

* Install SmartSitemap to your root directory and run dev/build?flush=all
* By default, site will ping only if in live mode, engine submission is enabled, and SmartSitemap::disable_ping() is not set


## VERSION HISTORY

0.1.6           UPDATE RELEASE (May 28, 2011)
                        - added SmartSitemapPage for displaying traditional HTML Sitemap pages
                        - added ExcludeFromHTML and ExcludeFromXML fields to Decorator
                        - added SitemapChildren to Decorator
                        - added $can_ping

0.1.5           BUGFIX RELEASE (March 23, 2011)
                        - SS_HTTPResponse class call
                        - documentation

0.1.4           BUGFIX RELEASE (July 15, 2010)
                        - now works with hierarchical urls

0.1.3           BUGFIX RELEASE (June 28, 2010)
                        - removed unused Google-specific functions

0.1.2           FEATURE RELEASE (May 27, 2010)
                        - improved flexibility of Sitemap Admin tab

0.1.1           BUGFIX RELEASE (May 15, 2010)
                        - added remove_extension('GoogleSitemapDecorator') to avoid conflicts

0.1.0           INITIAL RELEASE


