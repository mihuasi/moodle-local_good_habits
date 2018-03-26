jQuery(window).on('load',function($) {

    var $ = jQuery;
    
    function initGrid(x, y) {

        var wwwroot = $('.goodhabits-hidden-data').data('wwwroot');

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

            if (x !== undefined && y !== undefined ) {

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
            // console.log($.ui);
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
    // function displayVals(x, y) {
    //     return x + ' / ' + y;
    // }

    // function onGridChange() {
    //     $('.talentgrid-hidden-response').on('change', function () {
    //         var values = JSON.parse($(this).val());
    //         var displayVals = displayVals(values.x, values.y);
    //         var selectedCheckmark = $('.checkmark.is-selected');
    //         selectedCheckmark.text(displayVals);
    //     });
    //
    //     $('.axis-selector').change(function () {
    //         alert(23);
    //     });
    // }

    $('.checkmark').click(function () {
        // var el = jQuery(this);

        var gridOpen = $('.goodhabits-container').hasClass('grid-is-open');

        if (gridOpen) {
            return null;
        }

        var el = $(this);

        var x = el.data('x');
        var y = el.data('y');

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
            var wwwroot = $('.goodhabits-hidden-data').data('wwwroot');
            var timestamp = selectedCheckmark.data('timestamp');
            var periodDuration = $('.goodhabits-container .calendar').data('period-duration');
            var data = {x: values.x,y: values.y, habitId: habitId, timestamp: timestamp, periodDuration: periodDuration};
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
        $('.add-new-habit-form').show();
        $(this).addClass('clicked');
        $(this).text('');
    });


});