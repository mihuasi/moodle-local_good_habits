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

    private $periodduration;

    public function set_period_duration($periodduration) {
        if (!Helper::validate_period_duration($periodduration)) {
            print_error('err');
        }
        $this->periodduration = $periodduration;
    }

    public function display_unit() {
        if (empty($this->periodduration)) {
            print_error('must set periodDuration first');
        }
        $offset = $this->periodduration - 1;
        $toplinedatatime = Helper::new_date_time($this, '-' . $offset . ' day');
        $topline = $toplinedatatime->format('d/m') . ' - ';
        $bottomline = $this->format('d/m');
        switch ($this->periodduration) {
            case 1:
                $topline = $this->format('D');
                $bottomline = $this->format('d');
                break;
            case 7:
                $topline = get_string('week_displayunit', 'local_good_habits');
                $bottomline = $this->format('W');
                break;
        }
        $display = array(
            'topLine' => $topline,
            'bottomLine' => $bottomline,
        );
        return $display;
    }

    public function display_month($display = false) {
        $offset = $this->periodduration;
        $previousdatetime = Helper::new_date_time($this, '-' . $offset . ' day');
        $previousmonth = $previousdatetime->format('M');
        $currentmonth = $this->format('M');
        if ($display OR $previousmonth != $currentmonth) {
            return $currentmonth;
        }
        return '';
    }

    public function get_classes() {
        $month = $this->format('F');
        $month = strtolower($month);
        $classes = array($month, 'time-unit-' . $this->getTimestamp());
        return $classes;
    }
}