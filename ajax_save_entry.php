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
require_once('classes/HabitEntry.php');
require_once('classes/HabitEntryTwoDimensional.php');
require_once('classes/FlexiCalendar.php');
require_once('classes/FlexiCalendarUnit.php');
require_once('classes/Helper.php');

require_login();

$habitId = required_param('habitId', PARAM_INT);
$timestamp = required_param('timestamp', PARAM_INT);
$duration = required_param('periodDuration', PARAM_INT);
$x = required_param('x', PARAM_INT);
$y = required_param('y', PARAM_INT);

$habit = new gh\Habit($habitId);

$entry = new gh\HabitEntryTwoDimensional($habit, $USER->id, $timestamp, $duration, $x, $y);

if ($entry->alreadyExists()) {
    $entry->update();
} else {
    $entry->save();
}