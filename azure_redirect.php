<?php

/*
 * 
 */
require('../../config.php');
$code = optional_param('code', '', PARAM_TEXT);

if (empty($code)) {
    throw new moodle_exception('azure_failure');
}

// Ensure that this is no request forgery going on, and that the user
// sending us this connect request is the user that was supposed to.
if ($_SESSION['STATETOKEN'] !== required_param('state', PARAM_TEXT)) {
    throw new moodle_exception('Invalid state parameter');
}

$loginurl = '/login/index.php';
if (!empty($CFG->alternateloginurl)) {
    $loginurl = $CFG->alternateloginurl;
}
$url = new moodle_url($loginurl, array('code' => $code, 'authprovider' => 'azure'));
redirect($url);
?>
