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
        $displayset = $calendar->get_display_set();

        $periodduration = $calendar->get_period_duration();

        $html = "<div class='calendar' data-period-duration='$periodduration'>";
        $html .= "    <div class='dates'>";

        $year = gh\Helper::display_year($displayset);

        $html .= "        <div class='year'>$year</div>";

        $days = array();

        $backurl = $calendar->get_back_url();
        $forwardurl = $calendar->get_forward_url();

        foreach($displayset as $k => $unit) {
            $isfirst = $k == 0;
            $isLast = $k == (count($displayset) -1);
            $month = $unit->display_month($isfirst);
            $display = $unit->display_unit();
            $topline = $display['topLine'];

            $singlelinedisplay = $topline . ' ' . $display['bottomLine'];
            if ($isfirst) {
                $topline = $this->print_back_link($backurl, $topline);
            }
            if ($isLast && $forwardurl) {
                $topline = $this->print_forward_link($forwardurl, $topline);
            }
            $unitcontents = '<div class="top-line">'.$topline.'</div>';
            $unitcontents .= '<div class="bottom-line">'.$display['bottomLine'].'</div>';

            $monthhtml = ($month) ? '<div class="month">'.$month.'</div>' : '';
            $day = '<div data-text="'.$singlelinedisplay.'" class="time-unit '.implode(' ', $unit->get_classes()).'">' .$monthhtml .$unitcontents.'</div>';
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

        $canmanage = has_capability('local/good_habits:manage_habits', $PAGE->context);

        $canmanageclass = ($canmanage) ? ' can-edit ' : '';

        $html .= '<div class="streak ' . $canmanageclass . '" data-habit-id="'.$habit->id.'"></div>';

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

        $displayset = $calendar->get_display_set();

        $entries = $habit->get_entries($USER->id, $calendar->get_period_duration());

        foreach ($displayset as $k => $unit) {
            $dataxytxt = '';
            $txt = '<div class="empty-day">  </div>';
            if (isset($entries[$unit->getTimestamp()])) {
                $entry = $entries[$unit->getTimestamp()];
                $dataxytxt = ' data-x="'.$entry->x_axis_val.'" data-y="'.$entry->y_axis_val.'" ';
                $txt = $entry->x_axis_val . ' / ' . $entry->y_axis_val;
            }
            $caninteract = has_capability('local/good_habits:manage_entries', $PAGE->context);
            $caninteractclass = ($caninteract) ? '' : ' no-interact ';
            $html .= '<div class="checkmark ' . $caninteractclass . '" data-timestamp="'.$unit->getTimestamp().'" '.$dataxytxt.'>'.$txt.'</div>';
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

        $datatext = '';
        foreach ($data as $key => $val) {
            $datatext .= ' data-'.$key.'="'.$val.'" ';
        }

        $hiddendata = '<div class="goodhabits-hidden-data" '.$datatext.'></div> ';

        $langstringids = array(
            'confirm_delete'
        );
        $datalang = gh\Helper::lang_string_as_data($langstringids);

        $hiddenlangstrings = '<div class="goodhabits-hidden-lang" '.$datalang.'></div> ';

        return $hiddendata . $hiddenlangstrings;
    }

    public function time_period_selector($options, $selected) {
        $optionstxt = '';
        foreach ($options as $k => $option) {
            $selectedtxt = ($selected == $k) ? ' selected="selected" ' : '';
            $optionstxt .= "<option value='$k' $selectedtxt>$option</option>";
        }

        $sessionkey = $this->print_hidden_session_key();

        $select = " <select name='time-period-selector' autocomplete='off'>$optionstxt</select>";

        $submittxt = get_string('submit_text_change_cal', 'local_good_habits');

        $submit = "<input type='submit' value='$submittxt'> </input>";
        $html = "<form> $sessionkey {$select} $submit </form>";
        return $html;
    }

    public function print_hidden_session_key() {
        $sessionkey = sesskey();
        return "<input type='hidden' name='sesskey' value='$sessionkey'> </input>";
    }

    public function print_add_habit_el() {
        $html = "<div class='clearboth'></div>";

        $plus = "<div class='streak add-new-habit'>+</div>";

        $sessionkey = $this->print_hidden_session_key();

        $nametxt =  get_string('add_new_habit_name', 'local_good_habits');
        $desctxt =  get_string('add_new_habit_desc', 'local_good_habits');

        $habitname = "<label for='new-habit-name'>$nametxt</label><input class='new-habit-name' type='text' name='new-habit-name'> </input>";
        $habitdesc = "<label for='new-habit-desc'>$desctxt</label><input class='new-habit-desc' type='text' name='new-habit-desc'> </input>";

        $submittxt = get_string('add_new_habit', 'local_good_habits');
        $submit = "<input type='submit' value='$submittxt'> </input>";

        $form = "<form class='add-new-habit-form' method='post'> $sessionkey $habitname $habitdesc $submit</form>";
        $html .= "<div class='habit'>$plus $form</div>";

        return $html;
    }

    public function print_delete_my_entries() {
        $submittxt = get_string('delete_all_entries', 'local_good_habits');
        $sessionkey = $this->print_hidden_session_key();
        $submit = "<br /><br /><br /><input type='submit' value='$submittxt'> </input>";
        $action = "<input type='hidden' name='action' value='delete-all-entries'> </input>";
        $form = "<form class='delete-all-entries-form' method='post'>$sessionkey $action $submit</form>";
        echo $form;
    }
}