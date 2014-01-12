<?php
/*
Plugin Name: Front Page News
Plugin URI: http://gerrytucker.co.uk/wordpress/plugins/tmpco/front-page-news
Description: This plugin allows the The Mayfair Printing Co. to create posts for the frontpage news accordion and select and order those posts using a drag'n'drop interface.
Author: Gerry Tucker
Author URI: http://gerrytucker.co.uk/
Version: 1.3
GitHub Plugin URI: https://github.com/gerrytucker/tMPCo-front-page-news
GitHub Branch: master
*/
register_activation_hook(__FILE__, 'fpn_activation');
function fpn_activation()
{
}


register_deactivation_hook(__FILE__, 'fpn_deactivation');
function fpn_deactivation()
{
}


function fpn_enqueue_scripts()
{
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('json2');
	wp_enqueue_script(
		'front-page-news-js',
		plugins_url('js/front-page-news.js', __FILE__),
		false,
		'1.0',
		true
	);

	$fpn_data = array(
		'admin_url'		 => admin_url('admin-ajax.php')
	);
	
	wp_localize_script(
		'front-page-news-js',
		'fpn_data',
		$fpn_data
	);

}


function fpn_enqueue_styles()
{
	global $wp_scripts;
	$queryui = $wp_scripts->query('jquery-ui-core');
	$url = "http://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/blitzer/jquery-ui.css";
	wp_enqueue_style(
		'jquery-sunny',
		$url,
		false,
		null
	);
	wp_enqueue_style(
		'front-page-news-css',
		plugins_url('css/front-page-news.css', __FILE__),
		false,
		'1.0'
	);
}


add_action('admin_menu', 'fpn_settings');
function fpn_settings()
{
	add_submenu_page(
		'edit.php?post_type=news',
		'Select & Sort News',
		'Select & Sort News',
		'edit_posts',
		basename(__FILE__),
		'select_and_sort_news'
	);
}


function fpn_scripts_and_styles($hook)
{
	if ($hook !== "news_page_front-page-news")
		return;

	fpn_enqueue_scripts();
	fpn_enqueue_styles();
}
add_action('admin_enqueue_scripts', 'fpn_scripts_and_styles');


function select_and_sort_news()
{
	$fpn_news_option = json_decode(get_option('front-page-news'), true);
	if ($fpn_news_option == NULL)
	{
		$fpn_news_option = array();
	}

	$fpn_news = array(); $i = 0;
	
	foreach($fpn_news_option as $news_id)
	{
		$post = get_post(intval($news_id));
		$fpn_news[$i]['id'] = $post->ID;
		$fpn_news[$i]['title'] = $post->post_title;
		$fpn_news[$i]['date'] = get_the_time('l, F jS Y', $post->ID);
		$i++;
	}

	$posts = get_posts(array(
		'posts_per_page' 		=> -1,
		'post_type'				 => 'news',
		'post_status'			 => 'publish',
		'order'						 => 'DESC',
		'orderby'					 => 'date',
		'suppress_filters'	=> false
		)
	);

	$all_news = array(); $i=0;
	foreach($posts as $post)
	{
		if (!in_array($post->ID, $fpn_news_option))
		{
			$all_news[$i]['id'] = $post->ID;
			$all_news[$i]['title'] = $post->post_title;
			$all_news[$i]['date'] = get_the_time('l, F jS Y', $post->ID);
			$i++;
		}
	}

	require_once('Mustache/Autoloader.php');
	Mustache_Autoloader::register();
	
	$m = new Mustache_Engine(array(
		'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/views')
	));
	
	$tpl = $m->loadTemplate('front-page-news-settings');
	echo $tpl->render(array('all_news' => $all_news, 'fpn_news' => $fpn_news));
}


function fpn_save_news_action()
{
	global $wpdb;
	
	$news_ids = $_POST['news_ids'];
	$output = json_encode($news_ids);
	delete_option('front-page-news');
	add_option('front-page-news', $output, '', 'yes');
	
	header('Content-Type: application/json');
	echo json_encode(array('status' => 'ok', 'ids' => json_encode($news_ids)));
	die();
}
add_action('wp_ajax_fpn_save_news_action', 'fpn_save_news_action');
add_action('wp_ajax_nopriv_fpn_save_news_action', 'fpn_save_news_action');


function fpn_show_news()
{
	$news_ids = json_decode(get_option('front-page-news'), true);
	$news = array(); $i = 0;
	
	foreach ($news_ids as $post_id)
	{
		$post = get_post(intval($post_id));
		$news[$i]['title'] = $post->post_title;
		$news[$i]['content'] = wpautop($post->post_content);
		$news[$i]['image'] = array();
		$thumbnail_id = get_post_thumbnail_id($post->ID);
		if ($thumbnail_id) {
			if ($img = wp_get_attachment_image_src($thumbnail_id, 'news-large')) {
				$news[$i]['image'] = array(
					'src' => $img[0],
					'width' => $img[1],
					'height' => $img[2],
					'align' => 'top'
				);
			}
		}
		if ($i == 0) {
			$news[$i]['first'] = true;
		}
		$i++;
	}

	require_once('Mustache/Autoloader.php');
	Mustache_Autoloader::register();
	
	$m = new Mustache_Engine(array(
		'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/views')
	));
	
	$tpl = $m->loadTemplate('front-page-news');
	return $tpl->render(array('news' => $news));

}
function fpn_register_shortcodes()
{
	add_shortcode('fpn-show-news', 'fpn_show_news');
}
add_action('init', 'fpn_register_shortcodes');
