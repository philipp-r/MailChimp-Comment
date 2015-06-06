<?php
/*
Plugin Name: MailChimp Comment
Plugin URI: https://github.com/prcx/MailChimp-Comment
Description: Adds a checkbox to a WordPress comment form so people can subscribe to your MailChimp list.
Author: Philipp Rackevei
Author URI: https://github.com/prcx/
Version: 1.0.0
License: GNU General Public License v3.0
License URI: http://www.opensource.org/licenses/gpl-license.php
*/


/**
 * Define the Options
 *
 * @since 1.0.0
 */
$mcc_options = array(
	'apikey' 				=> '1234567890abcdefghij1234567890ab-us99',
	'username' 				=> 'USERNAME',
	'server'				=> 'us99',
	'list_id' 				=> '12345abcde',
	'list_name'		 		=> 'Newsletter',
	'check_text' 			=> 'I want to subscribe to the weekly newsletter.'
);


/**
 * Outputs the checkbox area below the comment form that allows
 * commenters to subscribe to the email list.
 *
 * @since 1.0.0
 *
 * @global array $mcc_options Array of plugin options
 * @return null
 */
function mccCommentForm() {

	global $mcc_options;
	
	echo '<p class="mcc-subscribe">';
	echo '<input type="checkbox" name="mcc-subscribe" id="mcc-subscribe" value="yes" style="width: auto;"  />';
	echo '<label for="mcc-subscribe"> ' . $mcc_options['check_text'] . '</label>';
	echo '</p>';

}

add_action( 'comment_form', 'mccCommentForm' );



/**
 * Sends the email and name of the commenter to the MailChimp list.
 *
 * @since 1.0.0
 *
 * Comment data from action comment_post is available with $_POST array:
 *		author, email, url, comment, comment_post_ID, comment_parent, mcc-subscribe
 *
 * @global array $mcc_options Array of plugin options
 */
function mccAddToList() {

	global $mcc_options;
	
	// if checkbox is activated
	if( $_POST['mcc-subscribe'] == "yes" ){
		// add name $_POST['author'] & email $_POST['email'] to mailchimp list
		// cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://".$mcc_options['server'].".api.mailchimp.com/3.0/lists/".$mcc_options['list_id']."/members");
		// Mailchimp API Schema: https://us10.api.mailchimp.com/schema/3.0/Lists/Members/Collection.json
		curl_setopt($ch, CURLOPT_USERPWD, $mcc_options['username'].":".$mcc_options['apikey']);
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




?>