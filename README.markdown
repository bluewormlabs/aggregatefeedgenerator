Aggregate Feed Generator
========================
About
-----
One of our clients has multiple blogs that they wanted a 
feed that combined the posts from all of those blogs.

To do this, we used the [SimplePie][1] library, which is 
used by WordPress to deal with feeds, and pulled in all 
three feeds then generated a new one.

The result is a small (currently, under 200 lines with 
comments) script that pulls in any number of feeds and 
generates a feed for them.

Supported input types: [any supported by SimplePie][4]  
Supported output types: [RSS 2.0][2], [Atom][3]  
License: [zlib/libpng license][5]

Installation
------------
### Requirements
+ PHP 5 (tested with 5.3.2)
+ [SimplePie][1] (tested with 1.2)

### Install
Place `feed.php` and `simplepie.inc` into a web-accessible 
directory.

### Configuration
Configuration is done directly in `feed.php`. All 
configuration is in a clearly marked area; modify (or 
fill, where applicable) values in the `define` calls and 
specify your feeds in `$feedUrls`.

### Use
1. The URLs to the feeds are the path to `feed.php` followed 
   by `?type=rss2` or `?type=atom`
2. To have browsers detect the feeds, place the following 
   (replacing `URL`) into your <head>:
      
      > &lt;link rel="alternate" type="application/rss+xml"  href="`URL`?type=rss2" title="My Blogs (RSS)"&gt;  
      > &lt;link rel="alternate" type="application/atom+xml"  href="`URL`?type=atom" title="My Blogs (Atom)"&gt;

[1]: http://simplepie.org
[2]: http://en.wikipedia.org/wiki/RSS
[3]: http://en.wikipedia.org/wiki/Atom_(standard)
[4]: http://simplepie.org/wiki/faq/what_versions_of_rss_or_atom_do_you_support
[5]: http://opensource.org/licenses/zlib-license
