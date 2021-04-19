# Good Habits Plugin (1.3) for Moodle

The intention of this plugin is to help track habits over time. Within an e-learning context this may help cultivate behaviours that improve learning outcomes.

## Overview of features

- Habit tracking by day, custom range of days or by week.
- Every calendar entry allows for rating by both dedication and outcome.
- Multilingual support
- Granular access control: 3 capabilities for access to plugin, management of habits and management of habit entries
- Uses the Privacy API to allow for control over user data.

## Changes from 1.2

- Add Personal Breaks feature.
- Add keyboard shortcut to save values quickly (number key when editing a habit entry).
- Check that personal habits belong to the user prior to deletion.

## Changes from 1.1

- Add ability to create global/personal habits with associated capabilities
- Change colours to track diagonal relationship between effort and outcome
- Entry colour changes to reflect outcome/effort selection
- Fix JS bug when re-loading co-ordinates
- Fix page reload issue with adding new habits / deleting entries
- Fix change of month display
- CSS specificity improved
- Tables renamed to fit Moodle standards
- php changes to fit Moodle standards
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

- Habit editing options
- Improve date selection UI
- Implement as an activity module to hook into Moodle features such as activity tracking.
- Decouple calendar system from entry type to support alternative types.
- Support for tracking habits on a single dimension.
- Add option to bubble up results, eg, from days to weeks
- Save/load habits and entries
- Make pre-loaded sets of habits available
- Add a system for tracking linear outcomes periodically
- A facility for marking critical points of change
- Analytics to chart the changes of all of these over time