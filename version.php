<?php
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Azure Oauth2 authentication plugin.
 *
 * @package    auth
 * @subpackage azureoauth2
 * @copyright  2014 Adam Matara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * ### Credits - code forked from - http://mouneyrac.github.io/moodle-auth_googleoauth2/).
 * [CSS social buttons](http://zocial.smcllns.com/) ###
 */

defined('MOODLE_INTERNAL') || die();
$plugin->component = 'auth_azureoauth2';
$plugin->version  = 2014112100;
$plugin->requires = 2014101000;   // Requires Moodle 2.8 or later
$plugin->release = '0.1 (Build: 2014112100)';
$plugin->maturity = MATURITY_ALPHA;             // this version's maturity level
