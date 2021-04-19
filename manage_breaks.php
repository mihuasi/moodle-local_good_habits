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
require_once "{$CFG->libdir}/formslib.php";
require_once($CFG->dirroot . '/local/good_habits/classes/form/add_break.php');
require_once($CFG->dirroot . '/local/good_habits/classes/BreaksHelper.php');

require_login();

$context = context_system::instance();

require_capability('local/good_habits:manage_personal_breaks', $context);
$pagetitle = get_string('manage_breaks_title', 'local_good_habits');

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->set_url('/local/good_habits/manage_breaks.php');

$renderer = $PAGE->get_renderer('local_good_habits');

gh\BreaksHelper::check_delete_break();

$table = new html_table();

$fromtext = get_string('fromdate_text', 'local_good_habits');
$totext = get_string('todate_text', 'local_good_habits');
$actionstext = get_string('actions', 'local_good_habits');
$table->head = array($fromtext, $totext, $actionstext);

$mform = new add_break();

if ($data = $mform->get_data()) {
    gh\BreaksHelper::add_personal_break($data);
    $msg = get_string('break_added', 'local_good_habits');
    redirect($CFG->wwwroot . '/local/good_habits/manage_breaks.php', $msg);
}

$breaks = gh\BreaksHelper::get_personal_breaks();

foreach ($breaks as $break) {
    $row = array();
    $row[] = userdate($break->timestart, '%A %d %h, %G');
    $row[] = userdate($break->timeend, '%A %d %h, %G');
    $delparams = array('action' => 'delete', 'breakid' => $break->id, 'sesskey' => sesskey());
    $deleteurl = new moodle_url('/local/good_habits/manage_breaks.php', $delparams);
    $deltext = get_string('delete', 'local_good_habits');
    $row[] = html_writer::link($deleteurl, $deltext);
    $table->data[] = $row;
}

echo $OUTPUT->header();

if ($breaks) {
    echo html_writer::table($table);
}

echo html_writer::start_div('add_break');
$text = get_string('addbreak_submit_text', 'local_good_habits');
echo html_writer::tag('p', $text, array('class' => 'add_break'));
$mform->display();
echo html_writer::end_div();

echo $renderer->print_home_link();

echo $OUTPUT->footer();