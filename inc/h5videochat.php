<?php
namespace VideoWhisper\Recorder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait H5Videochat {

	static function inList( $keys, $data ) {
		if ( ! $keys ) {
			return 0;
		}
		if ( ! $data ) {
			return 0;
		}
		if ( strtolower( trim( $data ) ) == 'all' ) {
			return 1;
		}
		if ( strtolower( trim( $data ) ) == 'guest' ) {
			return 1;
		}
		if ( strtolower( trim( $data ) ) == 'none' ) {
			return 0;
		}

		$list = explode( ',', strtolower( trim( $data ) ) );
		if ( in_array( 'all', $list ) ) {
			return 1;
		}

		foreach ( $keys as $key ) {
			foreach ( $list as $listing ) {
				if ( strtolower( trim( $key ) ) == trim( $listing ) ) {
					return 1;
				}
			}
		}

			return 0;
	}

	static function enqueueUI() {
		wp_enqueue_script( 'jquery' );

		wp_enqueue_style( 'fomantic-ui', dirname( plugin_dir_url( __FILE__ ) ) . '/scripts/semantic/semantic.min.css' );
		wp_enqueue_script( 'fomantic-ui', dirname( plugin_dir_url( __FILE__ ) ) . '/scripts/semantic/semantic.min.js', array( 'jquery' ) );
	}


	static function videowhisper_recorder_inline( $atts ) {

			$atts = shortcode_atts(
				array(
					'exiturl' => '',
				),
				$atts,
				'videowhisper_recorder_inline'
			);

	}

	static function videowhisper_html5recorder( $atts ) {

			$atts = shortcode_atts(
				array(
					'exiturl' => '',
				),
				$atts,
				'videowhisper_html5recorder'
			);

		$exitURL = sanitize_url( $atts['exiturl'] );

		self::enqueueUI();

		$options = self::getOptions();

		$current_user = wp_get_current_user();

		if ( $current_user->exists() ) {
			if ( $current_user->user_nicename ) {
				$username = urlencode( $current_user->user_nicename );
			} else {
				$visitor = 1;
			}
		}

		$username = sanitize_file_name( $username ?? '' );

		// access keys
		if ( $current_user ) {
			$userkeys   = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;
			$userID     = $current_user->ID;
		}

		$msg      = '';
		$loggedin = 0;

		switch ( $options['canRecord'] ) {
			case 'all':
				$loggedin = 1;

				if ( ! $username ) {
					$timeZone = get_option( 'gmt_offset' ) * 3600;
					$username = 'VW-' . str_replace( '.', '_', self::get_ip_address ) . '-' . date( 'd_m_Y-H_i', time() + $timeZone );
				}
				break;

			case 'members':
				if ( $username ) {
					$loggedin = 1;
				} else {
					$msg = '<a href="' . wp_login_url() . '">Please login first or register an account! Click here to return to website.</a>';
				}
				break;

			case 'list';
				if ( $username ) {
					if ( self::inList( $userkeys, $options['recordList'] ) ) {
						$loggedin = 1;
					} else {
						$msg = '<a href="' . wp_login_url() . '">' . $username . ', you are not in the allowed recorder list.</a>';
					}
				} else {
					$msg = '<a href="' . wp_login_url() . '">Please login first or register an account!</a>';
				}
		break;
		}

		if ( ! $loggedin ) {
			return '<div class="ui segment red">' . $msg . '</div>';
		}

		$sessionKey = 'VideoWhisper';
		
		$wlJS = '';
		if ( $options['whitelabel'] ?? false ) {
			$wlJS = ', checkWait: true, whitelabel: ' . $options['whitelabel'];
		}


		$ajaxurl  = admin_url() . 'admin-ajax.php?action=vw_rec_app';
		$dataCode = "window.VideoWhisper = {userID: $userID, sessionID: 1, sessionKey: '$sessionKey', roomID: 1, performer: 1, userName: '$username', exitURL: '$exitURL', roomName: '$username', serverURL: '$ajaxurl' $wlJS}";

		//
		// wp_enqueue_style( 'semantic-app', '//cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css');
		wp_enqueue_script( 'fomantic-ui', dirname( plugin_dir_url( __FILE__ ) ) . '/scripts/semantic/semantic.min.js', array( 'jquery' ) );

		$k = 0;

		$CSSfiles = scandir( dirname( dirname( __FILE__ ) ) . '/static/css/' );
		foreach ( $CSSfiles as $filename ) {
			if ( strpos( $filename, '.css' ) && ! strpos( $filename, '.css.map' ) ) {
				wp_enqueue_style( 'vw-cams-app' . ++$k, dirname( plugin_dir_url( __FILE__ ) ) . '/static/css/' . $filename );
			}
		}

			$countMain = 0;
		$countRuntime  = 0;
		$JSfiles       = scandir( dirname( dirname( __FILE__ ) ) . '/static/js/' );
		foreach ( $JSfiles as $filename ) {
			if ( strpos( $filename, '.js' ) && ! strpos( $filename, '.js.map' ) ) {
				wp_enqueue_script( 'vw-cams-app' . ++$k, dirname( plugin_dir_url( __FILE__ ) ) . '/static/js/' . $filename, array(), '', true );

				if ( ! strstr( $filename, 'LICENSE.txt' ) ) {
					if ( substr( $filename, 0, 5 ) == 'main.' ) {
						$countMain++;
					}
				}
				if ( ! strstr( $filename, 'LICENSE.txt' ) ) {
					if ( substr( $filename, 0, 7 ) == 'runtime' ) {
						$countRuntime++;
					}
				}
			}
		}

		if ( $countMain > 1 || $countRuntime > 1 ) {
			$htmlCode .= '<div class="ui segment red">Warning: Possible duplicate JS files in application folder! Only latest versions should be deployed.</div>';
		}

		/*
				$title = $room;
				if ($atts['title']) $title = $atts['title'];
				$htmlCode .='<div id="videowhisperHeader" class="ui ' . $options['interfaceClass'] .' segment header attached">' . $title . '</div>';
			*/

		$cssCode = html_entity_decode( stripslashes( $options['appCSS'] ) );

		$htmlCode = <<<HTMLCODE
<!--VideoWhisper.com - HTML5 Videochat web app - uid:$userID u:$username-->
<noscript>You need to enable JavaScript to run this app. For more details see <a href="https://paidvideochat.com/html5-videochat/">HTML5 Videochat</a> or <a href="https://videowhisper.com/">contact HTML5 Videochat developers</a>.</noscript>
<div id="videowhisperAppContainer"><div id="videowhisperVideochat"></div></div>
<script>$dataCode;
</script>
<style>

#videowhisperAppContainer
{
display: block;
min-height: 50px;
height: inherit;
background-color: #eee;
position: relative;
z-index: 102 !important;
}

#videowhisperVideochat
{
display: block;
width: 100%;
height: 100%;
position: absolute;
z-index: 102 !important;
}

$cssCode
</style>
HTMLCODE;

		return $htmlCode;

	}


	static function path2url( $file, $Protocol = 'https://' ) {
		if ( is_ssl() && $Protocol == 'http://' ) {
			$Protocol = 'https://';
		}

		$url = $Protocol . sanitize_text_field( $_SERVER['HTTP_HOST'] );

		// on godaddy hosting uploads is in different folder like /var/www/clients/ ..
		$upload_dir = wp_upload_dir();
		if ( strstr( $file, $upload_dir['basedir'] ) ) {
			return $upload_dir['baseurl'] . str_replace( $upload_dir['basedir'], '', $file );
		}

		// folder under WP path
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( strstr( $file, get_home_path() ) ) {
			return site_url() . str_replace( get_home_path(), '', $file );
		}

		// under document root
		if ( strstr( $file, sanitize_text_field( $_SERVER['DOCUMENT_ROOT'] ) ) ) {
			return $url . str_replace( sanitize_text_field( $_SERVER['DOCUMENT_ROOT'] ), '', $file );
		}

		return $url . $file;
	}

	/**
	 * Retrieves the best guess of the client's actual IP address.
	 * Takes into account numerous HTTP proxy headers due to variations
	 * in how different ISPs handle IP addresses in headers between hops.
	 */
	static function get_ip_address() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', sanitize_text_field( $_SERVER[ $key ] ) ) as $ip ) {
					// trim for safety measures
					$ip = trim( $ip );
					// attempt to validate IP
					if ( self::validate_ip( $ip ) ) {
						return $ip;
					}
				}
			}
		}

		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : false;
	}


		/**
		 * Ensures an ip address is both a valid IP and does not fall within
		 * a private network range.
		 */
	static function validate_ip( $ip ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
			return false;
		}
		return true;
	}


	static function appSfx() {
		// sound effects sources

		// $base = dirname(plugin_dir_url(__FILE__)) . '/sounds/';

		return array();
	}


	static function appText() {
		 // implement translations

		// returns texts
			return array(
				'Record' => __( 'Record', 'ppv-live-webcams' ),
				'Send'   => __( 'Send', 'ppv-live-webcams' ),
			);
	}

	static function appUserOptions( $userID, $options ) {
		return array(
			'h5v_dark' => self::is_true( $userID ? get_user_meta( $userID, 'h5v_dark', true ) : false ),
		);
	}


	static function is_true( $val, $return_null = false ) {
		$boolval = ( is_string( $val ) ? filter_var( $val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) : (bool) $val );
		return $boolval === null && ! $return_null ? false : $boolval;
	}


	static function appFail( $message = 'Request Failed', $response = null ) {
		// bad request: fail

		if ( ! $response ) {
			$response = array();
		}

		$response['error'] = $message;

		$response['VideoWhisper'] = 'https://videowhisper.com';

		echo json_encode( $response );

		die();
	}

	static function appRoom( $userName, $options, $exitURL = '' ) {
		// public room parameters, specific for this user
		// depends on integration

		$room = array();

		$room['ID']   = 1;
		$room['name'] = $userName;

		$room['performer']   = $userName;
		$room['performerID'] = 1;

		$isPerformer = 1;

		// screen

		$room['screen'] = 'RecorderScreen';

		// $room['actionPrivate'] = !$isPerformer;
		$room['privateUID'] = 0;

		$room['actionID'] = 0;

		// custom buttons
		$actionButtons = array();

		// _ will be added to target
		$actionButtons['exitDashboard'] = array(
			'name'    => 'exitDashboard',
			'icon'    => 'close',
			'color'   => 'red',
			'floated' => 'right',
			'target'  => 'top',
			'url'     => ( $exitURL ? $exitURL : get_permalink( $options['exitPage'] ) ),
			'text'    => 'Exit',
			'tooltip' => __( 'Exit', 'ppv-live-webcams' ),
		);
		$room['actionButtons']          = $actionButtons;

		return $room;
	}

	static function handle_upload( $file, $destination ) {
		   // ex $_FILE['myfile']

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		   $movefile = wp_handle_upload( $file, array( 'test_form' => false ) );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			if ( ! $destination ) {
				   return 0;
			}
			   rename( $movefile['file'], $destination ); // $movefile[file, url, type]
			   return 0;
		} else {
			/*
			 * Error generated by _wp_handle_upload()
			 * @see _wp_handle_upload() in wp-admin/includes/file.php
			 */
			return $movefile['error']; // return error
		}

	}


	// !App Ajax handlers
	static function vw_rec_app() {
		 $options = self::getOptions();
		ob_clean();

		$http_origin              = get_http_origin();
		$response['http_origin']  = $http_origin;
		$response['VideoWhisper'] = 'https://videowhisper.com';

		$task    = sanitize_file_name( $_POST['task'] );
		$devMode = self::is_true( sanitize_text_field( $_POST['devMode'] ) ); // app in devMode

		$requestUID = intval( $_POST['requestUID'] ?? 0 ); // directly requested private call

		// originally passed trough window after creating session
		// urlvar user_id > php var $userID

		$VideoWhisper = isset( $_POST['VideoWhisper'] ) ? (array) $_POST['VideoWhisper'] : array(); // sanitize elements as needed: int, url

		// session info received trough VideoWhisper POST var
		if ( count( $VideoWhisper ) ) {
			$userID     = intval( $VideoWhisper['userID'] );
			$sessionID  = intval( $VideoWhisper['sessionID'] );
			$roomID     = intval( $VideoWhisper['roomID'] );
			$sessionKey = intval( $VideoWhisper['sessionKey'] ?? 'VideoWhisper');

			$privateUID   = intval( $VideoWhisper[ 'privateUID' ] ?? 0 ); // in private call
			$roomActionID = intval( $VideoWhisper['roomActionID'] ?? 0 );

			$exitURL = sanitize_url( $VideoWhisper['exitURL'] ?? '' );
		}

		$user = wp_get_current_user();

		if ( $user ) {
			if ( $userID != $user->ID ) {
				self::appFail( "Invalid userID ($userID)!" );
			}
			$userName = sanitize_file_name( $user->user_nicename );
			$roomName = $userName;
		}

		if ( $task == 'login' ) {
			// retrieve wp info
			if ( ! $user ) {
								$isVisitor = 1;
				// self::appFail('User not found: ' . $userID);

				if ( sanitize_text_field( $_COOKIE['htmlchat_username'] ) ) {
					$userName = sanitize_text_field( $_COOKIE['htmlchat_username'] );
				} else {
					$userName = 'G_' . base_convert( time() % 36 * rand( 0, 36 * 36 ), 10, 36 );
					setcookie( 'htmlchat_username', $userName );
				}

				$isPerformer = 0;

			}

			// reset user preferences
			if ( $userID ) {
				if ( is_array( $options['appSetup'] ) ) {
					if ( array_key_exists( 'User', $options['appSetup'] ) ) {
						if ( is_array( $options['appSetup']['User'] ) ) {
							foreach ( $options['appSetup']['User'] as $key => $value ) {
								$optionCurrent = get_user_meta( $userID, $key, true );

								if ( empty( $optionCurrent ) || $options['appOptionsReset'] ) {
									update_user_meta( $userID, $key, $value );
								}
							}
						}
					}
				}
			}

					// $balance = floatval(self::balance($userID, false, $options)); //final only, not temp

					// user session parameters and info, updates
					$response['user'] = array(
						'ID'         => intval( $userID ),
						'name'       => $userName,
						'sessionID'  => intval( $sessionID ),
						'sessionKey' => intval( $sessionID ),
						'loggedIn'   => true,
						'balance'    => 0,
						'avatar'     => get_avatar_url( $userID, array( 'default' => dirname( plugin_dir_url( __FILE__ ) ) . '/images/avatar.png' ) ),
					);

					// if ($userID != $rm->owner) $privateUID = $rm->owner; //the other user is room owner

					$response['room'] = self::appRoom( $userName, $options, $exitURL );

					$response['user']['options'] = self::appUserOptions( $userID, $options );

					// config params, const
					$response['config'] = array(
						'wss'              => $options['wsURLWebRTC'] ?? '',
						'application'      => $options['applicationWebRTC'] ?? '',

						'videoCodec'       => $options['webrtcVideoCodec'] ?? '',
						'videoBitrate'     => $options['webrtcVideoBitrate'] ?? 0,
						'audioBitrate'     => $options['webrtcAudioBitrate'] ?? 0,
						'audioCodec'       => $options['webrtcAudioCodec'] ?? '',
						'autoBroadcast'    => false,
						'actionFullscreen' => true,
						'actionFullpage'   => false,

						'serverURL'        => admin_url() . 'admin-ajax.php?action=vw_rec_app',
					);

					// translations
					$response['config']['text']       = self::appText();
					$response['config']['sfx']        = self::appSfx();
					$response['config']['balanceURL'] = '';

					// pass app setup config parameters
					if ( is_array( $options['appSetup'] ) ) {
						if ( array_key_exists( 'Config', $options['appSetup'] ) ) {
							if ( is_array( $options['appSetup']['Config'] ) ) {
								foreach ( $options['appSetup']['Config'] as $key => $value ) {
									$response['config'][ $key ] = $value;
								}
							}
						}
					}

					$response['config']['exitURL'] = $exitURL ? $exitURL : get_permalink( $options['exitPage'] );

					$response['config']['loaded'] = true;

		}

		$needUpdate = array();

		// process app task (other than login)
		switch ( $task ) {

			case 'login':
				break;

			case 'tick':
				break;

			case 'options':
				break;

			case 'update':
				break;

			case 'recorder_upload':
				if ( ! $roomName ) {
					self::appFail( 'No room for recording.' );
				}
				if ( strstr( $filename, '.php' ) ) {
					self::appFail( 'Bad uploader!' );
				}

				$mode     = sanitize_text_field( $_POST['mode'] );
				$scenario = sanitize_text_field( $_POST['scenario'] );

				if ( ! $privateUID ) {
					$privateUID = 0; // public room
				}

				// generate same private room folder for both users
				if ( $privateUID ) {
					if ( $isPerformer ) {
						$proom = $userID . '_' . $privateUID; // performer id first
					} else {
						$proom = $privateUID . '_' . $userID;
					}
				}

				$destination = $options['uploadsPath'];
				if ( ! file_exists( $destination ) ) {
					mkdir( $destination );
				}

				$destination .= "/$roomName";
				if ( ! file_exists( $destination ) ) {
					mkdir( $destination );
				}

				if ( $proom ) {
					$destination .= "/$proom";
					if ( ! file_exists( $destination ) ) {
						mkdir( $destination );
					}
				}

				// $response['_FILES'] = $_FILES;

				$allowed = array( 'mp3', 'ogg', 'opus', 'mp4', 'webm' );

				$uploads  = 0;
				$filename = '';

				if ( isset( $_FILES ) ) {
					if ( is_array( $_FILES ) ) {
						foreach ( $_FILES as $ix => $file ) {

							$filename = filter_var( $file['name'], FILTER_SANITIZE_STRING );

							$ext                          = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
							$response['uploadRecLastExt'] = $ext;
							$response['uploadRecLastF']   = $filename;

							$filepath = $destination . '/' . $filename;

							if ( in_array( $ext, $allowed ) ) {
								if ( file_exists( $file['tmp_name'] ) ) {
									$errorUp = self::handle_upload( $file, $filepath ); // handle trough wp_handle_upload()
									if ( $errorUp ) {
										$response['warning'] = ( $response['warning'] ? $response['warning'] . '; ' : '' ) . 'Error uploading ' . esc_html( $filename . ':' . $errorUp );
									}

									$response['uploadRecLast'] = $filepath;
									$uploads++;

									$filetype = wp_check_filetype( $filepath );

									$attachment_args = array(
										'guid'           => self::path2url( $filepath ),
										'post_mime_type' => $filetype['type'],
										'post_title'     => $filename,
										'post_content'   => '',
										'post_status'    => 'publish',
									);

									if ( $options['mediaLibrary'] ) {
										$attach_id = wp_insert_attachment( $attachment_args, $filepath );

										// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
										require_once ABSPATH . 'wp-admin/includes/media.php';
										require_once ABSPATH . 'wp-admin/includes/image.php';

										// Generate the metadata for the attachment, and update the database record.
										$attach_data = wp_generate_attachment_metadata( $attach_id, $filepath );
										if ( ! empty( $attach_data ) ) {
											wp_update_attachment_metadata( $attach_id, $attach_data );
										}
									}
								}
							}
						}
					}
				}

				$response['uploadCount'] = $uploads;

				if ( ! file_exists( $filepath ) ) {
					$response['warning'] = 'Recording upload failed!';
				}

				if ( $mode == 'video' ) {
					if ( $options['videosharevod'] ) {
						if ( class_exists( 'VWvideoShare' ) ) {

							$playlists = sanitize_file_name( $userName );

							$categoryID = '';

							if ( $category ) {
								$categoryID = get_cat_ID( $category );
							}

							$output                 = \VWvideoShare::importFile( $filepath, $filename, $userID, $playlists, $categoryID, 'recorder', $roomName . ' Video Recording' );
							$response['vsv_output'] = $output;
						}
					}
				}

				break;

			default:
				$response['warning'] = 'Not implemented in this integration: ' . $task;

		}

		$response['startTime']           = 0;
		$response['messages']            = array(); // messages list
		$response['timestamp']           = time(); // update time
		$response['lastMessageID']       = 0;
		$response['roomUpdate']['users'] = array();

		echo json_encode( $response );
		die();
	}

}
