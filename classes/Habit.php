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

class Habit {

    public $id;

    public function __construct($id)
    {
        $this->id = $id;
        $this->init();
    }

    private function init() {
        global $DB;
        $habit_record = $DB->get_record('gh_habit', array('id' => $this->id));
        if (!$habit_record) {
            print_error('err');
        }
        foreach ($habit_record as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function getEntries($userId, $period_duration) {
        global $DB;
        $entries = $DB->get_records('gh_habit_entry', array('habit_id' => $this->id, 'userid' => $userId, 'period_duration' => $period_duration));
        $entries_by_time = array();
        foreach ($entries as $entry) {
            $entries_by_time[$entry->endofperiod_timestamp] = $entry;
        }
        return $entries_by_time;
    }

    public function delete() {
        global $DB;
        $DB->delete_records('gh_habit', array('id' => $this->id));
        $this->deleteOrphans();
    }

    private function deleteOrphans() {
        global $DB;
        $DB->delete_records('gh_habit_entry', array('habit_id' => $this->id));
    }
}