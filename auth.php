<?php

/**
 * @author Adam Matara
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * ### Credits - code forked from - http://mouneyrac.github.io/moodle-auth_googleoauth2/).
 * [CSS social buttons](http://zocial.smcllns.com/) ###
 *
 * Authentication Plugin: Azure AD Authentication
 * If the user account is disabled or non-existant in Azure Active Directory, then the plugin will deny the user.
 * If the email exist and is enabled(and the user has azure set for auth plugin ), then the plugin will login the user related to this email.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * Azure AD -  Oauth2 authentication plugin.
 */
class auth_plugin_Azureoauth2 extends auth_plugin_base {

			/**
			 * Constructor.
			 */
			function auth_plugin_azureoauth2() {
				$this->authtype = 'azureoauth2';
				$this->roleauth = 'auth_azureoauth2';
				$this->errorlogtag = '[AUTH AZUREOAUTH2] ';
				$this->config = get_config('auth/azureoauth2');
			}

			/**
			 * Prevent authenticate_user_login() to update the password in the DB
			 * @return boolean
			 */
			function prevent_local_passwords() {
				return true;
			}

			/**
			 * Authenticates user against azure
			 *
			 * @param string $username The username (with system magic quotes)
			 * @param string $password The password (with system magic quotes)
			 * @return bool Authentication success or failure.
			 */
			function user_login($username, $password) {
				global $DB, $CFG;

				//retrieve the user matching username
				$user = $DB->get_record('user', array('username' => $username,
					'mnethostid' => $CFG->mnet_localhost_id));

				//username must exist and have the right authentication method
				if (!empty($user) && ($user->auth == 'azureoauth2')) {
					$code = optional_param('code', false, PARAM_TEXT);
					if(empty($code)){
						return false;
					}
					return true;
				}

				return false;
			}

			/**
			 * Returns true if this authentication plugin is 'internal'.
			 *
			 * @return bool
			 */
			function is_internal() {
				return false;
			}

			/**
			 * Returns true if this authentication plugin can change the user's
			 * password.
			 *
			 * @return bool
			 */
			function can_change_password() {
				return false;
			}

			/**
			 * Authentication hook - is called every time user hit the login page
			 * The code is run only if the param code is mentionned.
			 */
			function loginpage_hook() {
				global $USER, $SESSION, $CFG, $DB;

				

				//check the azure authorization code
				$authorizationcode = optional_param('code', '', PARAM_TEXT);
				if (!empty($authorizationcode)) {
						
					$authprovider = required_param('authprovider', PARAM_ALPHANUMEXT);
					
					$params = array();
							$params['grant_type'] = 'authorization_code';
							//$params['response_type'] = 'grant_type';
							$params['client_id'] = get_config('auth/azureoauth2', 'azureclientid');
							$params['azuretenantid'] = get_config('auth/azureoauth2', 'azuretenantid');				
							$params['client_secret'] = get_config('auth/azureoauth2', 'azureclientsecret');
							$params['resource'] = 'https://graph.windows.net'; //?api-version=1.0
							$requestaccesstokenurl = 'https://login.windows.net/' .$params['azuretenantid'] .'/oauth2/token';
							$params['redirect_uri'] = $CFG->httpswwwroot . '/auth/azureoauth2/azure_redirect.php';
							$params['code'] = $authorizationcode;
							$params['app_id'] = get_config('auth/azureoauth2', 'azureappid');

					

					//request by curl an access token and refresh token
					require_once($CFG->libdir . '/filelib.php');
					// $curlsettings = array(
					// 'proxy'=>true,
					// 'proxyhost'=>'127.0.0.0.1',
					// 'proxyport'=>'8888',
					// 'proxytype'=>'CURLPROXY_HTTP');
					
					
					
					$curl = new curl();
					// $curl->setopt(array('CURLOPT_PROXY' => '127.0.0.1:8888'));
					// $curl->setheader(array('CURLOPT_HTTPHEADER' => array('Content-Type: application/x-www-form-urlencoded')));
					
				
					
					//if ($authprovider == 'azure') { //Windows Live returns an "Object moved" error with curl->post() encoding
				$clientSecret = urlencode($params['client_secret']);
				// Information about the resource we need access for which in this case is graph.
				$graphId = 'https://graph.windows.net/';
				
				$graphPrincipalId = urlencode($graphId);
				// Information about the app
				
				$clientPrincipalId = $params['azuretenantid'];
				// Construct the body for the STS request
				$authenticationRequestBody = 'grant_type=authorization_code&client_secret='.$clientSecret
						  .'&'.'resource='.$graphPrincipalId.'&'.'client_id='.$params['app_id'].'&'.'code='.$authorizationcode.'&'.'redirect_uri='.$params['redirect_uri'];
						
				$ch = curl_init();
				// set url 
				$stsUrl = 'https://login.windows.net/' .$clientPrincipalId .'/oauth2/token';        
				//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888'); //testing with Fiddler4.0 -proxy.
				curl_setopt($ch, CURLOPT_URL, $stsUrl); 
				// Get the response back as a string 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				// Mark as Post request
				curl_setopt($ch, CURLOPT_POST, 1);
				// Set the parameters for the request
				curl_setopt($ch, CURLOPT_POSTFIELDS,  $authenticationRequestBody);
				
				// By default, HTTPS does not work with curl.
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

				// read the output from the post request
				$output = curl_exec($ch);         
				// close curl resource to free up system resources
				curl_close($ch);      
				// decode the response from sts using json decoder
				$postreturnvalues = json_decode($output);
				   
				  
							// use token to get graph.


							//$accesstoken = json_decode($postreturnvalues)->access_token;
							$tokentype = $postreturnvalues->{'token_type'};
							$accesstoken = $postreturnvalues->{'access_token'};
							// echo $accesstoken;
							//$tokentype = json_decode($postreturnvalues)->token_type;
							 //die($postreturnvalues);
							
												
			  

					
					//with access token request by curl the email address
					if (!empty($accesstoken)) {

					//get the username matching the email
					 
								$authHeader = 'Authorization:' . $tokentype.' '.$accesstoken;
								//$params = array();
								//$params['access_token'] = $accesstoken;
								//$params['token_type'] = $tokentype;
								// $params['response_type'] = 'code';
								$params['client_id'] = get_config('auth/azureoauth2', 'azureclientid');
								// $params['client_id'] = 'https://localhost/login/index.php';
								// $params['client_secret'] = get_config('auth/azureoauth2', 'azureclientsecret');
								// $params['resource'] = 'https://graph.windows.net';
								$requestgraph = 'https://graph.windows.net/' .$params['azuretenantid'] .'/me?api-version=1.0';
								// $params['redirect_uri'] = $CFG->httpswwwroot . '/auth/azureoauth2/azure_redirect.php';
								//$params['code'] = $authorizationcode;
								//$params['grant_type'] = 'authorization_code';
								$ch = curl_init();
					//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888'); //testing with Fiddler4.0 -proxy.
					//Generate the authentication header
					
					// Add authorization header, request/response format header( for json) and a header to request content for Update and delete operations.  
							//die($authHeader);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array($authHeader));
					// Set the option to recieve the response back as string.
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					// By default https does not work for CURL.
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_URL,$requestgraph);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


					// receive server response ...
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$postreturnvalues = curl_exec ($ch);
					curl_close ($ch);

								// decode json
								$azureuser = json_decode($postreturnvalues);
								$useremail = $azureuser->userPrincipalName;
								
								$accountEnabled = $azureuser->accountEnabled; //test if user is enabled.
									
						//throw an error if the email address is not verified
						if (!$accountEnabled) {
							throw new moodle_exception('accountmustbeEnabled', 'auth_azureoauth2');
						}

						// Prohibit login if email belongs to the prohibited domain
						if ($err = email_is_not_allowed($useremail)) {
						   throw new moodle_exception($err, 'auth_azureoauth2');
						}

						//if email not existing in user database then create a new username (userX).
						//echo $useremail;
						if (empty($useremail) or $useremail != clean_param($useremail, PARAM_EMAIL)) {
							throw new moodle_exception('couldnotgetuseremail');
							//TODO: display a link for people to retry
						}

						$user = $DB->get_record('user', array('email' => $useremail, 'deleted' => 0, 'mnethostid' => $CFG->mnet_localhost_id));

						//create the user if it doesn't exist
						if (empty($user)) {

							// deny login if setting "Prevent account creation when authenticating" is on
							if($CFG->authpreventaccountcreation) throw new moodle_exception("noaccountyet", "auth_azureoauth2");



							$newuser = new stdClass();
							$newuser->email = $useremail;
							$username = $useremail; 
								
								// $newuser->firstname =  $azureuser->givenName;
								// $newuser->lastname =  $azureuser->surname;
								
								$newuser->firstname  = $azureuser->givenName;
								$newuser->lastname  = $azureuser->surname;
								$newuser->username = $useremail; 
								if (!empty($newuser->city))   {$newuser->city  = $azureuser->city;}
								if (!empty($newuser->country)){$newuser->country  = $azureuser->country;}
								//$newuser->description  = $azureuser->displayName;
								//$newuser->department  = $azureuser->department;
								//$newuser->phone1  = $azureuser->telephoneNumber;
								//$newuser->phone2  = $azureuser->mobile;
								$address  = $azureuser->streetAddress." ".$azureuser->city." ".$azureuser->state." ".$azureuser->postalCode;
								if (!empty ($address)){$newuser->address = $address;}
							
							create_user_record($username, '', 'azureoauth2');

						} else {
							$username = $user->username;
						}

						//authenticate the user
						//TODO: delete this log later
						require_once($CFG->dirroot . '/auth/azureoauth2/lib.php');
						$userid = empty($user)?'new user':$user->id;
						oauth_add_to_log(SITEID, 'auth_azureoauth2', '', '', $username . '/' . $useremail . '/' . $userid);
						$user = authenticate_user_login($username, null);
						if ($user) {

							//set a cookie to remember what auth provider was selected
							setcookie('MOODLEAZUREOAUTH2_'.$CFG->sessioncookie, $authprovider,
									time()+(DAYSECS*60), $CFG->sessioncookiepath,
									$CFG->sessioncookiedomain, $CFG->cookiesecure,
									$CFG->cookiehttponly);

							//prefill more user information if new user
							if (!empty($newuser)) {
								$newuser->id = $user->id;
								$DB->update_record('user', $newuser);
								$user = (object) array_merge((array) $user, (array) $newuser);
							}

							complete_user_login($user);

							// Create event for authenticated user.
							$event = \auth_azureoauth2\event\user_loggedin::create(
								array('context'=>context_system::instance(),
									'objectid'=>$user->id, 'relateduserid'=>$user->id,
									'other'=>array('accesstoken' => $accesstoken)));
							$event->trigger();

							// Redirection
							if (user_not_fully_set_up($USER)) {
								$urltogo = $CFG->wwwroot.'/user/edit.php';
								// We don't delete $SESSION->wantsurl yet, so we get there later
							} else if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
								$urltogo = $SESSION->wantsurl;    // Because it's an address in this site
								unset($SESSION->wantsurl);
							} else {
								// No wantsurl stored or external - go to homepage
								$urltogo = $CFG->wwwroot.'/';
								unset($SESSION->wantsurl);
							}
							redirect($urltogo);
						} else {
							// authenticate_user_login() failure, probably email registered by another auth plugin
							// Do a check to confirm this hypothesis.
							$userexist = $DB->get_record('user', array('email' => $useremail));
							if (!empty($userexist) and $userexist->auth != 'azureoauth2') {
								$a = new stdClass();
								$a->loginpage = (string) new moodle_url(empty($CFG->alternateloginurl) ? '/login/index.php' : $CFG->alternateloginurl);
								$a->forgotpass = (string) new moodle_url('/login/forgot_password.php');
								throw new moodle_exception('couldnotauthenticateuserlogin', 'auth_azureoauth2', '', $a);
							} else {
								throw new moodle_exception('couldnotauthenticate', 'auth_azureoauth2');
							}
						}
					} else {
						throw new moodle_exception('couldnotgetgoogleaccesstoken', 'auth_azureoauth2', '', null, print_r($postreturnvalues, true));
					}
					}
					// If you are having issue with the display buttons option, add the button code directly in the theme login page.
					if (get_config('auth/azureoauth2', 'oauth2displaybuttons')
						// Check manual parameter that indicate that we are trying to log a manual user.
						// We can add more param check for others provider but at the end,
						// the best way may be to not use the oauth2displaybuttons option and
						// add the button code directly in the theme login page.
						and empty($_POST['username'])
						and empty($_POST['password'])) {
															// Display the button on the login page.
															require_once($CFG->dirroot . '/auth/azureoauth2/lib.php');
															auth_azureoauth2_display_buttons();
														}
				


			}
			/**
			 * Prints a form for configuring this authentication plugin.
			 *
			 * This function is called from admin/auth.php, and outputs a full page with
			 * a form for configuring this plugin.
			 *
			 * TODO: as print_auth_lock_options() core function displays an old-fashion HTML table, I didn't bother writing
			 * some proper Moodle code. This code is similar to other auth plugins (04/09/11)
			 *
			 * @param array $page An object containing all the data for this page.
			 */
			function config_form($config, $err, $user_fields) {
				global $OUTPUT, $CFG;

				// set to defaults if undefined

				if (!isset ($config->azureclientid)) {
					$config->azureclientid = '';
				}
				if (!isset ($config->azureclientsecret)) {
					$config->azureclientsecret = '';
				}
				if (!isset ($config->azuretenantid)) {
					$config->azuretenantid = '';
				}
				if (!isset ($config->azureappid)) {
					$config->azureappid = '';
				}  
				if (!isset ($config->azuredomain)) {
					$config->azuredomain = '';
				}
				if (!isset ($config->oauth2displaybuttons)) {
					$config->oauth2displaybuttons = 0;
				}
				echo '<table cellspacing="0" cellpadding="5" border="0">
					<tr>
					   <td colspan="3">
							<h2 class="main">';
				// Settings 
					print_string('auth_azuresettings', 'auth_azureoauth2');


				echo '</h2>
					   </td>
					</tr>
					<tr>
						<td align="right"><label for="azureclientid">';

				// client ID

				print_string('auth_azureclientid_key', 'auth_azureoauth2');

				echo '</label></td><td>';

				echo html_writer::empty_tag('input',
						array('type' => 'text', 'id' => 'azureclientid', 'name' => 'azureclientid',
							'class' => 'azureclientid', 'value' => $config->azureclientid));
				if (isset($err["azureclientid"])) {
					echo $OUTPUT->error_text($err["azureclientid"]);
				}

				echo '</td><td>';

				print_string('auth_azureclientid', 'auth_azureoauth2', (object) array('domain' => $CFG->wwwroot)) ;


				echo '</td></tr>';

				// azure client secret

				echo '<tr>
						<td align="right"><label for="azureclientsecret">';

				print_string('auth_azureclientsecret_key', 'auth_azureoauth2');

				echo '</label></td><td>';


				echo html_writer::empty_tag('input',
						array('type' => 'text', 'id' => 'azureclientsecret', 'name' => 'azureclientsecret',
							'class' => 'azureclientsecret', 'value' => $config->azureclientsecret));

						if (isset($err["azureclientsecret"])) {
					echo $OUTPUT->error_text($err["azureclientsecret"]);
				}
				echo '</td><td>';
				print_string('auth_azureclientsecret', 'auth_azureoauth2') ;


				// tenant id.
				echo '<tr>
						<td align="right"><label for="auth_azuretenantid">';

				print_string('auth_azuretenantid_key', 'auth_azureoauth2');

				echo '</label></td><td>';

				echo html_writer::empty_tag('input', array('type' => 'text', 'id' => 'azuretenantid', 'name' => 'azuretenantid',
							'class' => 'azuretenantid', 'value' => $config->azuretenantid));

				if (isset($err["azuretenantid"])) {
					echo $OUTPUT->error_text($err["azuretenantid"]);
				}
				echo '</td><td>';
				print_string('auth_azuretenantid', 'auth_azureoauth2') ;		

				// app id.
				echo '<tr>
						<td align="right"><label for="auth_appid_key">';

				print_string('auth_appid_key', 'auth_azureoauth2');

				echo '</label></td><td>';

				echo html_writer::empty_tag('input', array('type' => 'text', 'id' => 'azureappid', 'name' => 'azureappid',
							'class' => 'azureappid', 'value' => $config->azureappid));

				if (isset($err["azureappid"])) {
					echo $OUTPUT->error_text($err["azureappid"]);
				}
				 echo '</td><td>';
				print_string('auth_appid', 'auth_azureoauth2') ;	



				// domain id.
				echo '<tr>
						<td align="right"><label for="auth_domain_key">';

				print_string('auth_domain_key', 'auth_azureoauth2');

				echo '</label></td><td>';

				echo html_writer::empty_tag('input', array('type' => 'text', 'id' => 'azuredomain', 'name' => 'azuredomain',
							'class' => 'azuredomain', 'value' => $config->azuredomain));

				if (isset($err["azuredomain"])) {
					echo $OUTPUT->error_text($err["azuredomain"]);
				}
				 echo '</td><td>';
				print_string('auth_domain', 'auth_azureoauth2') ;		



				// Display buttons

				echo '<tr>
						<td align="right"><label for="oauth2displaybuttons">';

				print_string('oauth2displaybuttons', 'auth_azureoauth2');

				echo '</label></td><td>';

				$checked = empty($config->oauth2displaybuttons)?'':'checked';
				echo html_writer::checkbox('oauth2displaybuttons', 1, $checked, '',
					array('type' => 'checkbox', 'id' => 'oauth2displaybuttons', 'class' => 'oauth2displaybuttons'));

				if (isset($err["oauth2displaybuttons"])) {
					echo $OUTPUT->error_text($err["oauth2displaybuttons"]);
				}

				echo '</td><td>';

				$code = '<code>&lt;?php require_once($CFG-&gt;dirroot . \'/auth/azureoauth2/lib.php\'); auth_azureoauth2_display_buttons(); ?&gt;</code>';
				print_string('oauth2displaybuttonshelp', 'auth_azureoauth2', $code) ;

				echo '</td></tr>';


				// Block field options
				// Hidden email options - email must be set to: locked
				echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => 'locked',
							'name' => 'lockconfig_field_lock_email'));

				//display other field options
				foreach ($user_fields as $key => $user_field) {
					if ($user_field == 'email') {
						unset($user_fields[$key]);
					}
				}
				print_auth_lock_options('azureoauth2', $user_fields, get_string('auth_fieldlocks_help', 'auth'), false, false);



				echo '</table>';
			}

			/**
			 * Processes and stores configuration data for this authentication plugin.
			 */
			function process_config($config) {
				// set to defaults if undefined

				if (!isset ($config->azureclientid)) {
					$config->azureclientid = '';
				}
				if (!isset ($config->azureclientsecret)) {
					$config->azureclientsecret = '';
				}
				if (!isset ($config->azuretenantid)) {
					$config->azuretenantid = '';
				}
				if (!isset ($config->azureappid)) {
					$config->azureappid = '';
				}
				if (!isset ($config->azuredomain)) {
					$config->azuredomain = '';
				}
				if (!isset ($config->oauth2displaybuttons)) {
					$config->oauth2displaybuttons = 0;
				}

				// save settings

				set_config('azureclientid', $config->azureclientid, 'auth/azureoauth2');
				set_config('azureclientsecret', $config->azureclientsecret, 'auth/azureoauth2');
				set_config('azuretenantid', $config->azuretenantid, 'auth/azureoauth2');
				set_config('azureappid', $config->azureappid, 'auth/azureoauth2');
				set_config('azuredomain', $config->azuredomain, 'auth/azureoauth2');
				set_config('oauth2displaybuttons', $config->oauth2displaybuttons, 'auth/azureoauth2');

				return true;
			}

			/**
			 * Called when the user record is updated.
			 *
			 * We check there is no hack-attempt by a user to change his/her email address
			 *
			 * @param mixed $olduser     Userobject before modifications    (without system magic quotes)
			 * @param mixed $newuser     Userobject new modified userobject (without system magic quotes)
			 * @return boolean result
			 *
			 */
			function user_update($olduser, $newuser) {
				if ($olduser->email != $newuser->email) {
					return false;
				} else {
					return true;
				}	
			}

}