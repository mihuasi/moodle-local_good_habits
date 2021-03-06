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
 * @package   local_good_habits
 * @copyright 2018 Joe Cape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_good_habits as gh;

require_once('../../config.php');
require_once('classes/Habit.php');
require_once('classes/Helper.php');

define('AJAX_SCRIPT', true);

require_login();

$context = context_system::instance();

require_capability('local/good_habits:view', $context);

$habitid = required_param('habitId', PARAM_INT);

$habit = new gh\Habit($habitid);

if ($habit->is_global()) {
    require_capability('local/good_habits:manage_global_habits', $context);
} else {
    require_capability('local/good_habits:manage_personal_habits', $context);
    if ($USER->id != $habit->userid) {
        print_error('Trying to edit a personal habit that does not belong to you');
    }
}

$habit->delete();