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

class Habit {

    public $id;

    public function __construct($id) {
        $this->id = $id;
        $this->init();
    }

    private function init() {
        global $DB;
        $habitrecord = $DB->get_record('local_good_habits_item', array('id' => $this->id));
        if (!$habitrecord) {
            print_error('err');
        }
        foreach ($habitrecord as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function is_global() {
        if (!$this->userid) {
            return true;
        }
        return false;
    }

    public function get_entries($userid, $periodduration) {
        global $DB;
        $params = array('habit_id' => $this->id, 'userid' => $userid, 'period_duration' => $periodduration);
        $entries = $DB->get_records('local_good_habits_entry', $params);
        $entriesbytime = array();
        foreach ($entries as $entry) {
            $entriesbytime[$entry->endofperiod_timestamp] = $entry;
        }
        return $entriesbytime;
    }

    public function delete() {
        global $DB;
        $DB->delete_records('local_good_habits_item', array('id' => $this->id));
        $this->delete_orphans();
    }

    private function delete_orphans() {
        global $DB;
        $DB->delete_records('local_good_habits_entry', array('habit_id' => $this->id));
    }
}