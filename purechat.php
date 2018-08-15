<?php
/**
 * @package Pure_Chat
 * @version 2.2
 */
/*
Plugin Name: Pure Chat
Plugin URI:
Description: Website chat, simplified. Now 100% Free for Live Chat for Three Users/Operators and Unlimited Chats for your website! Love purechat? Spread the word! <a href="https://wordpress.org/support/view/plugin-reviews/pure-chat">Click here to review the plugin!</a>
Author: Pure Chat, Inc.
Version: 2.2
Author URI: purechat.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include 'variables.php';

class Pure_Chat_Plugin {
	var $version = 5;

	public static function activate()	{
		Pure_Chat_Plugin::clear_cache();
	}

	public static function deactivate()	{
		Pure_Chat_Plugin::clear_cache();
	}



	function __construct() {
	//	add_option('purechat_widget_code', '', '', 'yes');
	//	add_option('purechat_widget_name', '', '', 'yes');

		add_action('wp_footer', array( &$this, 'pure_chat_load_snippet') );

		add_action('admin_menu', array( &$this, 'pure_chat_menu' ) );
		add_action('wp_ajax_pure_chat_update', array( &$this, 'pure_chat_update' ) );

		$this->update_plugin();
	}

	function update_plugin() {
		update_option('purechat_plugin_ver', $this->version);
	}

	function pure_chat_menu() {
		add_menu_page('Pure Chat', 'Pure Chat', 'manage_options', 'purechat-menu', array( &$this, 'pure_chat_generateAcctPage' ), plugins_url().'/pure-chat/favicon.ico');
	}

	function pure_chat_update() {
		if($_POST['action'] == 'pure_chat_update' && strlen((string)$_POST['purechatwid']) == 36)
		{
			update_option('purechat_widget_code', $_POST['purechatwid']);
			update_option('purechat_widget_name', $_POST['purechatwname']);
		}
	}

	function pure_chat_load_snippet() {
		global $current_user;
		if(get_option('purechat_widget_code'))
		{
			echo("<script type='text/javascript' data-cfasync='false'>window.purechatApi = { l: [], t: [], on: function () { this.l.push(arguments); } }; (function () { var done = false; var script = document.createElement('script'); script.async = true; script.type = 'text/javascript'; script.src = 'https://app.purechat.com/VisitorWidget/WidgetScript'; document.getElementsByTagName('HEAD').item(0).appendChild(script); script.onreadystatechange = script.onload = function (e) { if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) { var w = new PCWidget({c: '" . get_option('purechat_widget_code') . "', f: true }); done = true; } }; })();</script>");
		}
		else
		{
			echo("<!-- Please select a widget in the wordpress plugin to activate purechat -->");
		}
	}

	private static function clear_cache() {
		if (function_exists('wp_cache_clear_cache')) {
			wp_cache_clear_cache();
		}
	}

	function pure_chat_generateAcctPage() {
		global $purechatHome;
		?>
		<head>
				<link rel="stylesheet" href="<?php echo plugins_url().'/pure-chat/purechatStyles.css'?>" type="text/css">
		</head>
		<?php
		if (isset($_POST['purechatwid']) && isset($_POST['purechatwname'])) {
			pure_chat_update();
		}
		?>
		<p>
		<div class="purechatbuttonbox">
			<img src="<?php echo plugins_url().'/pure-chat/logo.png'?>"alt="Pure Chat logo"></img>
			<div class = "purechatcontentdiv">
				<?php
				if (get_option('purechat_widget_code') == '' ) {
					?>
					<p>Pure Chat allows you to chat in real time with visitors to your WordPress site. Click the button below to get started by logging in to Pure Chat and selecting a chat widget!</p>
					<p>The button will open a widget selector in an external page. Keep in mind that your Pure Chat account is separate from your WordPress account.</p>
				<?php
				} else {
				?>
					<h4>Your current chat widget is:</h4>
					<h1 class="purechatCurrentWidgetName"><?php echo get_option('purechat_widget_name'); ?></h1>
					<p>Would you like to switch widgets?</p>
				<?php
				}
				?>
			</div>
			<form>
				<input type="button" class="purechatbutton" value="Pick a widget!" onclick="openPureChatChildWindow()">
			</form>
			<p>
		</div>
		<script>
			var pureChatChildWindow;
			var purechatNameToPass = "<?php echo get_option('purechat_widget_name');?>";
			var purechatIdToPass = "<?php echo get_option('purechat_widget_code');?>";
			function openPureChatChildWindow() {
				pureChatChildWindow = window.open('<?php echo $purechatHome;?>/home/pagechoicewordpress?widForDisplay=' + purechatIdToPass +
											  '&nameForDisplay=' + purechatNameToPass, 'Pure Chat');
			}
			var url = ajaxurl;
			window.addEventListener('message', function(event) {
				var data = {
					'action': 'pure_chat_update',
					'purechatwid': event.data.id,
					'purechatwname': event.data.name
				};
				jQuery.post(url, data).done(function(){})
				var purechatNamePassedIn = event.data.name;
				if(typeof purechatNamePassedIn != 'undefined') {
					document.getElementsByClassName('purechatcontentdiv')[0].innerHTML = '<h4>Your current chat widget is:</h4><h1 class="purechatCurrentWidgetName">' +
																						  purechatNamePassedIn + '</h1><p>Would you like to switch widgets?</p>';
					purechatNameToPass = purechatNamePassedIn;
					purechatIdToPass = event.data.id;
				}
			}, false);
		</script>
		<div class="purechatlinkbox">
			<p><a href="https://app.purechat.com/user/dashboard" target="_blank">Your Pure Chat dashboard page</a> is your place to answer chats, add more widgets, customize their appearance with images and text, manage users, and more!</p>
		</div>
		<?php
	}
}

/*function deactivate_script(){
		?>
		<script type="text/javascript">

			//Popup in window
			jQuery(document).ready(function($){
				var currentPage='';

				var deactivate_popup = [
				'<div style="position: fixed; top: 80px; width: 275px; background-color: #fff; z-index: 9999; left: 0; right: 0; line-height: 20px; margin: auto; padding: 20px; box-shadow: 1px 1px 1px grey;">',
				'<center><img src="<?=plugin_dir_url( __FILE__ )?>/logo.png"/></center><br>',
				'<form action="">',
					'<input type="radio" name="purechat_survey" value="expensive">Didn\'t recieve enough chats</input><br><br>',
					'<input type="radio" name="purechat_survey" value="nowork">Too expensive</input><br><br>',
					'<input type="radio" name="purechat_survey" value="testing">Don\'t have the time</input><br><br>',
					'<input type="radio" name="purechat_survey" value="troubleshooting">Missing Key Feautres</input><br><br>',
					'<input type="radio" name="purechat_survey" value="option">Switched to a different chat provider</input><br><br>',
					'<input type="radio" name="purechat_survey" value="Not">Not a right fit or doesn\'t meat your needs</input><br><br>',
					'<input type="radio" name="purechat_survey" value="other">Other Reason:</input><br><input style="margin-top: 5px; width: 100%;"></input><br><br>',
					'</form>',
					'<center><button style="border-radius: 3px; cursor: pointer; font-size: 14px; white-space: nowrap; padding: 10px 60px; background-color: #289AA0; color: #ffffff; transition: 0.7s; border: 0;" id="pure-chat-send-survey">Send and Deactivate</button></center>',
				'</div>'].join("");

				$('#pure-chat-test').find('.deactivate a').click(function(e){
					currentPage = $(this).attr('href');

					e.preventDefault();

					$('body').append(deactivate_popup);
				});

				$(document).on('click', '#pure-chat-send-survey', function(){


					window.location.href=currentPage;
				});

			});
		</script>
		<?php
}*/

register_activation_hook(__FILE__, array('Pure_Chat_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('Pure_Chat_Plugin', 'deactivate'));

new Pure_Chat_Plugin();
?>
