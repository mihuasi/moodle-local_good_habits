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

abstract class HabitEntry {

    protected $habit;

    protected $userid;

    protected $endofperiodtimestamp;

    protected $periodduration;

    protected $entrytype;

    protected $existingrecord;

    const ENTRY_TYPE_TWO_DIMENSIONAL = 'two-dimensional';

    public function __construct(Habit $habit, $userid, $endofperiodtimestamp, $periodduration) {
        $this->habit = $habit;
        $this->userid = $userid;
        $this->endofperiodtimestamp = $endofperiodtimestamp;
        $this->periodduration = $periodduration;
        $this->init_existing_record();
    }

    public function init_existing_record() {
        global $DB;
        $params = array(
            'habit_id' => $this->habit->id,
            'userid' => $this->userid,
            'entry_type' => $this->entrytype,
            'period_duration' => $this->periodduration,
            'endofperiod_timestamp' => $this->endofperiodtimestamp);
        $this->existingrecord = $DB->get_record('local_good_habits_entry', $params);
    }

    public function already_exists() {

        return (boolean) $this->existingrecord;
    }

    abstract public function save();
    abstract public function update();
}