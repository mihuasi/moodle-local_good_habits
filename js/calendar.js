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
 * JavaScript library for the good_habits plugin.
 *
 * @package    local
 * @subpackage good_habits
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

jQuery(window).on('load',function($) {

    var $ = jQuery;
    
    function initGrid(x, y) {

        var wwwroot = getHiddenData('wwwroot');

        $.when(
            $.getScript( wwwroot + "/local/good_habits/talentgrid/talentgrid-plugin.js" ),
            $.Deferred(function( deferred ){
                $( deferred.resolve );
            })
        ).done(function(){

            var options = {
                allowTokenDragging: false,
                imageSrc: './talentgrid/smiley.png'
            };

            var arrowIndicator = { // Options for the text accompanying the external token.
                enabled: false,
                text: ''
            };

            options.imageTitle = 'Place using the drop-downs';

            options.arrowIndicator = arrowIndicator;

            options.xLabel = 'Dedication';
            options.yLabel = 'Outcome';

            options.xSmallLabels = { // X small labels.
                left: 'Lax',
                center: 'Reasonable effort',
                right: 'Vigorous effort'
            };

            options.ySmallLabels = { // Y small labels.
                bottom: 'Disappointing',
                center: 'Reasonable',
                top: 'Outstanding'
            };

            options.selectControls = { // Options for the selector controls to the right of the grid.
                enabled: true,
                    xSelectLabel: 'Dedication',
                    ySelectLabel: 'Outcome',
                    xDefault: 'Select',
                    yDefault: 'Select',
            };

            if (x && y) {

                options.prePlaceDraggableIcon = true;
                options.prePlaceCoordinates = { // Co-ordinates used for pre-placed token.
                    x: x,
                    y: y
                };
                options.showExternalToken = false;

            }

            options.overlayTexts = { // Text to use when hovering over sections of the grid. False to turn off this feature.
                1:{
                    1: 'Instinctually mastering',
                        2: 'Earning your mastery',
                        3: 'Working hard for mastery',
                },
                2:{
                    1: 'Laid back competence',
                        2: 'Achievement through effort',
                        3: 'Working hard to achieve',
                },
                3:{
                    1: 'You get what you put in!',
                        2: 'Sticking at it',
                        3: 'Persevering with a challenge',
                },
            };

            var talentgrid = $('.talentgrid').talentgriddle(options);

        });

    }

    function resetCheckmarkVals(selectedCheckmark) {
        var text = '';
        if (selectedCheckmark.attr('data-x') && selectedCheckmark.attr('data-y')) {
            var x = selectedCheckmark.data('x');
            var y = selectedCheckmark.data('y');
            var text = displayVals(x, y);
        }
        selectedCheckmark.text(text);
    }

    var displayVals = function (x,y) {
        return x + ' / ' + y;
    };

    $('.checkmark').click(function () {

        var gridOpen = $('.goodhabits-container').hasClass('grid-is-open');

        var canInteract = getHiddenData('can-interact');

        if (!canInteract) {
            return null;
        }

        if (gridOpen) {
            return null;
        }

        var el = $(this);

        x = parseInt(el.attr("data-x"));
        y = parseInt(el.attr("data-y"));

        el.addClass('is-selected');
        $('.goodhabits-container').addClass('grid-is-open');

        var habitId = el.parent().data('id');

        var buttonsHtml = '<div class="grid-buttons">' +
            '<button data-type="cancel" type="cancel" name="cancelGrid" value="Cancel">Cancel</button>' +
            '<button class="save-button" data-type="submit" type="submit" name="saveGrid" value="Save" disabled>Save</button>' +
            '</div>';

        var timestamp = el.data('timestamp');
        var timeUnitTxt = 'Entry for ' + $('.time-unit-' + timestamp).data('text');
        var habitTxt = $('.habit-' + habitId + ' .habit-name').text();
        $('.habit-grid-container-' + habitId).append("" +
            "<div class='grid-heading'>"+timeUnitTxt+" ("+habitTxt+")</div>" +
            "<div class=\"talentgrid\">" +
            buttonsHtml +
            "</div> " +
            " <div class='clear-both'></div> ");

        initGrid(x,y);

    });


    $('.goodhabits-container').on('click', '.grid-buttons button', function() {
        var JSONvalues = $('.talentgrid-hidden-response').val();
        var values  = (JSONvalues) ? JSON.parse(JSONvalues) : '';
        var action = $(this).data('type');
        var selectedCheckmark = $('.checkmark.is-selected');
        var habitId = selectedCheckmark.parent().data('id');
        if (action == 'submit') {
            var wwwroot = getHiddenData('wwwroot');
            var timestamp = selectedCheckmark.data('timestamp');
            var periodDuration = $('.goodhabits-container .calendar').data('period-duration');
            var sesskey = getHiddenData('sesskey');
            var data = {x: values.x,y: values.y, habitId: habitId, timestamp: timestamp, periodDuration: periodDuration, sesskey: sesskey};
            $.post( wwwroot + "/local/good_habits/ajax_save_entry.php", data)
                .done(function( data ) {
                    // alert( "Data Loaded: " + data );
                    // alert(245);
                });
            selectedCheckmark.data('xVal', values.x);
            selectedCheckmark.data('yVal', values.y);
            selectedCheckmark.attr("data-x", values.x);
            selectedCheckmark.attr("data-y", values.y);

            var displayVals = values.x + ' / ' + values.y;
            selectedCheckmark.text(displayVals);
        }
        if (action == 'cancel') {
            resetCheckmarkVals(selectedCheckmark);
        }



        $('.habit-grid-container-' + habitId).empty();

        $('.goodhabits-container').removeClass('grid-is-open');

        $('.checkmark').removeClass('is-selected');
    });

    $('.goodhabits-container').on('change', '.talentgrid-hidden-response', function() {
        // alert($('.talentgrid-hidden-response').val());
        var values = JSON.parse($(this).val());
        var displayVals = values.x + ' / ' + values.y;
        // var displayVals = displayVals(values.x,values.y);
        var selectedCheckmark = $('.checkmark.is-selected');
        // selectedCheckmark.data('newX', values.x);
        selectedCheckmark.text(displayVals);
        $('.grid-buttons .save-button').removeAttr('disabled');
    });


    $('.streak.add-new-habit').click(function () {
        var isGlobal = $(this).hasClass('global');
        var formClass = '.add-new-habit-form.personal';
        if (isGlobal) {
            formClass = '.add-new-habit-form.global';
        }
        $(formClass).show();
        $(this).addClass('clicked');
        $(this).text('');
    });

    $('.streak.can-edit').mouseover(function () {
        $(this).addClass('hovering');
        $(this).text('-');
    });

    $('.streak.can-edit').mouseout(function () {
        $(this).removeClass('hovering');
        $(this).text('');
    });

    function getLangString(id) {
        return $('.goodhabits-hidden-lang').data('lang-' + id);
    }

    function getHiddenData(id) {
        return $('.goodhabits-hidden-data').data(id);
    }

    $('.streak.can-edit').click(function () {
        var isGlobal = $(this).data('is-global');
        var strId = 'confirm_delete_global';
        if (!isGlobal) {
            strId = 'confirm_delete_personal';
        }
        var confirmMsg = getLangString(strId);
        var proceed = confirm(confirmMsg);
        if (proceed) {
            var habitId = $(this).data('habit-id');
            var wwwroot = getHiddenData('wwwroot');
            var sesskey = getHiddenData('sesskey');
            var data = {habitId: habitId, sesskey: sesskey};
            $.post( wwwroot + "/local/good_habits/ajax_delete_habit.php", data)
                .done(function( data ) {
                    var habitEntry = $('.goodhabits-container .habit-' + habitId);
                    habitEntry.hide();
                });
        }
    });


});