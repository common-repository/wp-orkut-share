<?php
/*
Plugin Name: WP Orkut Share
Description: WP Orkut Share allows website owners to enable their visitors to share the website content with visitor's orkut friends. Users can optionally promote the content among their friends.
Author: Arun Vishnu
Version: 1.2
Author URI: http://arunmvishnu.com/
*/

if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
define('SEXY_PLUGPATH',WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');

add_action('the_content', 'addOrkutShareButton');
add_action('wp_head', 'includeJs');
add_action('admin_menu', 'wpOrkutShareMenu');

function wpOrkutShareMenu() {
	add_options_page('Orkut Share', 'Orkut Share', 8, __FILE__, 'wpOrkutShareOptions');
}

function includeJs(){    ?>
   <script type="text/javascript" src="http://www.google.com/jsapi"></script>
<?php }

function getPostFirstImageForOrkut() {
	global $post, $posts;
	$firstImg = '';
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
	$firstImg = $matches[1][0];
	return $firstImg;
}

function trimSentenceForOrkut($str, $numChars=0, $force=0, $from=0) {
	mb_internal_encoding("UTF-8");
	if ((mb_strlen($str) <= $numChars) && !$force) {
		return $str;
	}
	if ($numChars == 0) {
		$numChars = mb_strlen($str);
	}
	$str = mb_substr($str, $from, $numChars);
	$pos = mb_strrpos($str, ' '); //gets the last space character
	if (!empty($pos)) {
		$str=mb_substr($str, 0, $pos); //extract characters up to the last space character.
	}
	return $str;
}

function addOrkutShareButton($content){
	global $post;
	if(!$post){
		return $content;
	}
	$shareTitle		= get_the_title($post->post_title);
	$shareTitle = trim(addslashes($post->post_title));
	$summary		= get_the_content("...");
	if($summary){
		$summary = trimSentenceForOrkut(addslashes(strip_tags(strip_shortcodes($summary))),250);
		$summary = trim($summary);
		$summaryCode = "summary: ('{$summary}'),";
	}
	$linkurl		= get_permalink($post->ID);
	$thumbImage = '';
	if(get_option('os_imageMeta')){
		$imgMeta = get_option('os_imageMeta');
		$thumbImage =  get_post_meta($post->ID, $imgMeta, $single = true);
	}
	if(!$thumbImage){
		$thumbImage =  getPostFirstImageForOrkut();
	}
	if($thumbImage){
		$thumbnailCode = "thumbnail: ('{$thumbImage}'),";
	}
	$buttonStyle = 'STYLE_REGULAR';
	if(get_option('os_show_mini_boutton')){
		$buttonStyle = 'STYLE_MINI';
	}
	$os_language = get_option('os_language');
	if(!$os_language){
		$os_language = 'en';
	}
	$shareCode = "<div id=\"orkut-button-{$post->ID}\"></div>
    <script type=\"text/javascript\">
      google.load('orkut.share', '1');
      google.setOnLoadCallback(function() {
        new google.orkut.share.Button({
          lang: '{$os_language}',
          style: google.orkut.share.Button.{$buttonStyle},
          title: '{$shareTitle}',{$summaryCode} {$thumbnailCode}
          destination: '{$linkurl}'
        }).draw('orkut-button-{$post->ID}');
      });
    </script>";
	if(
		(is_home() && !get_option('os_show_index'))
		||
		(is_category() && !get_option('os_show_index'))
		||
		(is_tag() && !get_option('os_show_index'))
		||
		(is_date() && !get_option('os_show_index'))
		||
		(is_author() && !get_option('os_show_index'))
		||
		(is_search() && !get_option('os_show_index'))
		||
		(is_feed() && !get_option('os_show_feed')) 
		||
		(is_page() && !get_option('os_show_pages'))
		||
		(is_single() && !get_option('os_show_posts'))
		 	
	) {
		return $content;
	}
	return $content.$shareCode;
}

function wpOrkutShareOptions() {
	if(isset($_POST['setOsOptions'])) {
		extract($_POST);
		if (!$os_show_posts) { 
			$os_show_posts ='';
		}if (!$os_show_index) { 
			$os_show_index ='';
		}if (!$os_show_feed) { 
			$os_show_feed='';
		}if (!$os_show_pages) { 
			$os_show_pages='';
		}if (!$os_show_mini_boutton) { 
			$os_show_mini_boutton = '';
		}if (!$os_imageMeta) { 
			$os_imageMeta = '';
		}if (!$os_language) { 
			$os_language = '';
		}
		
		update_option('os_show_posts',$os_show_posts);
		update_option('os_show_index',$os_show_index);
		update_option('os_show_feed',$os_show_feed);
		update_option('os_show_pages',$os_show_pages);
		update_option('os_show_mini_boutton',$os_show_mini_boutton);
		update_option('os_imageMeta',trim($os_imageMeta));
		update_option('os_language',trim($os_language));
		$message = "Your settings have been saved.";
	} else {
		$os_show_posts = get_option('os_show_posts');
		$os_show_index = get_option('os_show_index');
		$os_show_feed = get_option('os_show_feed');
		$os_show_pages = get_option('os_show_pages');
		$os_show_mini_boutton = get_option('os_show_mini_boutton');
		$os_imageMeta = get_option('os_imageMeta');
		$os_language = get_option('os_language');
		if(!$os_language){
			$os_language = 'en';
		}
	}
	?>
	<div>
	<script type="text/javascript"><!--
google_ad_client = "pub-2283207803449743";
google_ad_slot = "7289373244";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
	</div>
	<div class="wrap">
		<?php if($message){ ?>
		<h4><b><i><?php echo $message ?></i></b></h4>
		<?php } ?>
		<h2><i>Orkut Share Settings</i></h2>
			<fieldset>
				<form action="" method="post">
					<label>Image meta (The image meta name you used for post thumbnail)</label>
						<input type="text" value="<?php echo $os_imageMeta ?>" name="os_imageMeta"/>
					<br/>
					<label>Language: </label>
					<select name="os_language">
						<option value="en" <?php if($os_language=='en' || $os_language=='') { ?> selected="selected" <?php } ?> >English</option>
						<option value="fr" <?php if($os_language=='fr') { ?> selected="selected" <?php } ?> >French</option>
						<option value="pt_BR" <?php if($os_language=='pt_BR') { ?> selected="selected" <?php } ?> >Portuguese (Brazil)</option>
						<option value="ge" <?php if($os_language=='ge') { ?> selected="selected" <?php } ?> >German</option>
					</select>
					<br />
					<label>
						<input type="checkbox" value="1" <?php if($os_show_mini_boutton) { ?> checked="checked" <?php } ?> name="os_show_mini_boutton"/>
						Display Orkut Share mini button</label>
					<br/>
					<hr >
					<label>
						<input type="checkbox" value="1" <?php if($os_show_posts) { ?> checked="checked" <?php } ?> name="os_show_posts"/>
						Display Orkut Share button at the bottom of posts
					</label><br/>
					<label>
						<input type="checkbox" value="1" <?php if($os_show_index) { ?> checked="checked" <?php } ?> name="os_show_index"/>
						Display Orkut Share button at the bottom of posts on the front page</label><br/>
					<label>
						<input type="checkbox" value="1" <?php if($os_show_feed) { ?> checked="checked" <?php } ?> name="os_show_feed"/>
						Display Orkut Share button at the bottom of posts in the feed				</label><br/>
					<label>
						<input type="checkbox" value="1" <?php if($os_show_pages) { ?> checked="checked" <?php } ?> name="os_show_pages"/>
						Display Orkut Share button at the bottom of pages</label>
					<br/>
					<br/>
					<p><input type="submit" value="Save" /></p>
					<input type="hidden" name="setOsOptions" value="1" />
				</form>
			</fieldset>
	</div>
	<script type="text/javascript"><!--
google_ad_client = "pub-2283207803449743";
google_ad_slot = "8227114043";
google_ad_width = 336;
google_ad_height = 280;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
	<?php
	}
?>