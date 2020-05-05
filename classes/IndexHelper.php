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

namespace local_good_habits;

defined('MOODLE_INTERNAL') || die();

class IndexHelper {

    public static function check_for_new_habit() {
        global $PAGE, $USER;

        $name = optional_param('new-habit-name', '', PARAM_TEXT);
        if (!$name) {
            return null;
        }
        require_sesskey();

        $isglobal = optional_param('isglobal', 0, PARAM_INT);

        if ($isglobal) {
            require_capability('local/good_habits:manage_global_habits', $PAGE->context);
        } else {
            require_capability('local/good_habits:manage_personal_habits', $PAGE->context);
        }

        $desc = optional_param('new-habit-desc', '', PARAM_TEXT);
        global $DB;

        if ($DB->record_exists('local_good_habits_item', array('name' => $name))) {
            print_error('Habit already exists with name ' . $name);
        }
        $record = new \stdClass();
        $record->gh_id = 0;
        if (!$isglobal) {
            $record->userid = $USER->id;
        }
        $record->name = $name;
        $record->description = $desc;
        $record->colour = '';
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        $DB->insert_record('local_good_habits_item', $record);

        $msg = get_string('habit_added', 'local_good_habits');

        $url = new \moodle_url('/local/good_habits/index.php');
        redirect($url, $msg);
    }

    public static function check_delete_entries() {
        global $USER, $PAGE;
        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'delete-all-entries') {
            require_sesskey();
            require_capability('local/good_habits:manage_entries', $PAGE->context);
            Helper::delete_entries($USER->id);

            $msg = get_string('habit_entries_deleted', 'local_good_habits');

            $url = new \moodle_url('/local/good_habits/index.php');

            redirect($url, $msg);
        }
    }

    public static function get_habits() {
        global $DB, $USER;
        $sql = 'SELECT * FROM {local_good_habits_item} WHERE userid = :userid OR userid IS NULL OR userid = 0';
        $records = $DB->get_records_sql($sql, array('userid' => $USER->id));
        $arr = array();
        foreach ($records as $k => $habit) {
            $arr[$k] = new Habit($habit->id);
        }
        return $arr;
    }
}