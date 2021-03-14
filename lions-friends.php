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

// THis is a list of the option keys
$lf_option_keys = array('update-all', 'update-lotto', 'update-tri', 'update-cinema', 'update-santa');


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
	
		$wp_nonce = wp_create_nonce( 'wp_rest' );
		$rest_url = get_site_url()."/wp-json/"."lions-friends/v1/update-options"; 
	
		$user = wp_get_current_user();
		
		echo "<p>hello $user->user_login</p>";
		

		?>
		
		<p>Please keep me informed about:</p>
		<form name="lf-options-form" class="lf-options-form" onsubmit="return updateOptions();">
				
			<label for="update-all">Everything: </label><input type="checkbox" id="update-all" name="update-all" /><br>
			<label for="update-lottery">Southam Lions 500 Club </label><input type="checkbox" id="update-lotto" name="update-lotto" /><br>
			<label for="update-triathlon">Southam Triathlon </label><input type="checkbox" id="update-tri" name="update-tri" /><br>			
			<label for="update-cinema">Southam Cinema </label><input type="checkbox" id="update-cinema" name="update-cinema" /><br>
			<label for="update-santa">Santa </label><input type="checkbox" id="update-santa" name="update-santa" /><br>
	
		<br><br>		
			<input id="lf-submit-options" type="submit" value="Update" disabled="true" /> 
		
		</form>
		<p id="options_error"></p>
		
		<script type="application/javascript">
		<?php
		
			$update_flags = array();
			
			global $lf_option_keys;
			foreach($lf_option_keys as $key) {
				$update_flags[$key] = (bool) get_user_meta( $user->ID, $key, true );				
			}
		?>
		    const user_options = <?php echo json_encode($update_flags); ?>;
			
			
			//populate
			for( const [key, value] of Object.entries(user_options) ) {							
				document.getElementById(key).checked = value;
			}
			
			
		
			//disable options if all is selected?
			document.forms["lf-options-form"].addEventListener('change', function() {
				
				let is_modified = false;
				for( const [key, value] of Object.entries(user_options) ) {							
					if( value != document.getElementById(key).checked )
					{
						is_modified = true;
						break;
					}
				}
				
				//alert("is modified" + is_modified);
				
				document.getElementById("lf-submit-options").disabled = !is_modified;
			});	

		function updateOptions() {
			
			const url = <?php echo "\"$rest_url\"" ?>;					
			const nonce = <?php	echo "\"$wp_nonce\"" ?>;
			
			let formdata = document.forms["lf-options-form"];
			let result_element = document.getElementById("options_error");
			
			fetch(	
				url, 
				{
					method: 'POST',
					body: new URLSearchParams(new FormData(formdata)),
					headers: {
						'X-WP-Nonce': nonce			
					},
				}
			).then(
				(resp) => {
					return resp.json(); // or resp.text() or whatever the server sends
				}
			).then(
				(body) => {
					
					
					
					if( body.success )
					{
						//result_element.innerHTML = "success " 
						//location.reload();
						location.reload();
					}
					else{
						
						//assume WP_ERROR
						//let keys = "keys: ";
						//for( const [key,value] of Object.entries(body) )
						//{
						//	keys += key + " : " + value + " , ";
						//}
						//alert("error fdh");
						result_element.innerHTML = body.message;//.hello;
					}
					
					
				}
			).catch(
				(error) => {	
					
					
						//alert("error fdh");
						result_element.innerHTML = error.message;//.hello;
				
					//alert("error2");	
					//result_element.innerHTML = error. "Error updating";					
				}
								
			);
			
			
			return false;
		}
		</script>
		
		<?php
		wp_loginout( home_url() ); // Display "Log Out" link.
		//echo " | ";
		//wp_register('', ''); // Display "Site Admin" link.
	}
}

function lf_display_user_sign_up()
{
	$wp_nonce = wp_create_nonce( 'wp_rest' );
	$rest_url = get_site_url()."/wp-json/"."lions-friends/v1/create-user"; 	
	
	
	if ( !is_user_logged_in() ) { 
	
	?>
	<h2>Sign Up</h2>
	<script type="application/javascript">
		function validateForm() {
			
		
			document.getElementById("username-error").innerHTML ="";
			document.getElementById("email-error").innerHTML ="";
			document.getElementById("password-error").innerHTML ="";			
		
			let formdata = document.forms["signup-form"];
			
			let form_is_valid = true;
			
			if (formdata["username"].value == "") {
						
				document.getElementById("username-error").innerHTML ="!Name must be filled out!";
				form_is_valid = false;
			}
		
			const email_string = formdata["email"].value;
			if( email_string == "") {
				document.getElementById("email-error").innerHTML ="!email must be filled out!";
				form_is_valid = false;
			}
			else {
				const email_lower = String(email_string).toLowerCase();
				const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			 
				if( !regex.test( email_lower ) )
				{
					document.getElementById("email-error").innerHTML = "!invalid email!";
					form_is_valid = false;
				}		
			}			
			
			const pw = formdata["password"].value;
			if( pw == "" ){
				document.getElementById("password-error").innerHTML = "!password must be filled out!";
				form_is_valid = false;
			}
			else if( pw != formdata["confirm_pw"].value ){
				document.getElementById("password-error").innerHTML = "!passwords must match!";				
				form_is_valid = false;
				
				formdata["password"].value = "";
				formdata["confirm_pw"].value = "";
			}
				

			if( form_is_valid )
			{
				let result_element = document.getElementById("result");
				result_element.innerHTML = "form valid";
			
			
			const url = <?php echo "\"$rest_url\"" ?>;					
			const nonce = <?php	echo "\"$wp_nonce\"" ?>;							
			
					
			result_element.innerHTML = "sent data";
			fetch(	
				url, 
				{
					method: 'POST',
					body: new URLSearchParams(new FormData(formdata)),
					headers: {
						'X-WP-Nonce': nonce			
					},
				}
			).then(
				(resp) => {
					return resp.json(); // or resp.text() or whatever the server sends
				}
			).then(
				(body) => {
					
					if( body.success )
					{
						//result_element.innerHTML = "success " 
						location.reload();
					}
					else{
						let error_string = "Failed to register: ";
						let fail_reasons = "";
						let count = 0;
						for( const reason of body.fail_reasons )
						{
							if( count > 0)
							{
								fail_reasons += " , ";
							}
							
							fail_reasons += reason;
							++count;
						}
						
						result_element.innerHTML = error_string + fail_reasons;
						
						formdata["password"].value = "";
						formdata["confirm_pw"].value = "";
					}
				}
			).catch(
				(error) => {
					
					result_element.innerHTML = "server error";
					formdata["password"].value = "";
					formdata["confirm_pw"].value = "";
					
				}
			);
		
			}
				
			return false;
		}
		

	</script>

	
	<form name="signup-form" onsubmit="return validateForm();" class="signup-form" 
	
	method="post">
		<label for="username">User Name:</label><br>
		<input type="text" id="username" name="username" value=""/><p id="username-error"></p>
		
		<label for="email">e-mail:</label><br>
		<input type="email" id="email" name="email" value="" /><p id="email-error"></p>
		
		<label for="password">password</label><br>
		<input type="password" id="password" name="password" value=""/><p id="password-error"></p>
		
		<label for="confirm_pw">confirm password:</label><br>
		<input type="password" id="confirm_pw" name="confirm_pw" value="" />
	
		<br><br>		
		<input type="submit" value="Submit">
	</form> 
	<p id="result"></p>
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
//permission callbacks
function lf_is_member() {
    // Restrict endpoint to only users who have the edit_posts capability.
	
    if ( !current_user_can( 'read' ) ) 
	{
      return new WP_Error( 'rest_forbidden', esc_html__( 'Error - insufficient permissions.', 'lions-friends' ), array( 'status' => 401 ) );
	}
 
    // This is a black-listing approach. You could alternatively do this via white-listing, by returning false here and changing the permissions check.
    return true;
}


add_action( 'rest_api_init', function () {
	register_rest_route( 'lions-friends/v1', '/create-user', 
		array(
			'methods' => 'POST',
			'callback' => 'lf_try_create_friend',
			'permission_callback' => '__return_true',		
		)	
	);
	
	register_rest_route( 'lions-friends/v1', '/update-options', 
		array(
			'methods' => 'POST',
			'callback' => 'lf_update_options',
			'permission_callback' => 'lf_is_member',				
		)
	);	
	
	}
);

function lf_update_options( WP_REST_Request $request )
{
	global $lf_option_keys;
	
	$user_id = get_current_user_id();	
	$new_options = [];
	foreach($lf_option_keys as $key) {
		$new_options[$key] = (bool) $request->get_param( $key );
	}
	
	foreach ($new_options as $meta_key => $meta_value) {	
		update_user_meta( $user_id, $meta_key, $meta_value );
	}
	
	
	$success = true;
	
	return array( 
	    'success' => $success,		
	);
}

function lf_try_create_friend( WP_REST_Request $request )
{
		
	$user_name = sanitize_user( $request->get_param( 'username'), true );
	$email = sanitize_email($request->get_param( 'email' ));
	$password = $request->get_param( 'password' );
		
	$success = true;
	$fail_reasons = array();
	
	//
	if( username_exists($user_name) )
	{
		$success = false;
		$fail_reasons[] = "user name already exists";
	}
	
	if( !is_email($email) )
	{
		$success = false;
		$fail_reasons[] = "invalid email";
	}
	
	if( email_exists( $email ) )
	{
		$success = false;
		$fail_reasons[] = "email already exists";
	}
	
	if( $success )
	{
		$new_user_id = wp_create_user($user_name, $password, $email);
		
		//could use register_new_user and not need user to supply password
		if( is_wp_error($new_user_id ) )
		{
			$success = false;
			$fail_reasons[] = "failed to create user";
		}
		else
		{
			//login the new user
			wp_set_current_user($new_user_id, $user_name);
			wp_set_auth_cookie($new_user_id); 
			do_action('wp_login', $user_name); 
					
		}
	}
			
	return array( 
		 'success' => $success,
		 'fail_reasons' => $fail_reasons,
		
		);
}

