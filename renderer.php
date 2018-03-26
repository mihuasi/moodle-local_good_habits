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

use local_good_habits as gh;

class local_good_habits_renderer extends plugin_renderer_base {

    public function printCalendar(gh\FlexiCalendar $calendar) {
        $displaySet = $calendar->getDisplaySet();

        $periodDuration = $calendar->getPeriodDuration();

        $html = "<div class='calendar' data-period-duration='$periodDuration'>";
        $html .= "    <div class='dates'>";

        $year = gh\Helper::displayYear($displaySet);

        $html .= "        <div class='year'>$year</div>";

        $days = array();

        $backUrl = $calendar->getBackURL();
        $forwardUrl = $calendar->getForwardURL();

        foreach($displaySet as $k => $unit) {
            $isFirst = $k == 0;
            $isLast = $k == (count($displaySet) -1);
            $month = $unit->displayMonth($isFirst);
            $display = $unit->displayUnit();
            $topLine = $display['topLine'];
            $singleLineDisplay = $topLine . ' ' . $display['bottomLine'];
            if ($isFirst) {
                $topLine = $this->printBackLink($backUrl, $topLine);
            }
            if ($isLast && $forwardUrl) {
                $topLine = $this->printForwardLink($forwardUrl, $topLine);
            }
            $unitContents = '<div class="top-line">'.$topLine.'</div>';
            $unitContents .= '<div class="bottom-line">'.$display['bottomLine'].'</div>';

            $monthHtml = ($month) ? '<div class="month">'.$month.'</div>' : '';
            $day = '<div data-text="'.$singleLineDisplay.'" class="time-unit '.implode(' ', $unit->getClasses()).'">' .$monthHtml .$unitContents.'</div>';
            $days[] = $day;
        }

        $html .= implode('', $days);

        $html .= "    </div>";
        $html .= "</div>";
        return $html;
    }

    public function printHabits(gh\FlexiCalendar $calendar, $habits) {
        $arr = array();
        foreach ($habits as $habit) {
            $arr[] = $this->printHabit($calendar, $habit);
        }

        $arr[] = $this->printAddHabitEl();

        return '<div class="habits">' . implode('', $arr) . '</div>';
    }

    public function printHabit(gh\FlexiCalendar $calendar, gh\Habit $habit) {
        $html = "<div class='habit habit-".$habit->id."'>";

        $html .= '<div class="streak"></div>';

        $html .= '    <div class="title"><div class="habit-name">'.$habit->name.'</div><div class="description">'.$habit->description.'</div></div>';

        $html .= '    <div class="time-line">';

        $html .= $this->printCheckmarks($calendar, $habit);

        $html .= '        <div class="clear-both"></div>';

        $html .= '    </div>';


        $html .= '    <div class="clear-both"></div>';

        $html .= "</div>";

        $html .= '<div class="habit-grid-container habit-grid-container-'.$habit->id.'"></div>';

        return $html;
    }

    private function printCheckmarks(gh\FlexiCalendar $calendar, gh\Habit $habit) {
        global $USER;

        $html = '';

        $displaySet = $calendar->getDisplaySet();

        $entries = $habit->getEntries($USER->id, $calendar->getPeriodDuration());

        foreach ($displaySet as $k => $unit) {
            $dataXYtxt = '';
            $txt = '<div class="empty-day">  </div>';
            if (isset($entries[$unit->getTimestamp()])) {
                $entry = $entries[$unit->getTimestamp()];
                $dataXYtxt = ' data-x="'.$entry->x_axis_val.'" data-y="'.$entry->y_axis_val.'" ';
                $txt = $entry->x_axis_val . ' / ' . $entry->y_axis_val;
            }
            $html .= '<div class="checkmark" data-timestamp="'.$unit->getTimestamp().'" '.$dataXYtxt.'>'.$txt.'</div>';
        }

        return "<div class='checkmarks' data-id='".$habit->id."'>$html</div>";
    }

    private function printCalendarUnit(DateTime $datetime) {

    }

    public function printModule($calendar, $habits) {
        $html = "<div class='goodhabits-container'>$calendar
    <div class=\"clear-both\"></div>
 $habits</div> ";
//        $html .= '<div class="talentgrid"></div>';
        return $html;
    }

    private function printBackLink(moodle_url $url, $text) {
        return html_writer::link($url, '&#8592; ' . $text);
    }

    private function printForwardLink(moodle_url $url, $text) {
        return html_writer::link($url, $text . ' &#8594;');
    }

    public function printHiddenData () {
        global $CFG;

        return '<div class="goodhabits-hidden-data" data-wwwroot="'. $CFG->wwwroot .'" ></div> ';
    }

    public function timePeriodSelector($options, $selected) {
        $optionsTxt = '';
        foreach ($options as $k => $option) {
            $selectedTxt = ($selected == $k) ? ' selected="selected" ' : '';
            $optionsTxt .= "<option value='$k' $selectedTxt>$option</option>";
        }

        $sessionKey = $this->printHiddenSessionKey();

        $select = " <select name='time-period-selector' autocomplete='off'>$optionsTxt</select>";

        $submitTxt = get_string('submit_text_change_cal', 'local_good_habits');

        $submit = "<input type='submit' value='$submitTxt'> </input>";
        $html = "<form> $sessionKey {$select} $submit </form>";
        return $html;
    }

    public function printHiddenSessionKey() {
        $sessionKey = sesskey();
        return "<input type='hidden' name='sesskey' value='$sessionKey'> </input>";
    }

    public function printAddHabitEl() {
        $html = "<div class='clearboth'></div>";

        $plus = "<div class='streak add-new-habit'>+</div>";

        $sessionKey = $this->printHiddenSessionKey();

        $nameTxt =  get_string('add_new_habit_name', 'local_good_habits');
        $descTxt =  get_string('add_new_habit_desc', 'local_good_habits');

        $habitName = "<label for='new-habit-name'>$nameTxt</label><input class='new-habit-name' type='text' name='new-habit-name'> </input>";
        $habitDesc = "<label for='new-habit-desc'>$descTxt</label><input class='new-habit-desc' type='text' name='new-habit-desc'> </input>";

        $submitTxt = get_string('add_new_habit', 'local_good_habits');
        $submit = "<input type='submit' value='$submitTxt'> </input>";

        $form = "<form class='add-new-habit-form' method='post'> $sessionKey $habitName $habitDesc $submit</form>";
        $html .= "<div class='habit'>$plus $form</div>";

        return $html;
    }
}