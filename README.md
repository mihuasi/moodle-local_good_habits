# Good Habits Plugin (1.2) for Moodle

The intention of this plugin is to help track habits over time. Within an e-learning context this may help cultivate behaviours that improve learning outcomes.

## Overview of features

- Habit tracking by day, custom range of days or by week.
- Every calendar entry allows for rating by both dedication and outcome.
- Multilingual support
- Granular access control: 3 capabilities for access to plugin, management of habits and management of habit entries
- Uses the Privacy API to allow for control over user data.

## Changes from 1.1

- CSS specificity improved
- Tables renamed to fit Moodle standards
- php changes to fit Moodle standards
- Fix JS bug when re-loading co-ordinates
- Add .travis.yml for automated testing

## Changes from 1.0

- Add link to calendar via Site Admin / Plugins (under Local Plugins)
- CSS selector issue resolved
- Issue with display of talentgrid/smiley.png resolved
- The good_habits table was removed
- Some code refactoring and AJAX_SCRIPT declared.
- CSS improvements
- Privacy API implemented (declares user data impact) and tested using https://docs.moodle.org/dev/Privacy_API/Utilities
- Security issues addressed: sesskey added wherever needed ; new capabilities ; user-provided text is output via format_text().

## Ideas for future improvements

- Allow management of different habits for different users.
- Implement as an activity module to hook into Moodle features such as activity tracking.
- Decouple calendar system from entry type to support alternative types.
- Support for tracking habits on a single dimension.