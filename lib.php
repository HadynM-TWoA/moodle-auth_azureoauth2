<?php
// This file is part of Oauth2 authentication plugin for Moodle.
//
// azureoauth2 authentication plugin for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// azureoauth2 authentication plugin for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with azureoauth2 authentication plugin for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains lib functions for the azureoauth2 authentication plugin.
 *
/**
 * @author Adam Matara
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * ### Credits - code forked from - http://mouneyrac.github.io/moodle-auth_googleoauth2/).
 * [CSS social buttons](http://zocial.smcllns.com/) ###
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * hackoauth_add_to_log is a quick  to avoid add_to_log debugging
 */
function oauth_add_to_log($courseid, $module, $action, $url='', $info='', $cm=0, $user=0) {
    if (function_exists('get_log_manager')) {
        $manager = get_log_manager();
        $manager->legacy_add_to_log($courseid, $module, $action, $url, $info, $cm, $user);
    } else if (function_exists('add_to_log')) {
        add_to_log($courseid, $module, $action, $url, $info, $cm, $user);
    }
}

/**
 * Get (generate) session state token.
 *
 * @return string the state token.
 */
function auth_azureoauth2_get_state_token() {
    // Create a state token to prevent request forgery.
    // Store it in the session for later validation.
    if (empty($_SESSION['STATETOKEN'])) {
        $state = md5(rand());
        $_SESSION['STATETOKEN'] = $state;
    }
    return $_SESSION['STATETOKEN'];
}

/**
 * For backwards compatibility only: this echoes the html created in auth_azureoauth2_render_buttons
 */
function auth_azureoauth2_display_buttons() {
    echo auth_azureoauth2_render_buttons();
}

/**
 * The very ugly code to render the html buttons.
 * TODO remove ugly html like center-tag and inline styles, implement a moodle renderer
 * @return string: returns the html for buttons and some JavaScript 
 */
function auth_azureoauth2_render_buttons() {
	global $CFG;
	$html ='';
	
    if (!is_enabled_auth('azureoauth2')) {
        return $html;
    }

	$html .= '
    <script language="javascript">
        linkElement = document.createElement("link");
        linkElement.rel = "stylesheet";
        linkElement.href = "' . $CFG->httpswwwroot . '/auth/azureoauth2/csssocialbuttons/css/zocial.css";
        document.head.appendChild(linkElement);
    </script>
    ';
	
	//get previous auth provider
	$allauthproviders = optional_param('allauthproviders', false, PARAM_BOOL);
	$cookiename = 'MOODLEAZUREOAUTH2_'.$CFG->sessioncookie;
	if (empty($_COOKIE[$cookiename])) {
		$authprovider = '';
	} else {
		$authprovider = $_COOKIE[$cookiename];
		
	}
	
	$html .= "<center>";
	$html .= "<div style=\"width:'1%'\">";
    $a = new stdClass();

    $a->providername = 'Azure';
    $providerisenabled = get_config('auth/azureoauth2', 'azureclientid');
	
  
	$displayprovider = ((empty($authprovider) || $authprovider == 'azure' || $allauthproviders) && $providerisenabled);
	$providerdisplaystyle = $displayprovider?'display:inline-block;padding:10px;':'display:none;';
	$html .= '<div class="singinprovider" style="'. $providerdisplaystyle .'">
            <a class="zocial windows" href="https://login.windows.net/'. get_config('auth/azureoauth2', 'azuretenantid') .'/oauth2/authorize?api-version=1.0&client_id='. get_config('auth/azureoauth2', 'azureappid') .'&redirect_uri='. $CFG->httpswwwroot .'/auth/azureoauth2/azure_redirect.php&state=' .auth_azureoauth2_get_state_token(). '&response_type=code&domain_hint='.get_config('auth/azureoauth2', 'azuredomain') . '">
                '.get_string('auth_sign-in_with','auth_azureoauth2', $a).'
            </a>
        </div>
    </div>';

	// if (!empty($authprovider) and $providerscount>1) {
		// $html .= '<br /><br /> 
           // <div class="moreproviderlink">
                // <a href="'. $CFG->$CFG->httpswwwroot . (!empty($CFG->alternateloginurl) ? $CFG->alternateloginurl : '/login/index.php') . '?allauthproviders=true' .'" onclick="changecss(\'singinprovider\',\'display\',\'inline-block\');">
                    // '. get_string('moreproviderlink', 'auth_azureoauth2').'
                // </a>
            // </div>';
	// }

	$html .= "</center>";	
	return $html;
}
