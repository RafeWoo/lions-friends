<?php
/*
Plugin Name: lions-friends
Plugin URI: https://southamlions.org.uk
Description: Provides Some simple user options
Version: 0.1
Author: david woo
Author URI: https://southamlions.org.uk
License: MIT
Text Domain: lions-friends
*/


//redirects user to a particular page after login
//redirects admins to the dashboard and other users to the homepage. Make sure you use add_filter outside of is_admin(), since that function is not available when the filter is called.
function lf_login_default_page($redirect_to, $request, $user) {
	
    //is there a user to check?
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for admins
        if ( in_array( 'administrator', $user->roles ) ) {
            // redirect them to the default place
            return $redirect_to;
        } else {
            return 'user-options';
        }
    } else {
        return $redirect_to;
    }
}

add_filter('login_redirect', 'lf_login_default_page', 10, 3);

///////////////////////////////////////////////////////////////////////////////////////////

add_shortcode( 'lf-user-options', 'lf_display_user_options');
add_shortcode( 'lf-user-sign-up', 'lf_display_user_sign_up');
add_shortcode( 'lf-user-login', 'lf_display_user_login');


function lf_display_user_options() {
	if ( is_user_logged_in() ) { 
	
		//display the user options
		echo " <h1>Hello World</h1>";	
		wp_loginout( home_url() ); // Display "Log Out" link.
		echo " | ";
		wp_register('', ''); // Display "Site Admin" link.
	}
}

function lf_display_user_sign_up()
{
	
	/*
	$rest_url = get_site_url()."/wp-json/"."lions-friends/v1/create-user"; 	
	$wp_nonce = wp_create_nonce( 'wp_rest' );
	*/
	if ( !is_user_logged_in() ) { 
	
	?>
	<h2>Sign Up</h2>
	<script>
		function validateForm() {
			alert('Form submitted!');
			return false;
		}
	</script>


	<form onsubmit="return validateForm();" class="my-form" method="post">
		<label for="username">User Name:</label><br>
		<input type="text" id="username" name="username" value=""/><br>
		<label for="email">e-mail:</label><br>
		<input type="text" id="email" name="email" value="" /><br>
		<label for="password">password</label><br>
		<input type="password" id="password" name="password" value=""/><br>
		<label for="confirm_pw">confirm password:</label><br>
		<input type="password" id="confirm_pw" name="confirm_pw" value="" /><br>
		<br>		
		<input type="submit" value="Submit">
	</form> 
	
	<?php
	
	}
}


/*
	$args
	(array) (Optional) Array of options to control the form output.

	'echo'
	(bool) Whether to display the login form or return the form HTML code. Default true (echo).
	'redirect'
	(string) URL to redirect to. Must be absolute, as in "<a href="https://example.com/mypage/">https://example.com/mypage/</a>". Default is to redirect back to the request URI.
	'form_id'
	(string) ID attribute value for the form. Default 'loginform'.
	'label_username'
	(string) Label for the username or email address field. Default 'Username or Email Address'.
	'label_password'
	(string) Label for the password field. Default 'Password'.
	'label_remember'
	(string) Label for the remember field. Default 'Remember Me'.
	'label_log_in'
	(string) Label for the submit button. Default 'Log In'.
	'id_username'
	(string) ID attribute value for the username field. Default 'user_login'.
	'id_password'
	(string) ID attribute value for the password field. Default 'user_pass'.
	'id_remember'
	(string) ID attribute value for the remember field. Default 'rememberme'.
	'id_submit'
	(string) ID attribute value for the submit button. Default 'wp-submit'.
	'remember'
	(bool) Whether to display the "rememberme" checkbox in the form.
	'value_username'
	(string) Default value for the username field.
	'value_remember'
	(bool) Whether the "Remember Me" checkbox should be checked by default. Default false (unchecked).

	*/

function lf_display_user_login()
{
	if ( !is_user_logged_in() ) { 
	
		$args = array(
			//'redirect' => admin_url(), 
			'form_id' => 'loginform-custom',
			'label_username' => __( 'Username' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in' => __( 'Log In' ),
			'remember' => true
		);
		
		echo "<br>";
		echo "<h2>Log In</h2>";
		wp_login_form( $args );
	
	}
}


/////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'rest_api_init', function () {
	register_rest_route( 'lions-friends/v1', '/create-user', 
		array(
			'methods' => 'POST',
			'callback' => 'try_create_friend',
			'permission_callback' => '__return_true',		
		)	
	);
	}
);

function try_create_friend( WP_REST_Request $request )
{
	global $wpdb;
	
	return array( 
		 'success' => true,
		);
}

