<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'ESHB' ) ) {

    /**
     * Add the "Booking Rules" tab to the Easy Hotel settings screen, right after
     * "Booking".
     *
     * Registered through the sections filter rather than ESHB::createSection() so the
     * tab lands in a fixed position regardless of when this file is loaded.
     *
     * Both limits live in their rules repeater only — there are deliberately no global
     * "Minimum Nights" / "Maximum Nights" number fields, so there is a single place to
     * maintain each. Previously configured global values are carried into "All
     * Accomodations" rules by ESHB_Booking_Rules::migrate_global_nights().
     *
     * Accomodations have no "Nights" metabox tab either; that section belongs to the
     * standalone EHB Min Max add-on, which registers it itself when active (and this
     * whole module stands aside in that case — see booking-rules.php).
     */
    add_filter( 'eshb_eshb_settings_sections', function( $sections ){

        if ( ! is_array( $sections ) ) {
            return $sections;
        }

        $owns_nights       = ESHB_Booking_Rules::owns_nights();
        $owns_check_in_day = ESHB_Booking_Rules::owns_check_in_day();

        // Runs here, not on admin_init: the options object reads the stored values
        // straight after this filter, so a later migration would not show up until
        // the next request. Only for the features this site actually owns — an active
        // add-on is still the source of truth for its own.
        if ( $owns_nights ) {
            ESHB_Booking_Rules::migrate_global_nights();
        }

        if ( $owns_check_in_day ) {
            ESHB_Booking_Rules::migrate_check_in_day_meta();
        }

        $accomodations_field = array(
            'id'            => 'accomodations',
            'type'          => 'checkbox',
            'title'         => 'Accomodations',
            'class'         => 'eshb-nights-rule-accomodations',
            'options'       => 'eshb_booking_rules_accomodation_options',
            'default'       => array( ESHB_Booking_Rules::TARGET_ALL ),
            'empty_message' => 'No accomodation found. Publish an accomodation first.',
        );

        $fields = array();

        if ( $owns_nights ) {

            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Minimum Nights',
            );
            $fields[] = array(
                'id'           => ESHB_Booking_Rules::MIN_RULES_FIELD,
                'type'         => 'repeater',
                'title'        => 'Minimum Nights Rules',
                'help'         => 'Counted in nights, not calendar days — 3 nights means check-out 3 days after check-in. Tick "All" for a site wide minimum, or pick accomodations to set one for them only. A rule that picks accomodations beats an "All" rule; if several still apply, the highest wins. A season can require a longer stay for its own dates — whichever is stricter applies.',
                'button_title' => 'Add Rule',
                'fields'       => array(
                    array(
                        'id'      => 'min_nights',
                        'type'    => 'number',
                        'title'   => 'Minimum Nights',
                        'default' => 1,
                        'min'     => 1,
                    ),
                    $accomodations_field,
                ),
            );

            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Maximum Nights',
            );
            $fields[] = array(
                'id'           => ESHB_Booking_Rules::MAX_RULES_FIELD,
                'type'         => 'repeater',
                'title'        => 'Maximum Nights Rules',
                'help'         => 'The longest stay a guest can book. Tick "All" for a site wide cap, or pick accomodations to cap only those. A rule that picks accomodations beats an "All" rule; if several still apply, the lowest wins. Leave the table empty for no limit.',
                'button_title' => 'Add Rule',
                'fields'       => array(
                    array(
                        'id'      => 'max_nights',
                        'type'    => 'number',
                        'title'   => 'Maximum Nights',
                        'default' => 1,
                        'min'     => 1,
                    ),
                    $accomodations_field,
                ),
            );
        }

        if ( $owns_check_in_day ) {

            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Check-in Day',
            );
            $fields[] = array(
                'id'           => ESHB_Booking_Rules::CHECK_IN_DAY_RULES_FIELD,
                'type'         => 'repeater',
                'title'        => 'Check-in Day Rules',
                'help'         => 'Restrict which weekdays a stay may start on. Tick as many days as you like — a guest can then check in on any of them. "All Days" ticks every day at once, which means no restriction; use "Uncheck all" to clear them and pick your own. Tick "All" under Accomodations to apply the rule site wide, or pick accomodations to restrict only those; a rule that picks accomodations beats an "All" rule. Unlike nights there is no stricter of two, so if several rules still apply the first one in the table wins, with all of its days.',
                'button_title' => 'Add Rule',
                'fields'       => array(
                    array(
                        'id'      => 'check_in_day',
                        'type'    => 'checkbox',
                        'title'   => 'Allowed Check-in Days',
                        'class'   => 'eshb-check-in-day-choices',
                        'inline'  => true,
                        'options' => ESHB_Booking_Rules::get_check_in_day_options(),
                        'default' => array( ESHB_Booking_Rules::TARGET_ALL ),
                    ),
                    $accomodations_field,
                ),
            );
            $fields[] = array(
                'id'      => 'string_check_in_day_error_msg',
                'type'    => 'text',
                'title'   => 'Check-in Day Error Message',
                'desc'    => 'Shown when a guest picks a check-in date on a day that is not allowed. The allowed day is appended to it.',
                'default' => ESHB_Booking_Rules::get_week_field_default( 'string_check_in_day_error_msg', 'Only allowed check in day is :' ),
            );
        }

        // Core's own, always shown — moved here from the "Booking" tab, unchanged.
        //
        // Deliberately left as the plain repeater it has always been, rather than given
        // the rules tables' summary/Edit treatment: every id and field type is exactly
        // what the Booking tab used, so this moves the UI between tabs and nothing else.
        // The values live under the same `holidays` key of the same `eshb_settings`
        // option, so existing holidays keep working with no migration. Only the
        // labels say "Booking Blocked Dates" now — the ids stay `holidays` /
        // `holiday-date` / `holiday-title` so saved data is untouched.
        $fields[] = array(
            'type'    => 'subheading',
            'content' => 'Booking Blocked Dates',
        );
        $fields[] = array(
            'id'        => 'holidays',
            'type'      => 'repeater',
            'title'     => 'Booking Blocked Dates',
            'desc'      => 'Dates added here cannot be booked by guests. Use it for holidays, maintenance or any day you want to keep closed.',
            'fields'    => array(

                array(
                    'id'    => 'holiday-date',
                    'type'  => 'date',
                    'title' => 'Blocked Date',
                    'desc'  => 'Pick a single date, or a from–to range, to block from booking.',
                    'from_to'  => true,
                    'settings' => array(
                        'dateFormat'      => 'yy-mm-dd',
                        'changeMonth'     => true,
                        'changeYear'      => true,
                        'showButtonPanel' => true,
                        'weekHeader'      => 'Week',
                        'monthNamesShort' => array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
                        'dayNamesMin'     => array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
                    ),
                ),
                array(
                    'id'    => 'holiday-title',
                    'type'  => 'text',
                    'title' => 'Reason / Title',
                    'desc'  => 'Why these dates are blocked, e.g. "Eid Holiday" or "Maintenance".',
                ),
                array(
                    'id'          => 'accomodation-ids',
                    'type'        => 'select',
                    'title'       => 'Accomodations',
                    'placeholder' => 'Select Accomodations',
                    'desc'  => 'Block these dates only for the selected accomodations. Leave it empty to block for all.',
                    'options'     => 'posts',
                    'multiple'     => true,
                    'chosen'      => false,
                    'query_args'  => array(
                                        'post_type' => 'eshb_accomodation',
                                        'posts_per_page' => -1,
                                    ),
                ),

            ),
        );

        // Belongs to the min max feature, so it follows the same owner.
        if ( $owns_nights ) {
            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Global Settings',
            );
            $fields[] = array(
                'id'      => 'calendar_start_date_buffer',
                'type'    => 'number',
                'title'   => 'Calenader Start Date Buffer (+Night)',
                'default' => ESHB_Booking_Rules::get_field_default( 'calendar_start_date_buffer', 0 ),
            );
        }

        $booking_rules_section = array(
            'title'  => 'Booking Rules',
            'class'  => 'easy-hotel-booking-rules-settings',
            'fields' => $fields,
        );

        // Find the "Booking" tab and drop the new one straight after it.
        $position = false;
        foreach ( $sections as $index => $section ) {
            if ( isset( $section['title'] ) && $section['title'] === 'Booking' ) {
                $position = $index + 1;
                break;
            }
        }

        if ( $position === false ) {
            $sections[] = $booking_rules_section;
        } else {
            array_splice( $sections, $position, 0, array( $booking_rules_section ) );
        }

        return $sections;

    } );

    /**
     * jQuery UI datepicker for the "Booking Blocked Dates" fields.
     *
     * The framework enqueues a field type's own assets only for the types it has seen
     * through ESHB::createSection(), which is the one place ESHB::set_used_fields() is
     * called from. This tab is registered through the sections filter above instead, so
     * ESHB_Field_date::enqueue() never runs and jquery-ui-datepicker is never printed.
     *
     * Without it eshb_field_date() throws on the first date field it touches, and the
     * repeater initialises its saved rows before binding Add / Duplicate / Delete — so
     * one saved blocked date was enough to leave all three buttons dead. main.js now
     * guards against the missing plugin and binds before initialising, but the field is
     * still meant to have a calendar, which is what this puts back.
     *
     * Only the script is needed; the framework stylesheet already carries the
     * .ui-datepicker rules.
     */
    add_action( 'admin_enqueue_scripts', function(){

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only page check.
        if ( ! isset( $_GET['page'] ) || sanitize_key( wp_unslash( $_GET['page'] ) ) !== 'easy-hotel-settings' ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-datepicker' );

    } );

    /**
     * Turn each rules repeater into a plain admin list: an "Add Rule" button, a table
     * of the rules already configured, and Edit / Delete per row.
     *
     * The repeater itself stays the source of truth — the rows are still its inputs,
     * just collapsed until one is being edited, so saving, cloning and reindexing keep
     * working the way the settings framework expects. Nothing here is required for the
     * rules to work; a browser with the script blocked falls back to the plain
     * repeater.
     *
     * "All" acts as a check-all for the accomodation list: ticking it ticks every
     * accomodation so it is obvious the rule covers them, unticking it clears them, and
     * unticking any single one drops "All". The extra ids that ride along with "all"
     * change nothing — ESHB_Booking_Rules::resolve_nights_rule() reads a rule
     * containing "all" as the baseline rule regardless of what else is ticked.
     */
    // On admin_print_footer_scripts rather than admin_footer: _wp_footer_scripts runs
    // on that hook at priority 10, so by 20 jQuery and the framework's own script have
    // been printed and this can rely on both.
    add_action( 'admin_print_footer_scripts', function(){

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only page check.
        if ( ! isset( $_GET['page'] ) || sanitize_key( wp_unslash( $_GET['page'] ) ) !== 'easy-hotel-settings' ) {
            return;
        }

        ?>
        <style>
        /* Rows stay in the form but out of sight until opened for editing. The open one
           goes back to "table", the framework's own value — the title/fieldset columns
           inside are percentage widths that need that cell context to line up. */
        .eshb-rule-repeater .csf-repeater-wrapper > .csf-repeater-item { display: none; }
        .eshb-rule-repeater .csf-repeater-wrapper > .csf-repeater-item.eshb-editing { display: table; }
        .eshb-rule-repeater .csf-repeater-helper { display: none; }
        .eshb-rule-repeater .csf-repeater-wrapper { margin: 0; }
        .eshb-rule-table { margin-bottom: 12px; }
        .eshb-rule-table table { border-collapse: collapse; width: 100%; max-width: 760px; background: #fff; border: 1px solid #dcdcde; }
        .eshb-rule-table th,
        .eshb-rule-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #dcdcde; vertical-align: middle; }
        .eshb-rule-table th { font-weight: 600; background: #f6f7f7; }
        .eshb-rule-table tbody tr:last-child td { border-bottom: 0; }
        .eshb-rule-table tr.eshb-rule-open td { background: #f0f6fc; }
        .eshb-rule-table .eshb-rule-actions { width: 80px; white-space: nowrap; text-align: right; }
        .eshb-rule-table .eshb-rule-icon { cursor: pointer; padding: 6px 7px; margin-left: 2px; border: 0; border-radius: 3px; background: transparent; color: #8c8f94; font-size: 14px; line-height: 1; }
        .eshb-rule-table .eshb-rule-icon:hover { background: #f0f0f1; }
        .eshb-rule-table .eshb-rule-edit:hover { color: #2271b1; }
        .eshb-rule-table .eshb-rule-delete:hover { color: #b32d2e; }
        .eshb-rule-table .eshb-rule-icon:focus { outline: 2px solid #2271b1; outline-offset: -1px; }
        .eshb-rule-table tr.eshb-rule-open .eshb-rule-edit { color: #2271b1; }
        .eshb-rule-table .eshb-rule-unset { color: #b32d2e; font-style: italic; }
        .eshb-rule-table .eshb-rule-empty { margin: 0 0 4px; }
        .eshb-rule-conflicts { max-width: 760px; }
        .eshb-rule-conflicts .notice { margin: 0 0 12px; }
        .eshb-rule-conflicts ul { margin: 0 0 10px 22px; list-style: disc; }
        .eshb-choices-clear { display: inline-block; margin-top: 8px; font-size: 12px; text-decoration: none; }
        /* Help marker, moved inline after the field title by moveHelp(). */
        .eshb-rule-help { cursor: help; display: inline-block; padding: 0 0 0 5px; font-size: 13px; color: #8c8f94; }
        .eshb-rule-help:hover { color: #2271b1; }
        .eshb-rule-help .csf-help-text { display: none; }
        .eshb-rule-tip { position: absolute; z-index: 100100; max-width: 340px; padding: 10px 12px; border-radius: 3px; background: #1d2327; color: #f0f0f1; font-size: 12px; line-height: 1.6; box-shadow: 0 2px 8px rgba( 0, 0, 0, .25 ); }
        </style>
        <script>
        ( function( $ ) {

            var ALL       = <?php echo wp_json_encode( ESHB_Booking_Rules::TARGET_ALL ); ?>,
                MIN_FIELD = <?php echo wp_json_encode( ESHB_Booking_Rules::MIN_RULES_FIELD ); ?>,
                MAX_FIELD = <?php echo wp_json_encode( ESHB_Booking_Rules::MAX_RULES_FIELD ); ?>,
                DAY_FIELD = <?php echo wp_json_encode( ESHB_Booking_Rules::CHECK_IN_DAY_RULES_FIELD ); ?>,
                SELECTOR     = '.eshb-nights-rule-accomodations',
                DAY_SELECTOR = '.eshb-check-in-day-choices',
                FIELDS    = {},
                timer     = null,
                tipTimer  = null,
                $tip      = null;

            // Only the rules tables get the summary/Edit treatment. Holidays shares the
            // tab but stays the plain repeater it has always been, so it is not listed
            // here and nothing below touches it.
            //
            // kind decides how a row's value is read and printed: a number of nights, or
            // the days ticked in a check-in day rule.
            FIELDS[ MIN_FIELD ] = { column: 'Minimum stay', kind: 'nights' };
            FIELDS[ MAX_FIELD ] = { column: 'Maximum stay', kind: 'nights' };
            FIELDS[ DAY_FIELD ] = { column: 'Check-in day', kind: 'days' };

            function escape( text ) {
                return $( '<div/>' ).text( text ).html();
            }

            function wrapper( fieldId ) {
                return $( '.csf-repeater-wrapper[data-field-id="[' + fieldId + ']"]' );
            }

            function fieldIdOf( $element ) {

                var id = $element.closest( '.csf-field-repeater' ).find( '.csf-repeater-wrapper' ).first().attr( 'data-field-id' ) || '';

                id = id.replace( '[', '' ).replace( ']', '' );

                return FIELDS.hasOwnProperty( id ) ? id : '';
            }

            /* --- "All" as a check-all -------------------------------------------- */

            function allBox( $field ) {
                return $field.find( 'input[type="checkbox"]' ).filter( function() {
                    return this.value === ALL;
                } );
            }

            function accomodationBoxes( $field ) {
                return $field.find( 'input[type="checkbox"]' ).not( allBox( $field ) );
            }

            // Mirror downwards only, and only when "All" is on. Doing it in both
            // directions here would wipe a saved per accomodation selection on load.
            function syncAll() {

                $( SELECTOR + ', ' + DAY_SELECTOR ).each( function() {

                    var $field = $( this ),
                        $all   = allBox( $field );

                    if ( $all.length && $all.prop( 'checked' ) ) {
                        accomodationBoxes( $field ).prop( 'checked', true );
                    }

                    renderClearAll( $field );
                } );
            }

            // Once everything is ticked there is no checkbox left to clear the list with
            // — unticking "All" is what would do it, but only if that is the one you
            // click. This gives every list a single "Uncheck all".
            function renderClearAll( $field ) {

                var $link = $field.find( '.eshb-choices-clear' );

                if ( ! $link.length ) {
                    $link = $( '<a href="#" class="eshb-choices-clear">Uncheck all</a>' );
                    $field.children( '.csf-fieldset' ).append( $link );
                }

                $link.toggle( $field.find( 'input[type="checkbox"]' ).not( ':checked' ).length === 0 );
            }

            /* --- the list ------------------------------------------------------- */

            // One table per rules repeater, inserted above its rows. Runs again after
            // rows are added so a repeater rendered later still gets one.
            function build() {

                $.each( FIELDS, function( fieldId ) {

                    var $wrapper = wrapper( fieldId );

                    if ( ! $wrapper.length || $wrapper.data( 'eshbRuleTable' ) ) {
                        return;
                    }

                    $wrapper.data( 'eshbRuleTable', true );
                    $wrapper.closest( '.csf-field-repeater' ).addClass( 'eshb-rule-repeater' );

                    // The conflict notice is a sibling of the table, not part of it, so
                    // redrawing one row's table does not wipe it. Only the nights tables
                    // can conflict, so only they get the placeholder.
                    var conflicts = ( fieldId === MIN_FIELD || fieldId === MAX_FIELD )
                        ? '<div class="eshb-rule-conflicts"></div>'
                        : '';

                    $wrapper.before(
                        conflicts +
                        '<div class="eshb-rule-table" data-for="' + fieldId + '"></div>'
                    );
                } );

                moveHelp();
            }

            // Move the "?" marker right after the field title instead of the far right of
            // the row, so it reads as belonging to "… Nights Rules".
            //
            // It also stops being a `.csf-help`: the framework's own tooltip drops the
            // bubble the moment the pointer leaves the icon, which makes text this long
            // impossible to read. Renaming the class (and clearing any handler already
            // bound) takes it out of eshb_help()'s reach whichever order the two scripts
            // happen to run in, and the hover handling below takes over.
            function moveHelp() {

                $( '.eshb-rule-repeater' ).each( function() {

                    var $field = $( this ),
                        $help  = $field.children( '.csf-fieldset' ).children( '.csf-help' );

                    if ( $help.length ) {
                        $help.off()
                            .removeClass( 'csf-help' )
                            .addClass( 'eshb-rule-help' )
                            .appendTo( $field.children( '.csf-title' ).children( 'h4' ) );
                    }
                } );
            }

            /* --- help tooltip ---------------------------------------------------- */

            function hideTip() {

                if ( $tip ) {
                    $tip.remove();
                    $tip = null;
                }
            }

            // Closing on a delay is what lets the pointer travel from the marker to the
            // bubble; entering the bubble cancels it, leaving it starts it again.
            function scheduleHide() {
                window.clearTimeout( tipTimer );
                tipTimer = window.setTimeout( hideTip, 250 );
            }

            function showTip( $help ) {

                window.clearTimeout( tipTimer );
                hideTip();

                $tip = $( '<div class="eshb-rule-tip"></div>' )
                    .html( $help.children( '.csf-help-text' ).html() )
                    .appendTo( 'body' )
                    .on( 'mouseenter', function() {
                        window.clearTimeout( tipTimer );
                    } )
                    .on( 'mouseleave', scheduleHide );

                var offset  = $help.offset(),
                    maxLeft = $( window ).width() - $tip.outerWidth() - 20;

                // Below the marker and left aligned to it, pulled back in if that would
                // run off the right edge.
                $tip.css( {
                    top:  offset.top + $help.outerHeight() + 6,
                    left: Math.max( 10, Math.min( offset.left, maxLeft ) )
                } );
            }

            // What one row currently says, read straight off its inputs.
            function summarise( $item, kind ) {

                var value = '',
                    names = [],
                    all   = false;

                if ( kind === 'days' ) {

                    var days   = [],
                        anyDay = false;

                    $item.find( DAY_SELECTOR + ' input[type="checkbox"]:checked' ).each( function() {
                        if ( this.value === ALL ) {
                            anyDay = true;
                        } else {
                            days.push( $( this ).closest( 'label' ).find( '.csf--text' ).text() );
                        }
                    } );

                    // With "All Days" ticked every weekday is ticked too, so listing them
                    // all would just be noise — the rule is simply not restricting anything.
                    value = anyDay ? 'Any day' : days.join( ', ' );

                } else {

                    var nights = parseInt( $item.find( 'input[type="number"]' ).first().val(), 10 );

                    value = ( nights > 0 ) ? nights + ( nights === 1 ? ' night' : ' nights' ) : '';
                }

                $item.find( SELECTOR + ' input[type="checkbox"]:checked' ).each( function() {
                    if ( this.value === ALL ) {
                        all = true;
                    } else {
                        names.push( $( this ).closest( 'label' ).find( '.csf--text' ).text() );
                    }
                } );

                return {
                    value: value,
                    scope: all ? 'All accomodations' : ( names.length ? names.join( ', ' ) : '' )
                };
            }

            function render( fieldId ) {

                var $table = $( '.eshb-rule-table[data-for="' + fieldId + '"]' ),
                    $items = wrapper( fieldId ).children( '.csf-repeater-item' ),
                    kind   = FIELDS[ fieldId ].kind,
                    rows   = '';

                if ( ! $table.length ) {
                    return;
                }

                $items.each( function( index ) {

                    var $item   = $( this ),
                        rule    = summarise( $item, kind ),
                        editing = $item.hasClass( 'eshb-editing' ),
                        // Icons only, so the label lives in title/aria-label.
                        editLabel = editing ? 'Done editing' : 'Edit rule',
                        editIcon  = editing ? 'fa-check' : 'fa-pencil-alt';

                    rows += '<tr' + ( editing ? ' class="eshb-rule-open"' : '' ) + '>' +
                        '<td>' + ( rule.value
                            ? escape( rule.value )
                            : '<span class="eshb-rule-unset">not set</span>' ) + '</td>' +
                        '<td>' + ( rule.scope
                            ? escape( rule.scope )
                            : '<span class="eshb-rule-unset">nothing selected</span>' ) + '</td>' +
                        '<td class="eshb-rule-actions">' +
                            '<button type="button" class="eshb-rule-icon eshb-rule-edit" data-index="' + index + '"' +
                                ' title="' + editLabel + '" aria-label="' + editLabel + '">' +
                                '<i class="fas ' + editIcon + '"></i>' +
                            '</button>' +
                            '<button type="button" class="eshb-rule-icon eshb-rule-delete" data-index="' + index + '"' +
                                ' title="Delete rule" aria-label="Delete rule">' +
                                '<i class="fas fa-trash-alt"></i>' +
                            '</button>' +
                        '</td>' +
                        '</tr>';
                } );

                if ( ! rows ) {
                    $table.html( '<p class="description eshb-rule-empty">No rule yet — use "Add Rule" below to create one.</p>' );
                    return;
                }

                $table.html(
                    '<table><thead><tr>' +
                        '<th>' + FIELDS[ fieldId ].column + '</th>' +
                        '<th>Applies to</th>' +
                        '<th class="eshb-rule-actions">Actions</th>' +
                    '</tr></thead><tbody>' + rows + '</tbody></table>'
                );
            }

            function renderAll() {
                build();
                $.each( FIELDS, function( fieldId ) {
                    render( fieldId );
                } );
                renderConflicts();
            }

            /* --- conflict warning ------------------------------------------------ */

            // Every accomodation a rule row can target, minus the "All" entry. Read from
            // the repeater's hidden template too, so the list is there before any rule is.
            function accomodationList() {

                var list = [];

                $( SELECTOR ).first().find( 'input[type="checkbox"]' ).each( function() {
                    if ( this.value !== ALL ) {
                        list.push( { id: this.value, name: $( this ).closest( 'label' ).find( '.csf--text' ).text() } );
                    }
                } );

                return list;
            }

            function rulesOf( fieldId ) {

                var list = [];

                wrapper( fieldId ).children( '.csf-repeater-item' ).each( function() {

                    var $item   = $( this ),
                        nights  = parseInt( $item.find( 'input[type="number"]' ).first().val(), 10 ),
                        targets = [];

                    $item.find( SELECTOR + ' input[type="checkbox"]:checked' ).each( function() {
                        targets.push( this.value );
                    } );

                    if ( nights > 0 && targets.length ) {
                        list.push( { nights: nights, targets: targets } );
                    }
                } );

                return list;
            }

            // Mirrors ESHB_Booking_Rules::resolve_nights_rule(), including skipping the
            // ids that ride along with "all" so they do not read as explicit targets.
            function resolve( list, id, strictest ) {

                var specific = null,
                    forAll   = null;

                $.each( list, function( i, rule ) {

                    if ( $.inArray( ALL, rule.targets ) !== -1 ) {
                        forAll = ( forAll === null ) ? rule.nights : strictest( forAll, rule.nights );
                        return;
                    }

                    if ( $.inArray( id, rule.targets ) !== -1 ) {
                        specific = ( specific === null ) ? rule.nights : strictest( specific, rule.nights );
                    }
                } );

                return ( specific !== null ) ? specific : forAll;
            }

            // The two tables resolve independently, so nothing stops a minimum from one
            // and a maximum from the other landing the wrong way round — which leaves the
            // accomodation impossible to book. Worth saying out loud at configure time;
            // the limits themselves are left exactly as configured.
            function renderConflicts() {

                var minRules = rulesOf( MIN_FIELD ),
                    maxRules = rulesOf( MAX_FIELD ),
                    items    = '';

                $.each( accomodationList(), function( i, accomodation ) {

                    var min = resolve( minRules, accomodation.id, Math.max ),
                        max = resolve( maxRules, accomodation.id, Math.min );

                    if ( min !== null && max !== null && min > max ) {
                        items += '<li><strong>' + escape( accomodation.name ) + '</strong> — minimum ' +
                            min + ', maximum ' + max + '</li>';
                    }
                } );

                $( '.eshb-rule-conflicts' ).html( items
                    ? '<div class="notice notice-error inline"><p><strong>These accomodations cannot be booked at all</strong> — ' +
                      'the minimum stay is longer than the maximum. Raise the maximum, lower the minimum, or add a rule for them ' +
                      'in the other table.</p><ul>' + items + '</ul></div>'
                    : '' );
            }

            function refresh() {
                syncAll();
                renderAll();
            }

            // Debounced: rows are added, cloned, sorted and removed (behind a confirm
            // dialog) without any event of their own, so let the DOM settle first.
            function schedule() {
                window.clearTimeout( timer );
                timer = window.setTimeout( refresh, 200 );
            }

            // Only one row open at a time, so the form below the table always belongs
            // to the row you clicked.
            function open( fieldId, index ) {

                var $items = wrapper( fieldId ).children( '.csf-repeater-item' ),
                    $item  = $items.eq( index ),
                    wasOpen = $item.hasClass( 'eshb-editing' );

                $items.removeClass( 'eshb-editing' );

                if ( ! wasOpen ) {
                    $item.addClass( 'eshb-editing' );
                }

                render( fieldId );
            }

            /* --- wiring --------------------------------------------------------- */

            $( document ).on( 'click', '.eshb-rule-edit', function() {

                var $table = $( this ).closest( '.eshb-rule-table' );

                open( $table.attr( 'data-for' ), parseInt( $( this ).attr( 'data-index' ), 10 ) );
            } );

            $( document ).on( 'click', '.eshb-rule-delete', function() {

                var $table = $( this ).closest( '.eshb-rule-table' ),
                    fieldId = $table.attr( 'data-for' ),
                    index   = parseInt( $( this ).attr( 'data-index' ), 10 );

                // Reuse the repeater's own remove control so it reindexes the remaining
                // rows; it carries the framework's confirm dialog with it. Both happen
                // synchronously, so redraw straight away rather than on the debounce —
                // that keeps the buttons' row indexes from going stale mid-click.
                wrapper( fieldId ).children( '.csf-repeater-item' ).eq( index )
                    .find( '.csf-repeater-remove' ).first().trigger( 'click' );

                render( fieldId );
            } );

            // "Add Rule" is the repeater's own button. Let it append the row, then open
            // it straight away — an empty collapsed row would look like nothing happened.
            $( document ).on( 'click', '.csf-repeater-add', function() {

                var fieldId = fieldIdOf( $( this ) );

                window.setTimeout( function() {

                    if ( ! fieldId ) {
                        refresh();
                        return;
                    }

                    var $items = wrapper( fieldId ).children( '.csf-repeater-item' );

                    $items.removeClass( 'eshb-editing' );
                    $items.last().addClass( 'eshb-editing' );

                    syncAll();
                    renderAll();

                }, 0 );
            } );

            $( document ).on( 'click', '.csf-repeater-clone, .csf-repeater-remove', schedule );

            // "All" is a check-all over the list it heads — the accomodations, or the
            // weekdays in a check-in day rule. Both lists behave identically, and either
            // way "All" ticked and every entry ticked mean the same thing to
            // ESHB_Booking_Rules, so the two can never disagree.
            $( document ).on( 'change', SELECTOR + ' input[type="checkbox"], ' + DAY_SELECTOR + ' input[type="checkbox"]', function() {

                var $field  = $( this ).closest( SELECTOR + ', ' + DAY_SELECTOR ),
                    $others = accomodationBoxes( $field );

                if ( this.value === ALL ) {
                    $others.prop( 'checked', this.checked );
                } else {
                    // Unticking one drops "All"; ticking the last one puts it back.
                    allBox( $field ).prop( 'checked', $others.length > 0 && $others.not( ':checked' ).length === 0 );
                }

                renderClearAll( $field );
                schedule();
            } );

            $( document ).on( 'click', '.eshb-choices-clear', function( e ) {

                e.preventDefault();

                var $field = $( this ).closest( SELECTOR + ', ' + DAY_SELECTOR );

                $field.find( 'input[type="checkbox"]' ).prop( 'checked', false );

                renderClearAll( $field );
                schedule();
            } );

            $( document ).on( 'change input', '.csf-repeater-wrapper input[type="number"]', schedule );

            $( document ).on( 'mouseenter', '.eshb-rule-help', function() {
                showTip( $( this ) );
            } );

            $( document ).on( 'mouseleave', '.eshb-rule-help', scheduleHide );

            // Positioned in document coordinates, so it scrolls along with its marker and
            // scroll needs no handling. A resize can invalidate the right edge clamp.
            $( window ).on( 'resize', hideTip );

            $( refresh );

        } )( jQuery );
        </script>
        <?php

    }, 20 );

    /**
     * Add a "Booking Rules" entry to the Easy Hotel admin menu that jumps straight
     * to the matching tab on the settings screen.
     *
     * The slug is a full URL rather than a page slug, so WordPress prints it as the
     * link href untouched — that keeps the "#tab=" fragment intact, which is what the
     * settings framework reads on load to open the right tab. Passing an empty
     * callback is what puts core on that code path (a callback would make it rewrite
     * the href and encode the fragment away).
     *
     * Priority 10 lands it after the submenus registered in admin-settings.php and
     * before "Settings", which is re-added at priority 999.
     */
    add_action( 'admin_menu', function(){

        add_submenu_page(
            'edit.php?post_type=eshb_accomodation',
            'Booking Rules',
            'Booking Rules',
            'manage_options',
            'edit.php?post_type=eshb_accomodation&page=easy-hotel-settings#tab=booking-rules',
            ''
        );

    } );

}
