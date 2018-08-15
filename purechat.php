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
		add_action('wp_footer',                 array( $this, 'pure_chat_load_snippet') );
		add_action('admin_menu',                array( $this, 'pure_chat_menu' ) );
		add_action('wp_ajax_pure_chat_update',  array( $this, 'pure_chat_update' ) );

		$this->update_plugin();
	}

	function update_plugin() {
		update_option('purechat_plugin_ver', $this->version);
	}

	function pure_chat_menu() {
		add_menu_page('Pure Chat', 'Pure Chat', 'manage_options', 'purechat-menu', array( &$this, 'pure_chat_generateAcctPage' ), plugins_url().'/pure-chat/favicon.ico');
	}

	function pure_chat_update() {

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'purechatnonce' ) ){
			return;
		}

		if ( $_POST['action'] === 'pure_chat_update' && strlen( (string) $_POST['purechatwid'] ) === 36) {
			update_option( 'purechat_widget_code', sanitize_text_field( $_POST['purechatwid'] ) );
			update_option( 'purechat_widget_name', sanitize_text_field( $_POST['purechatwname'] ) );
		}
	}

	function pure_chat_load_snippet() {
		global $current_user;

		if ( get_option( 'purechat_widget_code' ) ) {
			$purechat_widget_code = get_option( 'purechat_widget_code' );
			echo "<script type='text/javascript' data-cfasync='false'>window.purechatApi = { l: [], t: [], on: function () { this.l.push(arguments); } }; (function () { var done = false; var script = document.createElement('script'); script.async = true; script.type = 'text/javascript'; script.src = 'https://app.purechat.com/VisitorWidget/WidgetScript'; document.getElementsByTagName('HEAD').item(0).appendChild(script); script.onreadystatechange = script.onload = function (e) { if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) { var w = new PCWidget({c: '" . esc_js( $purechat_widget_code ) . "', f: true }); done = true; } }; })();</script>";
		} else {
			echo "<!-- Please select a widget in the wordpress plugin to activate purechat -->";
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
				<link rel="stylesheet" href="<?php echo esc_url( plugins_url() ).'/pure-chat/purechatStyles.css'?>" type="text/css">
		</head>
		<?php
		if ( isset( $_POST['purechatwid'] ) && isset( $_POST['purechatwname'] ) ) {
			pure_chat_update();
		}
		?>
		<div class="purechatbuttonbox">
			<img src="<?php echo esc_url( plugins_url() ).'/pure-chat/logo.png'?>"alt="Pure Chat logo"></img>
			<div class="purechatcontentdiv">
				<?php if ( get_option('purechat_widget_code' ) === '' ) : ?>
					<p>Pure Chat allows you to chat in real time with visitors to your WordPress site. Click the button below to get started by logging in to Pure Chat and selecting a chat widget!</p>
					<p>The button will open a widget selector in an external page. Keep in mind that your Pure Chat account is separate from your WordPress account.</p>
				<?php : else : ?>
					<?php $purechat_widget_name = get_option( 'purechat_widget_name' ); ?>
					<h4>Your current chat widget is:</h4>
					<h1 class="purechatCurrentWidgetName"><?php echo esc_html( $purechat_widget_name ); ?></h1>
					<p>Would you like to switch widgets?</p>
				<?php endif; ?>
				?>
			</div>
			<form>
				<input type="button" class="purechatbutton" value="Pick a widget!" onclick="openPureChatChildWindow()">
			</form>
		</div>
		<script>
			var pureChatChildWindow;
			var purechatNameToPass = "<?php echo get_option('purechat_widget_name');?>";
			var purechatIdToPass = "<?php echo get_option('purechat_widget_code');?>";

			function openPureChatChildWindow() {
				var winURL = <?php echo esc_url( $purechatHome ); ?> '/home/pagechoicewordpress?widForDisplay='+purechatIdToPass+'&nameForDisplay='+purechatNameToPass;
				pureChatChildWindow = window.open(winURL, 'Pure Chat');
			}

			var url = ajaxurl;
			window.addEventListener('message', function(event) {
				var data = {
					'action': 'pure_chat_update',
					'purechatwid': event.data.id,
					'purechatwname': event.data.name,
					'nonce': <?php echo wp_create_nonce( 'purechatnonce' ); ?>
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

register_activation_hook(__FILE__, array('Pure_Chat_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('Pure_Chat_Plugin', 'deactivate'));

new Pure_Chat_Plugin();
?>
