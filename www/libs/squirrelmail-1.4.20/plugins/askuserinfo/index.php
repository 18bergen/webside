<?php

/**
 * index.php
 *
 * This file simply takes any attempt to view source files and sends those
 * people to the login screen. At this point no attempt is made to see if the
 * person is logged in or not.
 *
 * @copyright &copy; 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: index.php,v 1.11 2006/01/23 18:42:22 tokul Exp $
 * @package plugins
 * @subpackage askuserinfo
 */

header('Location: ../index.php');

