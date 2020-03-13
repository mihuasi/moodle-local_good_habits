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

class Helper {

    public static function validate_period_duration($periodduration) {
        $possiblevals = array_keys(static::possible_period_durations());
        return in_array($periodduration, $possiblevals) or 1;
    }

    public static function possible_period_durations() {
        $vals = array(
            1 => get_string('by_day', 'local_good_habits'),
            3 => get_string('x_days', 'local_good_habits', 3),
            5 => get_string('x_days', 'local_good_habits', 5),
            7 => get_string('by_week', 'local_good_habits', 3),
        );
        return $vals;
    }

    public static function get_end_period_timestamp($periodduration, \DateTime $basedate) {
        $timestamp = $basedate->getTimestamp();
        $days = static::unix_days($timestamp);
        $fraction = $days / $periodduration;
        $endperiodnumdays = floor($fraction) * ($periodduration);
        if ($endperiodnumdays < $days) {
            $diff = $days - $endperiodnumdays;
            $endperiodnumdays += $periodduration;
        }
        $endperiodtime = static::days_to_time($endperiodnumdays);
        return $endperiodtime;
    }

    public static function get_end_period_date_time($periodduration, \DateTime $basedate) {
        $timestamp = static::get_end_period_timestamp($periodduration, $basedate);
        return static::timestamp_to_date_time($timestamp);
    }

    public static function unix_days($timestamp) {
        $numdays = $timestamp / 60 / 60 / 24;
        return floor($numdays);
    }

    public static function days_to_time($days) {
        return $days * 60 * 60 * 24;
    }

    public static function timestamp_to_date_time($timestamp) {
        $dt = new \DateTime();
        $dt->setTimestamp($timestamp);
        return $dt;
    }

    public static function new_date_time(\DateTime $dt, $offset = null) {
        $newdt = clone $dt;
        if ($offset) {
            $newdt->modify($offset);
        }
        return $newdt;
    }

    public static function date_time_to_mysql(\DateTime $dt) {
        return $dt->format('Y-m-d');
    }

    public static function display_year($displayset) {
        $firstunit = reset($displayset);

        if ($firstunit->format('Y') != date('Y')) {
            return $firstunit->format('Y');
        }
        return '';
    }

    public static function get_habits() {
        global $DB;
        $records = $DB->get_records('gh_habit');
        $arr = array();
        foreach ($records as $k => $habit) {
            $arr[$k] = new Habit($habit->id);
        }
        return $arr;
    }

    public static function get_period_duration() {
        $default = 7;
        $userprefname = 'good-habits-period-duration';
        $selected = optional_param('time-period-selector', 0, PARAM_INT);
        if (!$selected) {
            $userpref = get_user_preferences($userprefname, $default);
            return $userpref;
        }
        require_sesskey();
        if (!static::validate_period_duration($selected)) {
            print_error('not valid selection');
        }
        set_user_preference($userprefname, $selected);
        return $selected;
    }

    public static function check_for_new_habit() {
        global $PAGE;

        $name = optional_param('new-habit-name', '', PARAM_TEXT);
        if (!$name) {
            return null;
        }
        require_sesskey();
        require_capability('local/good_habits:manage_habits', $PAGE->context);

        $desc = optional_param('new-habit-desc', '', PARAM_TEXT);
        global $DB;

        if ($DB->record_exists('gh_habit', array('name' => $name))) {
            print_error('Habit already exists with name ' . $name);
        }
        $record = new \stdClass();
        $record->gh_id = 0;
        $record->name = $name;
        $record->description = $desc;
        $record->colour = '';
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        $DB->insert_record('gh_habit', $record);
    }

    public static function check_delete_entries() {
        global $USER, $PAGE;
        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'delete-all-entries') {
            require_sesskey();
            require_capability('local/good_habits:manage_entries', $PAGE->context);
            static::delete_entries($USER->id);
        }
    }

    public static function delete_all_entries() {
        global $DB;
        $DB->delete_records('gh_habit_entry', array());
    }

    public static function delete_entries($userid) {
        global $DB;
        $DB->delete_records('gh_habit_entry', array('userid' => $userid));
    }

    public static function lang_string_as_data($ids, $module = 'local_good_habits') {
        $data = '';
        foreach ($ids as $id) {
            $data .= ' data-lang-' .$id . '="'. get_string($id, $module).'" ';
        }
        return $data;
    }
}