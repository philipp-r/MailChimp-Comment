<?php
/*
Plugin Name: MailChimp Comment
Plugin URI: https://github.com/philipp-r/MailChimp-Comment
Description: Adds a checkbox to a WordPress comment form so people can subscribe to your MailChimp list.
Author: Philipp Rackevei
Author URI: https://github.com/philipp-r/
Version: 2.0
License: GNU General Public License v3.0
License URI: http://www.opensource.org/licenses/gpl-license.php
*/


/**
 * Outputs the checkbox area below the comment form that allows
 * commenters to subscribe to the email list.
 */
function mccCommentForm() {

	if( empty( get_option('mcc_options_check_text') ) ) {
		$check_text = "I want to subscribe to the newsletter.";
	} else {
		$check_text = get_option('mcc_options_check_text');
	}
	
	echo '<p class="mcc-subscribe">';
	echo '<input type="checkbox" name="mcc-subscribe" id="mcc-subscribe" value="yes" style="width: auto;"  />';
	echo '<label for="mcc-subscribe"> ' . $check_text . '</label>';
	echo '</p>';

}

// Check if all required options mcc_options_(apikey, username, server, list_id) are set
// Otherwise this plugin won't work correctly
if( !empty(get_option('mcc_options_apikey')) && !empty(get_option('mcc_options_username')) && !empty(get_option('mcc_options_server')) && !empty(get_option('mcc_options_list_id')) ) {
	// add the checkbox to the WordPress comment form
	add_action( 'comment_form', 'mccCommentForm' );
}



/**
 * Sends the email and name of the commenter to the MailChimp list.
 *
 * Comment data from action comment_post is available with $_POST array:
 *		author, email, url, comment, comment_post_ID, comment_parent, mcc-subscribe
 *
 */
function mccAddToList() {
	
	// if checkbox is activated
	if( $_POST['mcc-subscribe'] == "yes" ){
		// add name $_POST['author'] & email $_POST['email'] to mailchimp list
		// cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://".get_option('mcc_options_server').".api.mailchimp.com/3.0/lists/".get_option('mcc_options_list_id')."/members");
		// Mailchimp API Schema: https://us10.api.mailchimp.com/schema/3.0/Lists/Members/Collection.json
		curl_setopt($ch, CURLOPT_USERPWD, get_option('mcc_options_username').":".get_option('mcc_options_apikey'));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// define HTTP Post values
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{
			"email_address":"'.$_POST['email'].'",
			"status":"pending"
		}');
		// Mailchimp API Schema: https://us10.api.mailchimp.com/schema/3.0/Lists/Members/Instance.json
		// Mailchimp API Schema: https://us10.api.mailchimp.com/schema/3.0/Lists/Members/MergeField.json
 
		// execute cURL
		curl_exec($ch);
		curl_close($ch);
	
	}
	
}

add_action( 'comment_post', 'mccAddToList' );



/**
 * Adds the settings for this plugin to WordPress
 *
 * Settings: mcc_options_
 * 	apikey, username, server, list_id, check_text 
 */
 
function mcc_settings_api_init() {
	// Add the new section to discussion settings so we can add our fields to it
	add_settings_section(
		'mcc_settings_section', 'MailChimp Comment', 'mcc_settings_section_callback', 'discussion'
	);
	
 	// Add mcc_options_apikey
 	add_settings_field(
		'mcc_options_apikey', 'Mailchimp API Key', 'mcc_options_apikey_callback', 'discussion', 'mcc_settings_section'
	);
 	// Add mcc_options_username
 	add_settings_field(
		'mcc_options_username', 'Username', 'mcc_options_username_callback', 'discussion', 'mcc_settings_section'
	);
 	// Add mcc_options_server
 	add_settings_field(
		'mcc_options_server', 'Server', 'mcc_options_server_callback', 'discussion', 'mcc_settings_section'
	);
 	// Add mcc_options_list_id
 	add_settings_field(
		'mcc_options_list_id', 'List ID', 'mcc_options_list_id_callback', 'discussion', 'mcc_settings_section'
	);
 	// Add mcc_options_check_text
 	add_settings_field(
		'mcc_options_check_text', 'Checkbox Text', 'mcc_options_check_text_callback', 'discussion', 'mcc_settings_section'
	);
 	
 	// Register the settings
 	register_setting( 'discussion', 'mcc_options_apikey' );
 	register_setting( 'discussion', 'mcc_options_username' );
 	register_setting( 'discussion', 'mcc_options_server' );
 	register_setting( 'discussion', 'mcc_options_list_id' );
 	register_setting( 'discussion', 'mcc_options_check_text' );
 } 
 
 add_action( 'admin_init', 'mcc_settings_api_init' );
 
  
// Adds a info text to the section mcc_settings_section 
// This function will be run at the start of our section
function mcc_settings_section_callback() {
	echo '<p>This plugin shows a checkbox under your comment form. A user can check it to subscribe to your Mailchimp list.</p><p>The Mailchimp status of the user will be "pending". He will get a automated email to verify his address.</p>';
}
 
// Create a text input field for mcc_options_apikey
function mcc_options_apikey_callback() {
	echo '<input name="mcc_options_apikey" id="mcc_options_apikey" type="input" value="' . get_option( 'mcc_options_apikey' ) . '" class="code" /> <br /> You can get an API Key on Mailchimp -> Account -> Extras -> API Keys. <a href="http://eepurl.com/bt_Xqf" target="_blank">More info</a>.';
}
// Create a text input field for mcc_options_username
function mcc_options_username_callback() {
	echo '<input name="mcc_options_username" id="mcc_options_username" type="input" value="' . get_option( 'mcc_options_username' ) . '" class="code" /> <br /> Your Mailchimp Username';
}
// Create a text input field for mcc_options_server
function mcc_options_server_callback() {
	echo '<input name="mcc_options_server" id="mcc_options_server" type="input" value="' . get_option( 'mcc_options_server' ) . '" class="code" /> <br /> You can find the server ID after the minus in your API key. It has to be something like <i>us1</i>, <i>us2</i>, ... <a href="http://status.mailchimp.com/which_datacenter_am_i_using" target="_blank">More info</a>.';
}
// Create a text input field for mcc_options_list_id
function mcc_options_list_id_callback() {
	echo '<input name="mcc_options_list_id" id="mcc_options_list_id" type="input" value="' . get_option( 'mcc_options_list_id' ) . '" class="code" /> <br /> The list ID is not the name of your list. You can get the ID on Mailchimp -> Lists -> [Select the list you want] -> Settings -> List name and defaults. <a href="http://eepurl.com/bub8Jj" target="_blank">More info</a>.';
}
// Create a text input field for mcc_options_check_text
function mcc_options_check_text_callback() {
	echo '<input name="mcc_options_check_text" id="mcc_options_check_text" type="input" value="' . get_option( 'mcc_options_check_text' ) . '" class="code" /> <br /> The text shown next to the checkbox under your comment form. You could use "I want to subscribe to the newsletter."';
}


