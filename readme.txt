=== Webcam Microphone Screen Recorder HTML5 ===
Contributors: videowhisper
Author: VideoWhisper.com
Author URI: https://videowhisper.com
Plugin Name: Webcam Microphone Screen Recorder HTML5
Plugin URI: https://demo.videowhisper.com/cam-recorder-html5-video-audio/
Donate link: https://site2stream.com/video/
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: video, recorder, webcam, microphone, screen
Requires at least: 5.0
Tested up to: 6.6
Stable tag: trunk

Record videos or audio directly in your browser with our HTML5-based webcam, microphone and screen recorder.

== Description ==

Empower your website with the Webcam Microphone Screen Recorder HTML5 plugin, a versatile tool for creating and sharing media directly within posts. This plugin lets authors and administrators easily record video or audio using a webcam, microphone, or screen capture. Perfect for tutorials, testimonials, or video blogs, it integrates seamlessly into the WordPress Media Library and supports various formats for immediate playback or further processing with Video Share VOD integration.

= Benefits = 
* Versatile Recording Options: Record from webcam, microphone, or capture your screen directly through the browser.
* Seamless WordPress Integration: Automatically adds recordings to the Media Library for easy use in posts and pages.
* HTML5 Technology: No need for additional software or plugins, works natively in all modern browsers.
* Video Post Integration: Integrates with the Video Share VOD plugin to enable publishing of videos as custom video posts.
* User-Friendly Interface: Simple controls for recording, playback, and uploading make it accessible to all user levels.
* Monetization Capability: Compatible with MicroPayments/FansPaysite plugin for adding and monetizing video assets.

= HTML5 Web Recorder = 
* Can be accessed from backend or deployed with a shortcode to record video / audio.
* Can record Webcam + Microphone Video, Screen + Microphone Video, Microphone Audio
* Recordings can be sent to server (configurable uploads folder) or saved locally by user for later upload.
* Integrates with Media Library to make recordings available to insert in posts.
* Integrates [Video Share VOD](https://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand") plugin to automatically publish the video recordings as custom video posts on frontend.
* Integrates with [MicroPayments - Paid Membership, Content, Subscriptions](https://wordpress.org/plugins/paid-membership/ "MicroPayments - Paid Membership, Content, Subscriptions") to record content. 
* 100% HTML5 browser based: Does not require a live streaming server or Flash. Based on [VideoWhisper HTML5 Webcam/Microphone Recorder](https://demo.videowhisper.com/cam-recorder-html5-video-audio/ "VideoWhisper HTML5 Webcam/Microphone Recorder")

= How to Use HTML5 Recorder = 
Allow access to webcam and microphone when prompted by browser, to enable recording. Select Video/Audio mode (configurable from settings), use Start/Stop buttons to record. Then you can playback preview, download recording or sent to server, or discard and retry. 
Recorder can save video/audio recordings in format supported by browsers (mp4/mp3 in Safari, webm in most other HTML5 browsers). Videos can be converted with Video Share VOD if integration is enabled. Access this page again (reload) to restart web recording application.

= Recommended Hosting =
Although recording does not involve special hosting requirements (except ability to upload the HTML5 recorded files), processing the videos server side with VideoShareVOD or similar plugins requires FFmpeg. 
[VideoShareVOD FFmpeg Hosting](https://videosharevod.com/hosting/  "VideoShareVOD FFmpeg Hosting") 

= Technical Clarifications / Support =
[Contact VideoWhisper](https://consult.videowhisper.com?topic=Video+Recorder  "Contact VideoWhisper") 

Note: Previous Flash recorder and functionality was removed as no longer supported by most browsers. Only HTML5 application and implemented features are supported.

== Screenshots ==
1. HTML5 recorder : video recording
2. HTML5 recorder : audio recording


== Changelog ==

= 3.3 = 
* Cleaned up legacy features
* Code improvements
* PHP8 support
* Screen recording

= 3.2 =
* Integrates Media Library to add the recordings
* Access recorder from backend

= 3.1 =
* Integrates HTML Recorder based on HTML5 Videochat app

= 2.6 =
* Vimeo player synchronized recordings with [videowhisper_recorder vimeo_sync="video-id"]

= 2.4 =
* Recorder app has ability to pause, resume - useful for sync with playback of another video

= 1.98 =
* VideoWhisper Video Recorder 1.98
* Youtube player synchronised recordings with [videowhisper_recorder youtube_sync="video-id"]

= 1.92 =
* VideoWhisper Video Recorder 1.92
* Video Container configuration

= 1.85.1 =
* VideoShareVOD integration for advanced video management and playback
* [videowhisper_recorder] Shortcoder

= 1.55 =
* Integrates VideoWhisper Video Recorder 1.55
* HTML5 playback support (if conversion is possible)
* Import previous recording in posts

= 1.45.4 =
* Support stream names with spaces fix

= 1.45.3 =
* Folder location fix (from videoposts to

= 1.45.2 =
* Shortcodes for code reliability
* Support for JwPlayer Plugin http://wordpress.org/extend/plugins/jw-player-plugin-for-wordpress/
* More settings

= 1.45 =
* First release
* Integrates VideoWhisper Video Recorder 1.45
* Record and embed video when writing post
* Settings
* Recordings list to delete recording files (if folder is accessible)