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

class HabitEntryTwoDimensional extends HabitEntry {

    protected $xVal;

    protected $yVal;

    public function __construct(Habit $habit, $userId, $endOfPeriodTimestamp, $periodDuration, $xVal, $yVal)
    {
        parent::__construct($habit, $userId, $endOfPeriodTimestamp, $periodDuration);
        $this->xVal = $xVal;
        $this->yVal = $yVal;
        $this->entryType = HabitEntry::ENTRY_TYPE_TWO_DIMENSIONAL;
    }

    public function save() {
        global $DB;
        $record = new \stdClass();
        $record->habit_id = $this->habit->id;
        $record->userid = $this->userId;
        $record->entry_type = $this->entryType;
        $record->period_duration = $this->periodDuration;
        $record->endofperiod_timestamp = $this->endOfPeriodTimestamp;
        $record->x_axis_val = $this->xVal;
        $record->y_axis_val = $this->yVal;
        $record->timecreated = time();
        $record->timemodified = time();
        $DB->insert_record('gh_habit_entry', $record);
    }

    public function update()
    {
        global $DB;
        if (!$this->existingRecord) {
            print_error('existingRecord not found');
        }
        $this->existingRecord->x_axis_val = $this->xVal;
        $this->existingRecord->y_axis_val = $this->yVal;
        $this->existingRecord->timemodified = time();
        $DB->update_record('gh_habit_entry', $this->existingRecord);
    }


}