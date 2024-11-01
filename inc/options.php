<?php
namespace VideoWhisper\Recorder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// ini_set('display_errors', 1);

trait Options {
	// define and edit settings


	// ! Admin Side

	static function admin_bar_menu( $wp_admin_bar ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$options = get_option( 'VWvideoRecorderOptions' );

		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {

			// find VideoWhisper menu
			$nodes = $wp_admin_bar->get_nodes();
			if ( ! $nodes ) {
				$nodes = array();
			}
			$found = 0;
			foreach ( $nodes as $node ) {
				if ( $node->title == 'VideoWhisper' ) {
					$found = 1;
				}
			}

			if ( ! $found ) {
				$wp_admin_bar->add_node(
					array(
						'id'    => 'videowhisper',
						'title' => '👁 VideoWhisper',
						'href'  => admin_url( 'plugin-install.php?s=videowhisper&tab=search&type=term' ),
					)
				);

				// more VideoWhisper menus

				$wp_admin_bar->add_node(
					array(
						'parent' => 'videowhisper',
						'id'     => 'videowhisper-add',
						'title'  => __( 'Add Plugins', 'paid-membership' ),
						'href'   => admin_url( 'plugin-install.php?s=videowhisper&tab=search&type=term' ),
					)
				);

				$wp_admin_bar->add_node(
					array(
						'parent' => 'videowhisper',
						'id'     => 'videowhisper-contact',
						'title'  => __( 'Contact Support', 'paid-membership' ),
						'href'   => 'https://videowhisper.com/tickets_submit.php?topic=WordPress+Plugins+' . urlencode( sanitize_text_field( $_SERVER['HTTP_HOST'] ) ),
					)
				);
			}

				$menu_id = 'videowhisper-recorder';

				$wp_admin_bar->add_node(
					array(
						'parent' => 'videowhisper',
						'id'     => $menu_id,
						'title'  => '📹 ' . __( 'Webcam Recorder', 'paid-membership' ),
						'href'   => admin_url( 'admin.php?page=recorder' ),
					)
				);

				$wp_admin_bar->add_node(
					array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-new',
						'title'  => __( 'New Recording', 'ppv-live-webcams' ),
						'href'   => admin_url( 'admin.php?page=recorder-new' ),
					)
				);

					$wp_admin_bar->add_node(
						array(
							'parent' => $menu_id,
							'id'     => $menu_id . '-settings',
							'title'  => __( 'Settings', 'ppv-live-webcams' ),
							'href'   => admin_url( 'admin.php?page=recorder' ),
						)
					);

			$wp_admin_bar->add_node(
				array(
					'parent' => $menu_id,
					'id'     => $menu_id . '-hosting',
					'title'  => __( 'Streaming Hosting', 'ppv-live-webcams' ),
					'href'   => 'https://webrtchost.com/hosting-plans/',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'parent' => $menu_id,
					'id'     => $menu_id . '-turnkey',
					'title'  => __( 'Turnkey Plans', 'ppv-live-webcams' ),
					'href'   => 'https://paidvideochat.com/order/',
				)
			);
		}

	}


	static function admin_menu() {

		add_menu_page( 'HTML5 Recorder', 'HTML5 Recorder', 'manage_options', 'recorder', array( 'VWvideoPosts', 'adminOptions' ), 'dashicons-video-alt2', 83 );
		add_submenu_page( 'recorder', 'New Recording', 'New Recording', 'manage_options', 'recorder-new', array( 'VWvideoPosts', 'recorderNew' ) );
		add_submenu_page( 'recorder', 'Settings', 'Settings', 'manage_options', 'recorder-settings', array( 'VWvideoPosts', 'adminOptions' ) );
	}

	static function settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=recorder">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}


	static function recorderNew() {
		?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Add New Recording</h2>
</div>
		<?php

		echo do_shortcode( '[videowhisper_html5recorder exiturl="upload.php"]' );

		?>
	Allow access to webcam and microphone when prompted by browser, to enable recording. Recordings can be sent to server or saved locally for later upload. Recordings sent to server are available in <a href="upload.php">Media Library</a> unless integration is disabled from <a href="options-general.php?page=recorder&mod=settings&tab=integration">plugin settings</A>. HTML5 recorder can save video/audio recordings in format supported by browsers. Videos can be converted with Video Share VOD if integration is enabled. Access this page again (reload) to restart web recording application.
		<?php
	}

	static function getOptions() {
		$options = get_option( 'VWvideoRecorderOptions' );
		if ( ! $options ) {
			$options = self::adminOptionsDefault();
		}

		return $options;
	}

	static function adminOptionsDefault() {
		 $root_url  = get_bloginfo( 'url' ) . '/';
		$upload_dir = wp_upload_dir();

		return array(
			'replaceFlash'        => 1,
			'mediaLibrary'        => 1,

			'exitPage'            => 0,
			'videosharevod'       => '1',
			'whitelabel' => 0, 

			'appSetup'            => unserialize( 'a:1:{s:6:"Config";a:6:{s:8:"darkMode";s:0:"";s:16:"resolutionHeight";s:3:"480";s:7:"bitrate";s:3:"750";s:19:"maxResolutionHeight";s:4:"1080";s:10:"maxBitrate";s:4:"3500";s:15:"recorderMaxTime";s:3:"300";}}' ),
			'appSetupConfig'      => '
; This configures HTML5 Videochat application and other apps that use same API.

[Config]						; Application settings
darkMode = false 			 	; true/false : start app in dark mode
resolutionHeight = 480			; streaming resolution, maximum 480p in free mode
bitrate = 750					; streaming bitrate in kbps, maximum 750kbps in free mode
maxResolutionHeight = 1080 		; maximum selectable resolution height, maximum 480p in free mode
maxBitrate = 3500				; maximum selectable streaming bitrate in kbps, maximum 750kbps in free mode, also limited by hosting
recorderMaxTime = 300			; maximum recording time in seconds, limited in free mode
recorderMode = video			; video/audio mode
recorderModeDisable	= false		; disable user from toggling video/audio mode
timeInterval = 300000			; check web connection to server (rare for recorder)
',

			'appCSS'              => '
.ui.button
{
width: auto !important;
height: inherit !important;
}

.ui .item
{
 margin-top: 0px !important;
}

.ui.modal>.content
{
margin: 0px !important;
}
.ui.header .content
{
background-color: inherit !important;
}

.site-inner
{
max-width: 100%;
}

.panel
{
padding: 0px !important;
margin: 0px !important;
}						
			',

			'uploadsPath'         => $upload_dir['basedir'] . '/vw_vpwr',

			'embedMode'           => 1,
			'autoplay'            => true,
			'rtmp_server'         => 'rtmp://localhost/videowhisper',
			'recordContainer'     => 'flv',
			'selectPlayer'        => 'vwplayer',
			'embedWidth'          => '640px',
			'embedHeight'         => '480px',

			'canRecord'           => 'members',
			'recordList'          => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber',

			'videoCodec'          => 'H264',
			'codecProfile'        => 'baseline',
			'codecLevel'          => '3.1',

			'soundCodec'          => 'Nellymoser',
			'soundQuality'        => '9',
			'micRate'             => '22',

			'camWidth'            => 640,
			'camHeight'           => 480,
			'camFPS'              => 30,

			'camBandwidth'        => 100000,
			'camMaxBandwidth'     => 250000,

			'showCamSettings'     => 1,
			'advancedCamSettings' => 1,
			'disablePreview'      => 0,
			'layoutCode'          => '',
			'fillWindow'          => 0,
			'recordLimit'         => 600,
			'directory'           => '/home/-youraccount-/public_html/streams/',
			'videos_url'          => 'http://-yoursite.com-/streams/',
			'ffmpegPath'          => '/usr/local/bin/ffmpeg',
			'ffmpegConvert'       => ' -vcodec copy -acodec libfaac -ac 2 -ar 22050 -ab 96k',
			'parameters'          => '&bufferLive=900&bufferFull=900&bufferLivePlayback=0.2&bufferFullPlayback=10',
			'parametersSync'      => '&bufferLive=900&bufferFull=900&bufferLivePlayback=0.2&bufferFullPlayback=10&showButtons=0&showMediaButtons=0&disablePreview=1&disableSave=1&disableExit=1&disableStop=1&disableMirror=0&disableRefresh=1&disableDiscard=1&notifyJS=1&recordingMode=record&recordingModeResume=append&withStamp=0',
			'finishedMessage'     => '',
			'playSyncButton'      => 'PLAY',
			'syncInstructions'    => '1) Enable Flash plugin if not already active.<br>2) Then, approve webcam and microphone access from plugin container and browser: when webcam is available for recording a PLAY button will show below.<br>3) Make sure you show in webcam preview as expected. If necessary, select a different camera, change your position or lighting.<br>4) When you are ready click on PLAY button!',
			'videowhisper'        => 0,
		);

	}

	static function getAdminOptions() {

		$adminOptions = self::adminOptionsDefault();

		$options = get_option( 'VWvideoRecorderOptions' );
		if ( ! empty( $options ) ) {
			foreach ( $options as $key => $option ) {
				$adminOptions[ $key ] = $option;
			}
		}
		update_option( 'VWvideoRecorderOptions', $adminOptions );
		return $adminOptions;
	}

	static function adminOptions() {
		$mod   = sanitize_text_field( $_GET['mod'] ?? '' );
		$model = sanitize_text_field( $_GET['model'] ?? '' );
		if ( $mod == '' ) {
			$mod = 'settings';
		}

		if ( $mod == 'settings' ) {
			$options = self::getAdminOptions();

			$optionsDefault = self::adminOptionsDefault();

			// var_dump($options);
			if ( isset( $_POST ) ) {
				if ( ! empty( $_POST ) ) {

					$nonce = $_REQUEST['_wpnonce'];
					if ( ! wp_verify_nonce( $nonce, 'vwsec' ) ) {
						echo 'Invalid nonce!';
						exit;
					}

					foreach ( $options as $key => $value ) {
						if ( isset( $_POST[ $key ] ) ) {
							$options[ $key ] = sanitize_textarea_field( $_POST[ $key ] );
						}
					}

					if ( isset( $_POST['appSetupConfig'] ) ) {
						$options['appSetup'] = parse_ini_string( sanitize_textarea_field( $_POST['appSetupConfig'] ), true );
					}

					update_option( 'VWvideoRecorderOptions', $options );
				}
			}

			$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'html5';

			?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>HTML5 Video/Audio Recorder Settings</h2>
</div>

<h2 class="nav-tab-wrapper">
	<a href="options-general.php?page=recorder&mod=settings&tab=html5" class="nav-tab <?php echo $active_tab == 'html5' ? 'nav-tab-active' : ''; ?>">HTML5 Recorder</a>
	<a href="options-general.php?page=recorder&mod=settings&tab=integration" class="nav-tab <?php echo $active_tab == 'integration' ? 'nav-tab-active' : ''; ?>">Integrations</a>
</h2>



<form method="post" action="<?php echo wp_nonce_url( $_SERVER['REQUEST_URI'], 'vwsec' ); ?>">

			<?php

			switch ( $active_tab ) {
				case 'integration':
				
					?>
<h2>Integrations for HTML5 Recorder</h2>
<h4>Media Library</h4>
<select name="mediaLibrary" id="mediaLibrary">
  <option value="0" <?php echo $options['mediaLibrary'] ? '' : 'selected'; ?>>No</option>
  <option value="1" <?php echo $options['mediaLibrary'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>Add recorded files to Media Library.

<h4>Video Share VOD <a target="_plugin" href="https://videosharevod.com/">Plugin</a></h4>
					<?php
					if ( is_plugin_active( 'video-share-vod/video-share-vod.php' ) ) {
						echo 'Detected:  <a href="admin.php?page=video-share">Configure</a> | <a href="https://videosharevod.com/features/quick-start-tutorial/">Tutorial</a>';
					} else {
						echo 'Not detected. Please install and activate <a target="_videosharevod" href="https://wordpress.org/plugins/video-share-vod/">VideoShareVOD Plugin</a> from <a href="plugin-install.php?s=videowhisper&tab=search&type=term">Plugins > Add New</a>!';
					}
					?>
<BR><select name="videosharevod" id="videosharevod">
  <option value="0" <?php echo $options['videosharevod'] ? '' : 'selected'; ?>>No</option>
  <option value="1" <?php echo $options['videosharevod'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>This feature requires FFmpeg with involved codecs.
<br>Recorder videos are automatically imported into VideoShareVOD.
<br>To also add source recorded files to Media Library, make sure Video Share VOD > Import > Delete Original on Import is disabled.


<h4>MicroPayments <a target="_plugin" href="https://ppvscript.com/micropayments/">Plugin</a></h4>
					<?php
					if ( is_plugin_active( 'paid-membership/paid-membership.php' ) ) {
						echo 'Detected:  <a href="admin.php?page=paid-membership">Configure</a>';
					} else {
						echo 'Not detected. Please install and activate <a target="_videosharevod" href="https://wordpress.org/plugins/paid-membership/">MicroPayments Plugin</a> from <a href="plugin-install.php?s=videowhisper&tab=search&type=term">Plugins > Add New</a>!';
					};
?><br>Recording new video is available from My Assets page.
<?php					
					break;

				case 'html5':
					?>
<h2>HTML5 Recorder Settings</h2>
Display HTML5 Recorder with shortcode [videowhisper_html5recorder exiturl=""]. This web app implementation is based on <a href="https://demo.videowhisper.com/cam-recorder-html5-video-audio/">HTML5 Videochat / Cam Recoder </a>. 
<br>Standalone Usage: <a href="admin.php?page=recorder-new">Record</a> from backend or create a frontend page containing [videowhisper_html5recorder] and recordings will end up in <a href="upload.php">Media library</a>, available to insert in posts.
<br>Recommended Usage: Install <a href="https://videosharevod.com">Video Share VOD - Turnkey Site</a> and/or <a href="https://ppvscript.com/micropayments/">MicroPayments - Paid Content Site</a> plugins that provide advanced turnkey site features and frontend content management. 

<h4>Recorder Exit Page</h4>
<select name="exitPage" id="exitPage">
					<?php

					$args   = array(
						'sort_order'   => 'asc',
						'sort_column'  => 'post_title',
						'hierarchical' => 1,
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'numberposts' => 50,
					);
					
					$sPages = get_posts( $args );
					foreach ( $sPages as $sPage ) {
						echo '<option value="' . esc_attr( $sPage->ID ) . '" ' . ( $options['exitPage'] == intval( $sPage->ID ) || ( !$options['exitPage'] && $sPage->post_title  == 'My Videos' ) ? 'selected' : '' ) . '>' . esc_html( $sPage->post_title ) . '</option>' . "\r\n";
					}
					?>
</select>
<br>Exit page, after uploading recording or cancellation. Recommended: My Videos or Videos page implemented by VideoShareVOD, that displays published videos.

<h4>App Configuration</h4>
<textarea name="appSetupConfig" id="appSetupConfig" cols="120" rows="12"><?php echo esc_textarea( $options['appSetupConfig'] ) ; ?></textarea>
<BR>Application setup parameters are delivered to app when connecting to server. Config section refers to application parameters. Room section refers to default room options (configurable from app at runtime). User section refers to default room options configurable from app at runtime and setup on access.

Default:<br><textarea readonly cols="120" rows="6"><?php echo esc_textarea( $optionsDefault['appSetupConfig'] ) ; ?></textarea>

<BR>Parsed configuration (should be an array or arrays):<BR>
					<?php

					var_dump( $options['appSetup'] );
					?>
<BR>Serialized:<BR>
					<?php

					echo esc_html( serialize( $options['appSetup'] ) );
					?>

<h4>App CSS</h4>
<textarea name="appCSS" id="appCSS" cols="100" rows="6"><?php echo esc_textarea( $options['appCSS'] ) ; ?></textarea>
<br>
CSS code to adjust or fix application styling if altered by site theme. Multiple interface elements are implemented by <a href="https://fomantic-ui.com">Fomantic UI</a> (a fork of <a href="https://semantic-ui.com">Semantic UI</a>). Editing interface and layout usually involves advanced CSS skills. For reference also see <a href="https://paidvideochat.com/html5-videochat/css/">Layout CSS</a>. Default:<br><textarea readonly cols="100" rows="3"><?php echo esc_textarea( $optionsDefault['appCSS'] ) ; ?></textarea>

<h4><?php _e( 'Uploads Path', 'video-share-vod' ); ?></h4>
<input name="uploadsPath" type="text" id="uploadsPath" size="100" maxlength="256" value="<?php echo esc_attr( $options['uploadsPath'] ); ?>"/>
<br>Where recordings are originally uploaded. Default: <?php echo esc_html(trim($optionsDefault['uploadsPath']))?>
<?php
if (!@file_exists($options['uploadsPath']) ) echo '<BR>WARNING! Path does not exist: ' . esc_html($options['uploadsPath']);
?>

<h4>Who can record videos</h4>
<select name="canRecord" id="canRecord">
  <option value="all" <?php echo $options['canRecord'] == 'all' ? 'selected' : ''; ?>>Anybody (including visitors)</option>
  <option value="members" <?php echo $options['canRecord'] == 'members' ? 'selected' : ''; ?>>All Members</option>
  <option value="list" <?php echo $options['canRecord'] == 'list' ? 'selected' : ''; ?>>Members in List</option>
</select>
<BR>When using VideoShareVOD, these must match video share settings (because user also needs to be able to share videos to get recording published). To enable recording for site visitors, video sharing should be enabled for "Guest" in VideoShareVOD settings.

<h4>Members allowed to record video (comma separated usernames-logins, roles, IDs)</h4>
<textarea name="recordList" cols="100" rows="3" id="recordList"><?php echo esc_textarea( $options['recordList'] ) ; ?>
</textarea>
<BR>Add "Guest" or "All" to enable site visitors to access video recorder.
<br>Ability to add videos needs to be enabled for the specific users from VideoShareVOD, otherwise videos can only be added to Media Library (if integration is enabled).


<h4>Whitelabel Mode: Remove Author Attribution Notices (Explicit Permission Required)</h4>
<select name="whitelabel" id="whitelabel">
	<option value="0" <?php echo ! $options['whitelabel'] ? 'selected' : ''; ?>>Disabled</option>
	<option value="1" <?php echo $options['whitelabel'] == '1' ? 'selected' : ''; ?>>Enabled</option>
</select>
<br>Embedded HTML5 Videochat application is branded with subtle attribution references to authors, similar to most software solutions in the world. Removing the default author attributions can be permitted by authors with a <a href="https://videowhisper.com/tickets_submit.php?topic=WhiteLabel+HTML5+Videochat">special agreement</a>.
<br>Warning: Application will not start if whitelabel mode is enabled and explicit agreement from authors is not available, to remove attribution notices.
				
					<?php
					break;
			}
			?>

			<?php
			submit_button();
			?>


</form>
			<?php
		}

	}


}
