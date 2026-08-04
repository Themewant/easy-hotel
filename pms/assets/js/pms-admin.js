/**
 * Easy Hotel - PMS module admin script.
 *
 * Only the room rack needs scripting: the unit editor and the assignment selects are stock
 * settings-framework fields and are already handled by the framework's own bundle.
 */
( function ( $ ) {
    'use strict';

    var settings = window.eshbPms || {};
    var i18n = settings.i18n || {};

    /**
     * Removes any open popover.
     */
    function closePopover() {
        $( '.eshb-pms-popover' ).remove();
    }

    /**
     * Opens the assign popover for a booking bar.
     *
     * @param {jQuery} $bar The clicked bar.
     */
    function openPopover( $bar ) {

        closePopover();

        var offset = $bar.offset();

        var $popover = $( '<div class="eshb-pms-popover" />' )
            .append( $( '<strong />' ).text( i18n.assignTo || 'Assign to' ) )
            .append( $( '<select disabled />' ).append( $( '<option />' ).text( i18n.saving || 'Loading…' ) ) )
            .append( $( '<span class="eshb-pms-popover-status" />' ) )
            .css( { top: offset.top + $bar.outerHeight() + 4, left: offset.left } )
            .appendTo( document.body );

        $.post( settings.ajaxUrl, {
            action: 'eshb_pms_get_free_units',
            nonce: settings.nonce,
            booking: $bar.data( 'booking' ),
            slot: $bar.data( 'slot' )
        } ).done( function ( response ) {

            if ( ! response || ! response.success ) {
                $popover.find( '.eshb-pms-popover-status' )
                    .addClass( 'is-error' )
                    .text( ( response && response.data && response.data.message ) || i18n.error );
                return;
            }

            var $select = $( '<select />' );

            $select.append( $( '<option />' ).val( 0 ).text( i18n.unassign || '— Not assigned —' ) );

            $.each( response.data.units, function ( index, unit ) {
                $select.append( $( '<option />' ).val( unit.index ).text( unit.label ) );
            } );

            $select.val( response.data.current || 0 );

            if ( ! response.data.units.length ) {
                $popover.find( '.eshb-pms-popover-status' ).text( i18n.noFreeUnit );
            }

            $popover.find( 'select' ).replaceWith( $select );

            $select.on( 'change', function () {
                saveAssignment( $bar, $popover, parseInt( $select.val(), 10 ) );
            } );

        } ).fail( function () {
            $popover.find( '.eshb-pms-popover-status' ).addClass( 'is-error' ).text( i18n.error );
        } );
    }

    /**
     * Persists a slot assignment and reloads the rack.
     *
     * @param {jQuery} $bar      Clicked bar.
     * @param {jQuery} $popover  Open popover.
     * @param {number} unitIndex Chosen unit, 0 to release.
     */
    function saveAssignment( $bar, $popover, unitIndex ) {

        var $status = $popover.find( '.eshb-pms-popover-status' ).removeClass( 'is-error' ).text( i18n.saving );

        $.post( settings.ajaxUrl, {
            action: 'eshb_pms_assign',
            nonce: settings.nonce,
            booking: $bar.data( 'booking' ),
            slot: $bar.data( 'slot' ),
            unit: unitIndex
        } ).done( function ( response ) {

            if ( ! response || ! response.success ) {
                $status.addClass( 'is-error' ).text( ( response && response.data && response.data.message ) || i18n.error );
                return;
            }

            window.location.reload();

        } ).fail( function () {
            $status.addClass( 'is-error' ).text( i18n.error );
        } );
    }

    /**
     * Room rack interactions.
     */
    function initRack() {

        var $chart = $( '.eshb-pms-chart' );

        if ( ! $chart.length ) {
            return;
        }

        $chart.on( 'click', '.eshb-pms-bar', function ( event ) {

            if ( $( event.target ).is( '.eshb-pms-bar-edit' ) ) {
                return; // Let the edit link through.
            }

            event.preventDefault();
            event.stopPropagation();

            openPopover( $( this ) );
        } );

        $( document ).on( 'click', function ( event ) {
            if ( ! $( event.target ).closest( '.eshb-pms-popover' ).length ) {
                closePopover();
            }
        } );

        $( '.eshb-pms-auto-all' ).on( 'click', function () {

            var $button = $( this );

            if ( ! window.confirm( i18n.confirmAll ) ) {
                return;
            }

            $button.prop( 'disabled', true );

            $.post( settings.ajaxUrl, {
                action: 'eshb_pms_auto_assign_window',
                nonce: settings.nonce,
                accommodation: $button.data( 'accommodation' ),
                from: $button.data( 'from' ),
                to: $button.data( 'to' )
            } ).done( function ( response ) {

                if ( response && response.success ) {
                    window.location.reload();
                    return;
                }

                window.alert( ( response && response.data && response.data.message ) || i18n.error );
                $button.prop( 'disabled', false );

            } ).fail( function () {
                window.alert( i18n.error );
                $button.prop( 'disabled', false );
            } );
        } );
    }

    $( function () {
        initRack();
    } );

}( jQuery ) );
