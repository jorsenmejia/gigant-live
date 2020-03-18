<?php
/*
Template Name: VideoWhisper App - Full Page
*/
?><!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no"/>
		<link rel="manifest" href="./manifest.json"/>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
<?php
		$CSSfiles = scandir(dirname(  __FILE__ ) . '/static/css/');
		foreach($CSSfiles as $filename)
			if (strpos($filename,'.css')&&!strpos($filename,'.css.map'))
				echo '<link rel="stylesheet" href="'. plugin_dir_url(  __FILE__ ) . '/static/css/' . $filename . '">';
?>
	  	<link href="https://vjs.zencdn.net/7.6.5/video-js.css" rel="stylesheet">
  		<title><?php _e('Video Chat','ppv-live-webcams'); ?></title>

		<body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<?php
if ( have_posts() ) : while ( have_posts() ) : the_post();
the_content();
endwhile; else: echo 'No content: ' . get_the_ID(); endif; 

		$JSfiles = scandir(dirname(  __FILE__ ) . '/static/js/');
		foreach ($JSfiles as $filename)
			if ( strpos($filename,'.js') && !strpos($filename,'.js.map')) // && !strstr($filename,'runtime~')
				echo '<script type="text/javascript" src="' . plugin_dir_url(  __FILE__ ) . '/static/js/' . $filename . '"></script>';
?>
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
<script src='https://vjs.zencdn.net/7.6.5/video.js'></script>