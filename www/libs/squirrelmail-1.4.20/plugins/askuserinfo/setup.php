<?php
/**
 *
 * Ask User Info plugin 1.1
 *
 * When user logs in and doesn't have email_address or full_name set,
 * direct the user automatically to the relevant options page.
 *
 * Copyright (c) 2003, 2007 Thijs Kinkhorst
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * @package plugins
 * @subpackage askuserinfo
 */

function squirrelmail_plugin_init_askuserinfo()
{
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks['webmail_top']['askuserinfo'] = 'askuserinfo_check';
    $squirrelmail_plugin_hooks['optpage_loadhook_personal']['askuserinfo'] = 'askuserinfo_message';
}

function askuserinfo_check()
{
    global $data_dir, $username, $right_frame;

    $email_address = getPref($data_dir, $username, 'email_address');
    $full_name     = getPref($data_dir, $username, 'full_name');

    if( trim($email_address) == '' || trim($full_name) == '' )
    {
        $_SESSION['optpage'] = 'personal';
        $_SESSION['askinfo'] = 'yes';
        $right_frame = 'options.php';
    }
}

function askuserinfo_message()
{
    global $color;

    sqgetGlobalVar('askinfo', $askinfo, SQ_SESSION);

    if(isset($askinfo) && $askinfo)
    {
        echo '<p align="center"><b><font color="' . $color[2] .
             '">NOTE: You need to supply your full name and email address.'.
             '</font></b></p>';

        unset($_SESSION['optpage'], $_SESSION['askinfo']);
    }
}

function askuserinfo_info() {
    return array ( 'version' => '1.1' );
}

function askuserinfo_version() {
    $info = askuserinfo_info();
    return $info['version'];
}

