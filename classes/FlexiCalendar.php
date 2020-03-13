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

class FlexiCalendar {

    private $periodduration;

    private $basedate;

    private $numentries;

    private $displayset;

    const DEFAULT_NUM_ENTRIES = 8;

    public function __construct($periodduration, \DateTime $basedate, $numentries) {
        $this->init_period_duration($periodduration);
        $this->basedate = $basedate;
        $this->numentries = $numentries;
        $this->generate_display_set();
    }

    public function get_display_set() {
        return $this->displayset;
    }

    public function get_period_duration() {
        return $this->periodduration;
    }

    private function init_period_duration($periodduration) {
        $periodduration = (int) $periodduration;
        if (!Helper::validate_period_duration($periodduration)) {
            print_error('err');
        }
        $this->periodduration = $periodduration;
    }

    private function current_span() {
        return ($this->numentries * $this->periodduration);
    }

    private function generate_display_set() {
        $numdaysago = $this->current_span() - 1;
        $startdate = Helper::new_date_time($this->basedate, '-' . $numdaysago . ' day');
        $currentdate = Helper::new_date_time($startdate);
        $displayset = array();
        while (count($displayset) < $this->numentries) {
            $unit = new FlexiCalendarUnit();
            $unit->setTimestamp($currentdate->getTimestamp());
            $unit->set_period_duration($this->periodduration);
            $displayset[] = $unit;
            $currentdate->modify('+'.$this->periodduration.' day');
        }
        $this->displayset = $displayset;
    }

    public function get_back_url() {
        $backdate = Helper::new_date_time($this->basedate, '-' . $this->current_span() . ' day');
        $backdatemysql = Helper::date_time_to_mysql($backdate);
        $url = new \moodle_url('/local/good_habits/index.php', array('toDate' => $backdatemysql));
        return $url;
    }

    public function get_forward_url() {
        $forwarddate = Helper::new_date_time($this->basedate, '+' . $this->current_span(). ' day');
        $threshold = Helper::getEndPeriodDateTime($this->periodduration, new \DateTime());
        if ($forwarddate->getTimestamp() > $threshold->getTimestamp()) {
            $forwarddate = $threshold;
            if ($forwarddate->getTimestamp() <= $this->basedate->getTimestamp()) {
                return null;
            }
        }
        $forwarddatemysql = Helper::date_time_to_mysql($forwarddate);
        $url = new \moodle_url('/local/good_habits/index.php', array('toDate' => $forwarddatemysql));
        return $url;
    }


}