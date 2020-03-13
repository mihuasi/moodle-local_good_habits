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
require_once('classes/FlexiCalendar.php');
require_once('classes/FlexiCalendarUnit.php');
require_once('classes/Helper.php');

require_login();

$context = context_system::instance();

require_capability('local/good_habits:view', $context);

$toDate = optional_param('toDate', null, PARAM_TEXT);

$pageTitle = get_string('plugin_title', 'local_good_habits');

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pageTitle);
$PAGE->set_heading($pageTitle);

$PAGE->set_url('/local/good_habits/index.php');

//$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');

$PAGE->requires->js('/local/good_habits/talentgrid/talentgrid-plugin.js', true);
$PAGE->requires->js('/local/good_habits/js/calendar.js', false);

$PAGE->requires->css('/local/good_habits/talentgrid/talentgrid-test.css');

$renderer = $PAGE->get_renderer('local_good_habits');

$periodDuration = gh\Helper::get_period_duration();
$numEntries = 8;

if ($toDate) {
    $currentDate = new DateTime($toDate);
} else {
    $currentDate = new DateTime();
}

gh\Helper::checkForNewHabit();

gh\Helper::checkDeleteEntries();

$baseDate = gh\Helper::getEndPeriodDateTime($periodDuration, $currentDate);

$calendar = new gh\FlexiCalendar($periodDuration, $baseDate, $numEntries);

$habits = gh\Helper::getHabits();

echo $OUTPUT->header();

echo $renderer->printHiddenData();

echo $renderer->timePeriodSelector(gh\Helper::possiblePeriodDurations(), $periodDuration);

$calendarHtml = $renderer->printCalendar($calendar);

$habitsHtml = $renderer->printHabits($calendar, $habits);

echo $renderer->printModule($calendarHtml, $habitsHtml);

if (has_capability('local/good_habits:manage_entries', $context)) {
    $renderer->printDeleteMyEntries();
}

echo $OUTPUT->footer();

