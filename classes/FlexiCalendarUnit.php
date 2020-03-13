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

class FlexiCalendarUnit extends \DateTime {

    private $periodDuration;

    public function setPeriodDuration($periodDuration) {
        if (!Helper::validatePeriodDuration($periodDuration)) {
            print_error('err');
        }
        $this->periodDuration = $periodDuration;
    }

    public function displayUnit() {
        if (empty($this->periodDuration)) {
            print_error('must set periodDuration first');
        }
        $offset = $this->periodDuration - 1;
        $topLineDateTime = Helper::newDateTime($this, '-' . $offset . ' day');
        $topLine = $topLineDateTime->format('d/m') . ' - ';
        $bottomLine = $this->format('d/m');
        switch ($this->periodDuration) {
            case 1:
                $topLine = $this->format('D');
                $bottomLine = $this->format('d');
                break;
            case 7:
                $topLine = get_string('week_displayunit', 'local_good_habits');
                $bottomLine = $this->format('W');
                break;
        }
        $display = array(
            'topLine' => $topLine,
            'bottomLine' => $bottomLine,
        );
        return $display;
    }

    public function displayMonth($display = false) {
        $offset = $this->periodDuration;
        $previousDateTime = Helper::newDateTime($this, '-' . $offset . ' day');
        $previousMonth = $previousDateTime->format('M');
        $currentMonth = $this->format('M');
        if ($display OR $previousMonth != $currentMonth) {
            return $currentMonth;
        }
        return '';
    }

    public function getClasses() {
        $month = $this->format('F');
        $month = strtolower($month);
        $classes = array($month, 'time-unit-' . $this->getTimestamp());
        return $classes;
    }
}