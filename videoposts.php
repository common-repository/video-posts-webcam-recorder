<?php
/*
Plugin Name: Webcam Microphone Screen Recorder - HTML5 Web Based
Plugin URI: https://videowhisper.com/?p=WordPress+Video+Recorder+Posts+Comments
Description: <strong>Webcam Microphone Screen Recorder - HTML5 Web Based</strong> allows WordPress authors to record and insert video/audio in their posts. Integrates with Media Gallery and frontend plugins like VideoShareVOD, MicroPayments for advanced video management, access control including paid access and membership.
Version: 3.3.12
Requires PHP: 7.4
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once plugin_dir_path( __FILE__ ) . '/inc/options.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/h5videochat.php';

use VideoWhisper\Recorder;


if ( ! class_exists( 'VWvideoPosts' ) ) {
	class VWvideoPosts {


		use VideoWhisper\Recorder\Options;
		use VideoWhisper\Recorder\H5Videochat;

		public function __construct() {         }


		function VWvideoPosts() {
			// constructor
			self::__construct();
		}


		function shortcode_recorder( $atts ) {
			$options = get_option( 'VWvideoRecorderOptions' );

			if ( class_exists( 'VWvideoShare' ) ) {
				$optionsVSV = get_option( 'VWvideoShareOptions' );
				if ( ! VWvideoShare::hasPriviledge( $optionsVSV['shareList'] ) ) {
					return __( 'You do not have permissions to share videos!', 'videosharevod' );
				}
			}

			if ( $options['replaceFlash'] ) {
				return do_shortcode( '[videowhisper_html5recorder]' );
			}

			$atts = shortcode_atts(
				array(
					'height'       => '550px',
					'width'        => '100%',
					'youtube_sync' => '',
					'vimeo_sync'   => '',
					'default_name' => '',
					'category'     => '',
				),
				$atts,
				'videowhisper_recorder'
			);

			$base   = plugin_dir_url( __FILE__ ) . 'posts/videowhisper/';
			$swfurl = $base . 'videorecorder.swf';

			$swfurl .= '?ssl=1&room=' . $atts['default_name']; // default recording name passed as room (applies to visitors)

			$height = $atts['height'];

			if ( $category = sanitize_file_name( $atts['category'] ) ) {
				setcookie( 'vw_recording_category', $category, time() + 86400, '/' );

				$categoryID = get_cat_ID( $category );
				if ( ! $categoryID ) {
					require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
					$categoryID = wp_create_category( $category );
				}

				/*
				$sessionPath = $options['uploadsPath'] . $atts['default_name'];
				$sessionInfo = array('category' => $category);
				VWvideoPosts::varSave($sessionPath, $sessionInfo);
				*/
			}

			$videowhisper = $options['videowhisper'];
			$state        = 'block';
			if ( ! $videowhisper ) {
				$state = 'none';
			}

			$poweredby = '<div style=\'display: ' . esc_attr( $state ) . ';\'><i><small>Powered by <a href=\'http://www.videowhisper.com\'  target=\'_blank\'>VideoWhisper</a>,<a href=\'http://www.videowhisper.com/?p=WordPress+Video+Recorder+Posts+Comments\'  target=\'_blank\'>Video Recorder</a>.</small></i></div>';

			$htmlCode .= $poweredby;

			return $htmlCode;
		}



		static function varSave( $path, $var ) {
			file_put_contents( $path, serialize( $var ) );
		}


		static function varLoad( $path ) {
			if ( ! file_exists( $path ) ) {
				return false;
			}

			return unserialize( file_get_contents( $path ) );
		}


		static function media_view_settings( $settings ) {
			$settings['tabs'] = array( 'recorder' => __( 'Recorder', 'textdomain' ) );
			return $settings;
		}

		// Call the new tab with wp_iframe.
		static function media_view_recorder() {
			wp_iframe( array( 'VWvideoPosts', 'recorder_form' ) );
		}

		// the tab content
		static function recorder_form() {
			echo media_upload_header(); // This function is used for print media uploader headers etc.
			echo '<p>Recorder HTML content goes here.</p>';
		}

		static function ajax_query_attachments_args( $args ) {
		   
		    if( isset( $_POST['query']['videos'] ) ) {
		 
		        $args['post_mime_type'] = array( 'video/webm', 'video/mp4', 'video/x-m4v', 'video/mpeg', 'video/quicktime' );
		         
		        unset( $_POST['query']['videos'] );
		    }
		    return $args;
		}


		function plugins_loaded() {
			add_filter( 'ajax_query_attachments_args', array( 'VWvideoPosts', 'ajax_query_attachments_args' ), 99 );

			// add a new recorder  tab
			// add_filter('media_view_settings', array( 'VWvideoPosts', 'media_view_settings') );

			// call the new tab with wp_iframe
			add_action( 'media_upload_recorder', array( 'VWvideoPosts', 'media_upload_recorder' ) );

			add_shortcode( 'videowhisper_html5recorder', array( 'VWvideoPosts', 'videowhisper_html5recorder' ) );

			// web app ajax calls
			add_action( 'wp_ajax_vw_rec_app', array( 'VWvideoPosts', 'vw_rec_app' ) );
			add_action( 'wp_ajax_nopriv_vw_rec_app', array( 'VWvideoPosts', 'vw_rec_app' ) );

			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( 'VWvideoPosts', 'settings_link' ) );

			add_shortcode( 'videowhisper_recorder', array( 'VWvideoPosts', 'shortcode_recorder' ) );
			
			add_action( 'wp_ajax_vpwr_recorder', array( 'VWvideoPosts', 'vpwr_recorder' ) );
			add_action( 'wp_ajax_nopriv_vpwr_recorder', array( 'VWvideoPosts', 'vpwr_recorder' ) );

		}

		function vpwr_recorder() {
			$youtube_sync = sanitize_text_field( $_GET['youtube_sync'] );
			$vimeo_sync   = sanitize_text_field( $_GET['vimeo_sync'] );

			$name     = sanitize_text_field( $_GET['name'] );
			$category = sanitize_file_name( $_GET['category'] );

			ob_clean();
			echo do_shortcode( '[videowhisper_recorder youtube_sync="' . esc_attr( $youtube_sync ) . '" vimeo_sync="' . esc_attr( $vimeo_sync ) . '" default_name="' . esc_attr( $name ) . '" category="' . esc_attr( $category ) . '"]' );
			die;
		}



	}


}
// instantiate
if ( class_exists( 'VWvideoPosts' ) ) {
	$videoPosts = new VWvideoPosts();
}

// Actions and Filters
if ( isset( $videoPosts ) ) {
	add_action( 'plugins_loaded', array( &$videoPosts, 'plugins_loaded' ) );
	add_action( 'admin_menu', array( &$videoPosts, 'admin_menu' ) );
	add_action( 'admin_bar_menu', array( &$videoPosts, 'admin_bar_menu' ), 90 );

}

