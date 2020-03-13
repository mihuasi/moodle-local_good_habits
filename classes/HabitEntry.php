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

abstract class HabitEntry {

    protected $habit;

    protected $user_id;

    protected $end_of_period_timestamp;

    protected $period_duration;

    protected $entryType;

    protected $existing_record;

    const ENTRY_TYPE_TWO_DIMENSIONAL = 'two-dimensional';

    public function __construct(Habit $habit, $user_id, $end_of_period_timestamp, $period_duration)
    {
        $this->habit = $habit;
        $this->user_id = $user_id;
        $this->end_of_period_timestamp = $end_of_period_timestamp;
        $this->period_duration = $period_duration;
        $this->init_existing_record();
    }

    public function init_existing_record() {
        global $DB;
        $this->existing_record = $DB->get_record('gh_habit_entry', array('habit_id' => $this->habit->id, 'userid' => $this->user_id,
            'entry_type' => $this->entryType, 'period_duration' => $this->period_duration, 'endofperiod_timestamp' => $this->end_of_period_timestamp));
    }

    public function already_exists() {

        return (boolean) $this->existing_record;
    }

    abstract function save();
    abstract function update();
}