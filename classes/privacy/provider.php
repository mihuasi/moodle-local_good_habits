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

namespace local_good_habits\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use local_good_habits\Helper;

global $CFG;

require_once($CFG->dirroot . '/local/good_habits/classes/Helper.php');

class provider implements
    // This plugin does store personal user data.
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'local_good_habits_entry',
            [
                'userid' => 'privacy:metadata:local_good_habits_entry:userid',
                'habit_id' => 'privacy:metadata:local_good_habits_entry:habit_id',
                'x_axis_val' => 'privacy:metadata:local_good_habits_entry:x_axis_val',
                'y_axis_val' => 'privacy:metadata:local_good_habits_entry:y_axis_val',
            ],
            'privacy:metadata:local_good_habits_entry'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        $contextlist->add_system_context();
        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        $contexts = $contextlist->get_contexts();
        foreach ($contexts as $context) {
            static::export($context);
        }
    }

    private static function export($context) {
        global $DB, $USER;
        $entries = $DB->get_records('local_good_habits_entry', array('userid' => $USER->id));

        $subcontext = array();

        $subcontext[] = get_string('local_good_habits_subcontext', 'local_good_habits');

        \core_privacy\local\request\writer::with_context($context)
            ->export_data($subcontext, (object) [
                'entries' => $entries,
            ]);
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        Helper::delete_all_entries();
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $userid = $contextlist->get_user()->id;
        Helper::delete_entries($userid);
    }
}