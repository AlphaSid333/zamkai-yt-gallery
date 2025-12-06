<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	require_once plugin_dir_path( __FILE__ ) . '../zamkai-video-gallery-for-youtube.php';

	echo do_shortcode( '[zamkai_yt_gallery]' );
