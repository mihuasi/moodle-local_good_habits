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

defined('MOODLE_INTERNAL') || die();

class local_good_habits_renderer extends plugin_renderer_base {

    public function print_calendar(gh\FlexiCalendar $calendar) {
        $displaySet = $calendar->get_display_set();

        $periodDuration = $calendar->get_period_duration();

        $html = "<div class='calendar' data-period-duration='$periodDuration'>";
        $html .= "    <div class='dates'>";

        $year = gh\Helper::display_year($displaySet);

        $html .= "        <div class='year'>$year</div>";

        $days = array();

        $backUrl = $calendar->get_back_url();
        $forwardUrl = $calendar->get_forward_url();

        foreach($displaySet as $k => $unit) {
            $isFirst = $k == 0;
            $isLast = $k == (count($displaySet) -1);
            $month = $unit->display_month($isFirst);
            $display = $unit->display_unit();
            $topLine = $display['topLine'];

            $singleLineDisplay = $topLine . ' ' . $display['bottomLine'];
            if ($isFirst) {
                $topLine = $this->print_back_link($backUrl, $topLine);
            }
            if ($isLast && $forwardUrl) {
                $topLine = $this->print_forward_link($forwardUrl, $topLine);
            }
            $unitContents = '<div class="top-line">'.$topLine.'</div>';
            $unitContents .= '<div class="bottom-line">'.$display['bottomLine'].'</div>';

            $monthHtml = ($month) ? '<div class="month">'.$month.'</div>' : '';
            $day = '<div data-text="'.$singleLineDisplay.'" class="time-unit '.implode(' ', $unit->get_classes()).'">' .$monthHtml .$unitContents.'</div>';
            $days[] = $day;
        }

        $html .= implode('', $days);

        $html .= "    </div>";
        $html .= "</div>";
        return $html;
    }

    public function print_habits(gh\FlexiCalendar $calendar, $habits) {
        global $PAGE;
        $arr = array();
        foreach ($habits as $habit) {
            $arr[] = $this->print_habit($calendar, $habit);
        }

        if (has_capability('local/good_habits:manage_habits', $PAGE->context)) {
            $arr[] = $this->print_add_habit_el();
        }

        return '<div class="habits">' . implode('', $arr) . '</div>';
    }

    public function print_habit(gh\FlexiCalendar $calendar, gh\Habit $habit) {
        global $PAGE;
        $html = "<div class='habit habit-".$habit->id."'>";

        $canManage = has_capability('local/good_habits:manage_habits', $PAGE->context);

        $canManageClass = ($canManage) ? ' can-edit ' : '';

        $html .= '<div class="streak ' . $canManageClass . '" data-habit-id="'.$habit->id.'"></div>';

        $html .= '    <div class="title"><div class="habit-name">'.format_text($habit->name).'</div><div class="description">'.format_text($habit->description).'</div></div>';

        $html .= '    <div class="time-line">';

        $html .= $this->print_checkmarks($calendar, $habit);

        $html .= '        <div class="clear-both"></div>';

        $html .= '    </div>';


        $html .= '    <div class="clear-both"></div>';

        $html .= "</div>";

        $html .= '<div class="habit-grid-container habit-grid-container-'.$habit->id.'"></div>';

        return $html;
    }

    private function print_checkmarks(gh\FlexiCalendar $calendar, gh\Habit $habit) {
        global $USER, $PAGE;

        $html = '';

        $displaySet = $calendar->get_display_set();

        $entries = $habit->get_entries($USER->id, $calendar->get_period_duration());

        foreach ($displaySet as $k => $unit) {
            $dataXYtxt = '';
            $txt = '<div class="empty-day">  </div>';
            if (isset($entries[$unit->getTimestamp()])) {
                $entry = $entries[$unit->getTimestamp()];
                $dataXYtxt = ' data-x="'.$entry->x_axis_val.'" data-y="'.$entry->y_axis_val.'" ';
                $txt = $entry->x_axis_val . ' / ' . $entry->y_axis_val;
            }
            $canInteract = has_capability('local/good_habits:manage_entries', $PAGE->context);
            $canInteractClass = ($canInteract) ? '' : ' no-interact ';
            $html .= '<div class="checkmark ' . $canInteractClass . '" data-timestamp="'.$unit->getTimestamp().'" '.$dataXYtxt.'>'.$txt.'</div>';
        }

        return "<div class='checkmarks' data-id='".$habit->id."'>$html</div>";
    }

    private function print_calender_unit(DateTime $datetime) {

    }

    public function print_module($calendar, $habits) {
        $html = "<div class='goodhabits-container'>$calendar
    <div class=\"clear-both\"></div>
 $habits</div> ";
//        $html .= '<div class="talentgrid"></div>';
        return $html;
    }

    private function print_back_link(moodle_url $url, $text) {
        return html_writer::link($url, '&#8592; ' . $text);
    }

    private function print_forward_link(moodle_url $url, $text) {
        return html_writer::link($url, $text . ' &#8594;');
    }

    public function print_hidden_data() {
        global $CFG, $PAGE;

        $data = array(
            'wwwroot' => $CFG->wwwroot,
            'sesskey' => sesskey(),
            'can-interact' => (int) has_capability('local/good_habits:manage_entries', $PAGE->context),
            'can-manage' => (int) has_capability('local/good_habits:manage_habits', $PAGE->context),
        );

        $dataText = '';
        foreach ($data as $key => $val) {
            $dataText .= ' data-'.$key.'="'.$val.'" ';
        }

        $hiddenData = '<div class="goodhabits-hidden-data" '.$dataText.'></div> ';

        $langStringIds = array(
            'confirm_delete'
        );
        $dataLang = gh\Helper::lang_string_as_data($langStringIds);

        $hiddenLangStrings = '<div class="goodhabits-hidden-lang" '.$dataLang.'></div> ';

        return $hiddenData . $hiddenLangStrings;
    }

    public function time_period_selector($options, $selected) {
        $optionsTxt = '';
        foreach ($options as $k => $option) {
            $selectedTxt = ($selected == $k) ? ' selected="selected" ' : '';
            $optionsTxt .= "<option value='$k' $selectedTxt>$option</option>";
        }

        $sessionKey = $this->print_hidden_session_key();

        $select = " <select name='time-period-selector' autocomplete='off'>$optionsTxt</select>";

        $submitTxt = get_string('submit_text_change_cal', 'local_good_habits');

        $submit = "<input type='submit' value='$submitTxt'> </input>";
        $html = "<form> $sessionKey {$select} $submit </form>";
        return $html;
    }

    public function print_hidden_session_key() {
        $sessionKey = sesskey();
        return "<input type='hidden' name='sesskey' value='$sessionKey'> </input>";
    }

    public function print_add_habit_el() {
        $html = "<div class='clearboth'></div>";

        $plus = "<div class='streak add-new-habit'>+</div>";

        $sessionKey = $this->print_hidden_session_key();

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

    public function print_delete_my_entries() {
        $submitTxt = get_string('delete_all_entries', 'local_good_habits');
        $sessionKey = $this->print_hidden_session_key();
        $submit = "<br /><br /><br /><input type='submit' value='$submitTxt'> </input>";
        $action = "<input type='hidden' name='action' value='delete-all-entries'> </input>";
        $form = "<form class='delete-all-entries-form' method='post'>$sessionKey $action $submit</form>";
        echo $form;
    }
}