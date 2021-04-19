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

class BreaksHelper {

    public static function add_personal_break($data) {
        global $DB, $USER;
        $userid = $USER->id;
        $timestart = $data->fromdate;
        $timeend = $data->todate;
        $break = $DB->get_record('local_good_habits_break', array(
            'userid' => $userid,
            'timestart' => $timestart,
            'timeend' => $timeend
        ));
        if ($break) {
            return null;
        }
        $break = new \stdClass();
        $break->userid = $userid;
        $break->createdby = $userid;
        $break->timestart = $timestart;
        $break->timeend = $timeend;
        $break->timecreated = time();
        $break->timemodified = $break->timecreated;
        $DB->insert_record('local_good_habits_break', $break);
    }

    public static function get_personal_breaks() {
        global $DB, $USER;
        $userid = $USER->id;
        $breaks = $DB->get_records('local_good_habits_break', array(
            'userid' => $userid));
        return $breaks;
    }

    public static function check_delete_break() {
        global $CFG;
        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'delete') {
            require_sesskey();
            $breakid = required_param('breakid', PARAM_INT);
            static::delete_break($breakid);
            $msg = get_string('break_deleted', 'local_good_habits');
            redirect($CFG->wwwroot . '/local/good_habits/manage_breaks.php', $msg);
        }
    }

    public static function delete_break($breakid) {
        global $DB, $USER;
        $DB->delete_records('local_good_habits_break', array('id' => $breakid, 'createdby' => $USER->id));
    }

    public static function is_in_a_break($timestamp) {
        $breaks = static::get_personal_breaks();
        foreach ($breaks as $break) {
            $start = $break->timestart;
            $end = $break->timeend;
            if ($timestamp >= $start AND $timestamp <= $end) {
                return true;
            }
        }
        return false;
    }
}