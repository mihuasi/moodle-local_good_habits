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

    protected $userId;

    protected $endOfPeriodTimestamp;

    protected $periodDuration;

    protected $entryType;

    protected $existingRecord;

    const ENTRY_TYPE_TWO_DIMENSIONAL = 'two-dimensional';

    public function __construct(Habit $habit, $userId, $endOfPeriodTimestamp, $periodDuration)
    {
        $this->habit = $habit;
        $this->userId = $userId;
        $this->endOfPeriodTimestamp = $endOfPeriodTimestamp;
        $this->periodDuration = $periodDuration;
        $this->initExistingRecord();
    }

    public function initExistingRecord() {
        global $DB;
        $this->existingRecord = $DB->get_record('gh_habit_entry', array('habit_id' => $this->habit->id, 'userid' => $this->userId,
            'entry_type' => $this->entryType, 'period_duration' => $this->periodDuration, 'endofperiod_timestamp' => $this->endOfPeriodTimestamp));
    }

    public function alreadyExists() {

        return (boolean) $this->existingRecord;
    }

    abstract function save();
    abstract function update();
}