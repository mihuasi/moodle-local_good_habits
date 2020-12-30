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

defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'local/good_habits:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),

    'local/good_habits:manage_entries' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_SYSTEM,
        'captype' => 'write',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),

    'local/good_habits:manage_global_habits' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_SYSTEM,
        'captype' => 'write',
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        )
    ),

    'local/good_habits:manage_personal_habits' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_SYSTEM,
        'captype' => 'write',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'local/good_habits:manage_personal_breaks' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_SYSTEM,
        'captype' => 'write',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),
);
