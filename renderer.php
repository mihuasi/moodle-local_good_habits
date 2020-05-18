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

        foreach ($displayset as $k => $unit) {
            $isfirst = $k == 0;
            $islast = $k == (count($displayset) - 1);
            $month = $unit->display_month();

            $display = $unit->display_unit();
            $topline = $display['topLine'];

            $singlelinedisplay = $topline . ' ' . $display['bottomLine'];
            if ($isfirst) {
                $topline = $this->print_back_link($backurl, $topline);
            }
            if ($islast && $forwardurl) {
                $topline = $this->print_forward_link($forwardurl, $topline);
            }
            $unitcontents = '<div class="top-line">'.$topline.'</div>';
            $unitcontents .= '<div class="bottom-line">'.$display['bottomLine'].'</div>';

            $monthhtml = ($month) ? '<div class="month">'.$month.'</div>' : '';
            $implode = implode(' ', $unit->get_classes());
            $day = '<div data-text="'.$singlelinedisplay.'" class="time-unit '. $implode .'">';
            $day .= $monthhtml . $unitcontents.'</div>';
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

        if (has_capability('local/good_habits:manage_global_habits', $PAGE->context)) {
            $newhabittext = get_string('add_new_habit_global', 'local_good_habits');
            $arr[] = $this->print_add_habit_el('global', $newhabittext, 1);
        }

        if (has_capability('local/good_habits:manage_personal_habits', $PAGE->context)) {
            $newhabittext = get_string('add_new_habit_personal', 'local_good_habits');
            $arr[] = $this->print_add_habit_el('personal', $newhabittext, 0);
        }

        return '<div class="habits">' . implode('', $arr) . '</div>';
    }

    public function print_habit(gh\FlexiCalendar $calendar, gh\Habit $habit) {
        global $PAGE;
        $html = "<div class='habit habit-".$habit->id."'>";

        $editglobal = has_capability('local/good_habits:manage_global_habits', $PAGE->context);
        $editpersonal = has_capability('local/good_habits:manage_personal_habits', $PAGE->context);
        $isglobal = $habit->is_global();

        $canmanage = false;
        if ($isglobal AND $editglobal) {
            $canmanage = true;
        }
        if (!$isglobal AND $editpersonal) {
            $canmanage = true;
        }

        $canmanageclass = ($canmanage) ? ' can-edit ' : '';

        $data = ' data-habit-id="'.$habit->id.'" data-is-global="'.$isglobal.'" ';
        $globalclass = ($isglobal) ? 'global' : 'personal';

        $html .= '<div class="streak ' . $canmanageclass . ' ' . $globalclass . '" ' . $data . '></div>';

        $html .= '<div class="title"><div class="habit-name">'.format_text($habit->name).'</div>';
        $html .= '    <div class="description">'.format_text($habit->description).'</div></div>';

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

        foreach ($displayset as $unit) {
            $dataxytxt = '';
            $txt = '<div class="empty-day">  </div>';
            $timestamp = $unit->getTimestamp();
            $classxy = 'noxy';
            if (isset($entries[$timestamp])) {
                $entry = $entries[$timestamp];
                $xval = $entry->x_axis_val;
                $yval = $entry->y_axis_val;
                $dataxytxt = ' data-x="'. $xval .'" data-y="'. $yval .'" ';
                $txt = $xval . ' / ' . $yval;
                $classxy = 'x-val-' . $xval . ' y-val-' . $yval;
            }
            $caninteract = has_capability('local/good_habits:manage_entries', $PAGE->context);
            $caninteractclass = ($caninteract) ? '' : ' no-interact ';

            $classes = 'checkmark ' . $caninteractclass . ' ' . $classxy;
            $html .= '<div class="' . $classes . '" data-timestamp="'. $timestamp .'" '.$dataxytxt.'>';
            $html .= $txt . '</div>';
        }
        $html .= '<div class="bubble-up"><span class="bubble-up-sign">&uarr;</span></div>';

        return "<div class='checkmarks' data-id='".$habit->id."'>$html</div>";
    }

    public function print_module($calendar, $habits) {
        $html = "<div class='goodhabits-container'>$calendar
                       <div class=\"clear-both\"></div>
                 $habits
                 </div> ";
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
            'can-manage' => (int) has_capability('local/good_habits:manage_global_habits', $PAGE->context),
        );

        $datatext = '';
        foreach ($data as $key => $val) {
            $datatext .= ' data-'.$key.'="'.$val.'" ';
        }

        $hiddendata = '<div class="goodhabits-hidden-data" '.$datatext.'></div> ';

        $langstringids = array(
            'confirm_delete_global', 'confirm_delete_personal', 'entry_for', 'cancel', 'save'
        );

        $gridstringids = array(
            'imagetitle', 'xlabel', 'ylabel', 'x_small_label_left', 'x_small_label_center', 'x_small_label_right',
            'y_small_label_bottom', 'y_small_label_center', 'y_small_label_top', 'x_select_label', 'y_select_label',
            'x_default', 'y_default', 'overlay_1_1', 'overlay_1_2', 'overlay_1_3', 'overlay_2_1', 'overlay_2_2',
            'overlay_2_3', 'overlay_3_1', 'overlay_3_2', 'overlay_3_3'
        );

        $langstringids = array_merge($langstringids, $gridstringids);

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

    public function print_add_habit_el($globalclass, $newhabittext, $globalhabit) {
        $html = "<div class='clearboth'></div>";

        $plus = "<div class='streak add-new-habit $globalclass'>+ <span class='new-habit-text'>".$newhabittext."</span></div>";

        $sessionkey = $this->print_hidden_session_key();

        $globalinput = "<input type='hidden' name='isglobal' value='$globalhabit' />";

        $nametxt = get_string('add_new_habit_name', 'local_good_habits');
        $desctxt = get_string('add_new_habit_desc', 'local_good_habits');

        $habitname = "<label for='new-habit-name'>$nametxt</label>";
        $habitname .= "<input class='new-habit-name' type='text' maxlength='17' name='new-habit-name'> </input>";
        $habitdesc = "<label for='new-habit-desc'>$desctxt</label>";
        $habitdesc .= "<input class='new-habit-desc' type='text' maxlength='75' name='new-habit-desc'> </input>";

        $submittxt = $newhabittext;
        $submit = "<input type='submit' value='$submittxt'> </input>";

        $formcontent = "$sessionkey $habitname $habitdesc $globalinput $submit";

        $form = "<form class='add-new-habit-form $globalclass' method='post'>$formcontent</form>";
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