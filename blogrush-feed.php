<?php
if (empty($wp)) {
	$bcm_abs_path = dirname( __FILE__ );
	$bcm_path     = str_replace("\\", "/", strstr($bcm_abs_path, 'wp-content'));
	$count        = substr_count(trim($bcm_path, '/'), '/');
	$_bcm_path    = '';
	if ( $count > 0 )
		for ($i=0; $i<=$count; $i++)
			$_bcm_path .= "../";
						
	require_once($_bcm_path.'wp-config.php');
	wp('feed=rss2');
}

header('Content-type: text/xml; charset=' . get_option('blog_charset'), true);
$more = 1;
?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	<?php do_action('rss2_ns'); ?>
>

<channel>
<title><?php bloginfo_rss('name'); ?></title>
<link><?php bloginfo_rss('url') ?></link>
<description><?php bloginfo_rss("description") ?></description>
<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></pubDate>
<generator>http://wordpress.org/?v=<?php bloginfo_rss('version'); ?></generator>
<language><?php echo get_option('rss_language'); ?></language>
<?php do_action('rss2_head'); ?>
<?php query_posts(p<>0) ?>
<?php $post_cnt = 0; ?>
<?php while (have_posts()) : the_post(); ?>
	<?php $post_cnt++;if ($post_cnt<=10) : ?>
	<?php
	  $bcm_include = get_post_meta($post->ID, 'bcm_include', true);
	  $bcm_title   = get_post_meta($post->ID, 'bcm_title', true);
	  if ( strlen(trim($bcm_title)) <= 0 ) {
		  $bcm_title = the_title('','',false);
	  }
	  $bcm_title   = apply_filters('the_title', $bcm_title);
	  $bcm_title   = apply_filters('the_title_rss', $bcm_title);
	?>
	<?php if ( $bcm_include == 'true' || $bcm_include == '' ) : //continue; ?>
	<item>
		<title><?php echo $bcm_title; ?></title>
		<link><?php permalink_single_rss() ?></link>
		<comments><?php comments_link(); ?></comments>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
		<dc:creator><?php the_author() ?></dc:creator>
		<?php the_category_rss() ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php if (get_option('rss_use_excerpt')) : ?>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
		<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
		<?php if ( strlen( $post->post_content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php the_content() ?>]]></content:encoded>
		<?php else : ?>
		<content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
		<?php endif; ?>
		<?php endif; ?>
		<wfw:commentRss><?php echo comments_rss(); ?></wfw:commentRss>
		<?php rss_enclosure(); ?>
		<?php do_action('rss2_item'); ?>
	</item>
	<?php endif; ?>
	<?php endif; ?>
<?php endwhile; ?>
</channel>
</rss>