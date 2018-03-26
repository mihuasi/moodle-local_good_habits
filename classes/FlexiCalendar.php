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

class FlexiCalendar {

    private $periodDuration;

    private $baseDate;

    private $numEntries;

    private $displaySet;

    const DEFAULT_NUM_ENTRIES = 8;

    public function __construct($periodDuration, \DateTime $baseDate, $numEntries)
    {
        $this->initPeriodDuration($periodDuration);
        $this->baseDate = $baseDate;
        $this->numEntries = $numEntries;
        $this->generateDisplaySet();
    }

    public function getDisplaySet() {
        return $this->displaySet;
    }

    public function getPeriodDuration() {
        return $this->periodDuration;
    }

    private function initPeriodDuration($periodDuration) {
        $periodDuration = (int) $periodDuration;
        if (!Helper::validatePeriodDuration($periodDuration)) {
            print_error('err');
        }
        $this->periodDuration = $periodDuration;
    }

    private function currentSpan() {
        return ($this->numEntries * $this->periodDuration);
    }

    private function generateDisplaySet() {
        $numDaysAgo = $this->currentSpan() -1;
        $startDate = Helper::newDateTime($this->baseDate, '-' . $numDaysAgo . ' day');
        $currentDate = Helper::newDateTime($startDate);
        $displaySet = array();
        while (count($displaySet) < $this->numEntries) {
            $unit = new FlexiCalendarUnit();
            $unit->setTimestamp($currentDate->getTimestamp());
            $unit->setPeriodDuration($this->periodDuration);
            $displaySet[] = $unit;
            $currentDate->modify('+'.$this->periodDuration.' day');
        }
        $this->displaySet = $displaySet;
    }

    public function getBackURL() {
        $backDate = Helper::newDateTime($this->baseDate, '-' . $this->currentSpan() . ' day');
        $backDateMySQL = Helper::DateTimeToMySQL($backDate);
        $url = new \moodle_url('/local/good_habits/index.php', array('toDate' => $backDateMySQL));
        return $url;
    }

    public function getForwardURL() {
        $forwardDate = Helper::newDateTime($this->baseDate, '+' . $this->currentSpan(). ' day');
        $threshold = Helper::getEndPeriodDateTime($this->periodDuration, new \DateTime());
        if ($forwardDate->getTimestamp() > $threshold->getTimestamp()) {
            $forwardDate = $threshold;
            if ($forwardDate->getTimestamp() <= $this->baseDate->getTimestamp()) {
                return null;
            }
        }
        $fwdDateMySQL = Helper::DateTimeToMySQL($forwardDate);
        $url = new \moodle_url('/local/good_habits/index.php', array('toDate' => $fwdDateMySQL));
        return $url;
    }


}