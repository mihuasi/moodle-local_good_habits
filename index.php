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

$todate = optional_param('toDate', null, PARAM_TEXT);

$pagetitle = get_string('plugin_title', 'local_good_habits');

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->set_url('/local/good_habits/index.php');

$PAGE->requires->jquery_plugin('ui');

$PAGE->requires->js('/local/good_habits/talentgrid/talentgrid-plugin.js', true);
$PAGE->requires->js('/local/good_habits/js/calendar.js', false);

$PAGE->requires->css('/local/good_habits/talentgrid/talentgrid-test.css');

$renderer = $PAGE->get_renderer('local_good_habits');

$periodduration = gh\Helper::get_period_duration();
$numentries = 8;

if ($todate) {
    $currentdate = new DateTime($todate);
} else {
    $currentdate = new DateTime();
}

gh\Helper::check_for_new_habit();

gh\Helper::check_delete_entries();

$baseDate = gh\Helper::get_end_period_date_time($periodduration, $currentdate);

$calendar = new gh\FlexiCalendar($periodduration, $baseDate, $numentries);

$habits = gh\Helper::get_habits();

echo $OUTPUT->header();

echo $renderer->print_hidden_data();

echo $renderer->time_period_selector(gh\Helper::possible_period_durations(), $periodduration);

$calendarhtml = $renderer->print_calendar($calendar);

$habitshtml = $renderer->print_habits($calendar, $habits);

echo $renderer->print_module($calendarhtml, $habitshtml);

if (has_capability('local/good_habits:manage_entries', $context)) {
    $renderer->print_delete_my_entries();
}

echo $OUTPUT->footer();

