<?php
/*
	Copyright (c) 2013 Blue Worm Labs LLC

	This software is provided 'as-is', without any express or implied
	warranty. In no event will the authors be held liable for any damages
	arising from the use of this software.

	Permission is granted to anyone to use this software for any purpose,
	including commercial applications, and to alter it and redistribute it
	freely, subject to the following restrictions:

	   1. The origin of this software must not be misrepresented; you must not
	      claim that you wrote the original software. If you use this software
	      in a product, an acknowledgment in the product documentation would be
	      appreciated but is not required.
   
	   2. Altered source versions must be plainly marked as such, and must not be
	      misrepresented as being the original software.
   
	   3. This notice may not be removed or altered from any source
	      distribution.
*/

// ================ START CONFIGURATION ================
define('FEED_TITLE', 'My Aggregated Feeds'); // title of the new feed
define('FEED_LINK', 'http://example.com'); // url to set as a link for the new feed
define('FEED_DESC', 'Multiple feeds in one!'); // description of the new feed
define('FEED_LANG', 'en-US'); // language for the feed
define('FEED_COPY', 'Copyright (C) 2013'); // copyright string
define('FEED_IMG_URL', ''); // URL to an image file (~favicon) [RSS]
define('FEED_IMG_LINK', ''); // URL to link the image to [RSS]
define('FEED_URL', "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); // url of the feed, incl. ?type=X
define('ATOM_TAG_PREFIX', 'tag:example.com,2011:'); // prefix for <id> elements in the Atom feed
define('SIMPLEPIE', dirname($_SERVER['SCRIPT_FILENAME']) . '/simplepie.inc'); // path to the SimplePie file
define('TRUNCATE_LEN', -1); // -1 to disable, >= 0 for the number of chars to truncate story text to; be wary of HTML posts+truncating
define('TRUNCATE_READMORE', 'Read More'); // text to link to the full post if truncated

// Links to RSS/Atom feeds; replace these with the feeds you want combined
$feedUrls = array(
		'http://rss.slashdot.org/Slashdot/slashdot',
		'http://xkcd.com/atom.xml',
		'http://www.postsecret.com/feeds/posts/default',
		'http://events.unl.edu/cse/upcoming/?format=rss',
	);
// ================  END CONFIGURATION  ================












// Include the Simple Pie library; turn off error reporting as PHP will spit out a ton
// of deprecation warnings and the like here which makes the output beyond invalid
error_reporting(0);
require_once(SIMPLEPIE);

// Create a new SimplePie object for the combind feeds
$feed = new SimplePie();
$feed->set_feed_url($feedUrls);
$feed->init();
$feed->handle_content_type();


// Determine what type of feed we're spitting out
$type = strtolower($_GET['type']);


// Display the feed
if ($type == 'rss' || $type == 'rss2') {
	// RSS 2.0
	header("Content-Type: application/rss+xml; charset=utf-8");
	
	// Preamble
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	echo "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
	echo "\t<channel>\n";
	
	// Blog information
	echo "\t\t<title>" . FEED_TITLE . "</title>\n";
	echo "\t\t<link>" . FEED_LINK . "</link>\n";
	echo "\t\t<description>" . FEED_DESC . "</description>\n";
	echo "\t\t<language>" . FEED_LANG . "</language>\n";
	echo "\t\t<copyright>" . FEED_COPY . "</copyright>\n";
	echo "\t\t<atom:link href=\"" . FEED_URL . "\" rel=\"self\" type=\"application/rss+xml\" />\n";
	
	if (strlen(FEED_IMG_URL) > 0 && strlen(FEED_IMG_LINK) > 0) {
		echo "\t\t<image>\n";
		echo "\t\t\t<url>" . FEED_IMG_URL . "</url>\n";
		echo "\t\t\t<link>" . FEED_IMG_LINK . "</link>\n";
		echo "\t\t</image>\n";
	}
	
	// Posts
	foreach ($feed->get_items() as $item) {
		$feed = $item->get_feed(); // the specific feed for this post
		$content = trim($item->get_description()); // content for this post
		
		if (TRUNCATE_LEN > -1 && strlen($content) > TRUNCATE_LEN) {
			$postLen = TRUNCATE_LEN - strlen('... ') - strlen(TRUNCATE_READMORE);
			$content = substr($content, 0, $postLen);
			$content .= '... <a href="' . $item->get_permalink() . '">' . TRUNCATE_READMORE . '</a>';
		}
		
		echo "\t\t<item>\n";
		echo "\t\t\t<guid isPermaLink=\"true\">" . $item->get_permalink() . "</guid>\n";
		echo "\t\t\t<title>" . $item->get_title() . "</title>\n";
		echo "\t\t\t<description><![CDATA[" . $content . "]]></description>\n";
		echo "\t\t\t<link>" . $item->get_permalink() . "</link>\n";
		echo "\t\t\t<pubDate>" . $item->get_date('D, d M Y H:i:s O') . "</pubDate>\n";
		echo "\t\t</item>\n";
	}
	
	echo "\t</channel>\n";
	echo "</rss>\n";
}
else if ($type == 'atom') {
	// Atom
	header("Content-Type: application/atom+xml; charset=utf-8");
	
	// Preamble
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	echo "<feed xml:lang=\"" . FEED_LANG . "\" xmlns=\"http://www.w3.org/2005/Atom\">\n";
	
	// Blog information
	echo "\t<title>" . FEED_TITLE . "</title>\n";
	echo "\t<subtitle>" . FEED_DESC . "</subtitle>\n";
	echo "\t<link href=\"" . FEED_URL . "\" rel=\"self\" />\n";
	echo "\t<updated>" . date('c') . "</updated>\n";
	echo "\t<id>" . ATOM_TAG_PREFIX . FEED_URL . "</id>\n";
	
	// Posts
	foreach ($feed->get_items() as $item) {
		$feed = $item->get_feed(); // the specific feed for this post
		$content = trim($item->get_description()); // content for this post
		
		if (TRUNCATE_LEN > -1 && strlen($content) > TRUNCATE_LEN) {
			$postLen = TRUNCATE_LEN - strlen('... ') - strlen(TRUNCATE_READMORE);
			$content = substr($content, 0, $postLen);
			$content .= '... <a href="' . $item->get_permalink() . '">' . TRUNCATE_READMORE . '</a>';
		}
		
		// Determine whether the title and content have any HTML
		$titleHtml = ($item->get_title() != strip_tags($item->get_title())) ? true : false;
		$titleHex = (strpos($item->get_title(), '&#')) ? true : false;
		$contentHtml = ($content != strip_tags($item->get_title())) ? true : false;
		$contentHex = (strpos($content, '&#')) ? true : false;
		
		$titleType = ($titleHtml || $titleHex) ? ' type="html"' : '';
		$contentType = ($contentHtml || $contentHex) ? ' type="html"' : '';
		
		echo "\t<entry>\n";
		echo "\t\t<title" . $titleType . ">" . $item->get_title() . "</title>\n";
		echo "\t\t<link type=\"text/html\" href=\"" . $item->get_permalink() . "\" />\n";
		echo "\t\t<id>" . ATOM_TAG_PREFIX . $item->get_permalink() . "</id>\n";
		echo "\t\t<updated>" . $item->get_date('c') . "</updated>\n";
		echo "\t\t<author>\n";
		echo "\t\t\t<name>" . $item->get_author()->get_name() . "</name>\n";
		echo "\t\t</author>\n";
		echo "\t\t<summary" . $contentType . "><![CDATA[" . $content . "]]></summary>\n";
		echo "\t</entry>\n";
	}
	
	echo "</feed>\n";
}
else {
	// Error
	$baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	echo 'Error. Did you specify an incorrect type? Supported: <a href="' . $baseUrl . '?type=rss2">RSS</a> 2.0, ';
	echo '<a href="' . $baseUrl . '?type=atom">Atom</a>.';
}

?>
