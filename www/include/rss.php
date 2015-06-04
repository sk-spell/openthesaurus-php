<?php
if( isset($db) ) {
	# called from index.php, don't re-initialise vars
} else {
	# called from cronjob
	include("phplib/prepend.php3");
	include("tool.php");	
	$db = new DB_Thesaurus;
}

include("feedcreator.class.php"); 

$rss = new UniversalFeedCreator(); 
$rss->useCached(); 
$rss->title = _("Recent changes in the thesaurus"); 
$rss->description = _("Recent changes in the thesaurus");

//optional
//$rss->descriptionTruncSize = 500;
//$rss->descriptionHtmlSyndicated = true;
//$rss->xslStyleSheet = "http://feedster.com/rss20.xsl";

$rss->cssStyleSheet 	= NULL;
$rss->link = HOMEPAGE."changes.php"; 
$rss->feedURL = HOMEPAGE."feed.xml"; 

$image = new FeedImage(); 
$image->title = "OpenThesaurus"; 
$image->url = HOMEPAGE."favicon.png"; 
$image->link = HOMEPAGE; 
$image->description = _("Recent changes in the thesaurus");

//optional
//$image->descriptionTruncSize = 500;
//$image->descriptionHtmlSyndicated = true;

$rss->image = $image; 

$actions_limit = 200;
$query = sprintf("SELECT id, user_actions_log.user_id, visiblename, date,
		word, synset, synset_id, type, comment
	FROM user_actions_log, auth_user
	WHERE
		auth_user.user_id = user_actions_log.user_id			
   	ORDER BY date DESC
	LIMIT %d", $actions_limit);

# filter out admin actions:	WHERE user_id != 1
$db->query($query);
$prev_user = "_start";
$prev_date = "_start";

while( $db->next_record() ) {
	$username = escape($db->f('visiblename'));
	if( $username == "" || $username == _("(anonymous)") ) {
		$username = "<span class=\"anonymous\">"._("(anonymous)")."</span>";
	}
	$date = $db->f('date');
	$prev_date = $db->f('date');
	$prev_user = $db->f('user_id');
	$date = str_replace(" ", "&nbsp;", $db->f('date'));
	$comment = $db->f('comment');
	if( ! $comment ) {
		$comment = _("[none]");
	}
	$msg = "";
	$msg = getChangeEntry($db->f('type'), $db->f('word'),
			$db->f('synset_id'), $db->f('synset'), $comment, HOMEPAGE, 1);
	$link = ereg_replace("^.*href=\"", "", $msg);
	$link = ereg_replace("\".*$", "", $link);
	$msg = ereg_replace("class=\"removed\"","style=\"font-weight: bold; text-decoration: line-through; color: red;\"",
		$msg);
	$msg = ereg_replace("class=\"added\"","style=\"font-weight: bold; color: green;\"",
		$msg);
	$item = new FeedItem(); 

	# use this line to add the username:
	$item->title = $date." ".$username." ".$msg; 

	$item->link = $link; 
	# use this line to add the username:
	$item->description = $date." ".$username." ".$msg; 
	
	//optional
	//$item->descriptionTruncSize = 500;
	$item->descriptionHtmlSyndicated = true;
	
	$item->date = time(); 
	$item->source = HOMEPAGE; 
	$item->author = $username; 
	 
	$rss->addItem($item); 
}

// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1, MBOX, OPML, ATOM0.3, HTML, JS
$rss->saveFeed("RSS0.91", "../www/feed.xml", false); 

?>
