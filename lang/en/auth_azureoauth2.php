<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'auth_azure', language 'en'
 *
 * @package   auth_azure
 * @author Adam Matara
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['providername'] = 'Office 365';
$string['pluginname'] = 'Azure AD OAuth2';
$string['auth_azureoauth2description'] = 'This method provides authentication against Azure AD (Office 365). If the given username and password are valid, Moodle can create a new user entry in its database.  To disable new account creation see ‘<a href="'.$CFG->wwwroot.'/admin/search.php?query=authpreventaccountcreation">Prevent account creation when authenticating</a>’.  This module can read user attributes from Azure AD and prefill wanted fields in Moodle.';
$string['auth_azureclientid'] = 'Generate your clientid, secret, and tenantid using the <a href="https://manage.windowsazure.com/">Windows Azure portal</a>:
<br/>Redirect URL: '. $CFG->httpswwwroot . '/auth/azureoauth2/azure_redirect.php';
$string['auth_azureclientid_key'] = 'Client ID';
$string['auth_azureclientsecret'] = 'See above.';
$string['auth_azureclientsecret_key'] = 'Client Secret';
$string['auth_azuretenantid'] = 'On the Azure Management Portal click the <b>View Endpoints</b> button and copy the GUID from any endpoint available.  For example,  from https://login.windows.net/f9d3e034-03ca-4e58-8799-3c112417378d/oauth2/token?api-version=1.0 copy and paste f9d3e034-03ca-4e58-8799-3c112417378d';
$string['auth_azuretenantid_key'] = 'Tenant ID';
$string['auth_appid'] = 'See Above.';
$string['auth_appid_key'] = 'App ID';
$string['auth_domain'] = 'Set the domain login hint - this will redirect users to your Azure organisational login page associated with your Tenant ID. For example, contoso.com';
$string['auth_domain_key'] = 'Domain';
$string['couldnotauthenticate'] = 'The authentication failed - Please try to sign-in again.';
$string['couldnotgetazureaccesstoken'] = 'The authentication provider sent us a communication error. Please try to sign-in again.';
$string['couldnotauthenticateuserlogin'] = 'Authentication method error.<br/>
Please try to login again with your username and password.<br/>
<br/>
<a href="{$a->loginpage}">Try again</a>.<br/>
<a href="{$a->forgotpass}">Forgot your password</a>?';
$string['oauth2displaybuttons'] = 'Display buttons on login page';
$string['oauth2displaybuttonshelp'] = 'Display the login with Azure logo on the top of the login page. If you want to position the buttons yourself in your login page, you can keep this option disabled and add the following code:
{$a}';
$string['auth_azuresettings'] ='Settings';
$string['emailaddressmustbeverified'] = 'Your email address is not verified by the authentication method you selected. You likely have forgotten to click on a "verify email address" link that Google or Facebook should have sent you during your subscribtion to their service.';
$string['auth_sign-in_with'] = 'Sign-in with {$a->providername}';
$string['moreproviderlink'] = 'Sign-in with another service.';
$string['signinwithanaccount'] = 'Log in with:';
$string['noaccountyet'] = 'You do not have permission to use the site yet. Please contact your administrator request your account to be activated.';
