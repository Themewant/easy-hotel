( function( $ ) {

    var CONFIG    = window.eshbBookingRules || {},
        ALL       = CONFIG.all,
        MIN_FIELD = CONFIG.minField,
        MAX_FIELD = CONFIG.maxField,
        DAY_FIELD = CONFIG.dayField,
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
