(function($) {

    let ESHBADMIN = {
        init: function () { 
            $( document )
            .on( 'click.ESHBADMIN', '.eshb-booking-info-calendar-tables a.booking-info', this.openBookingInfoModal )
            .on( 'click.ESHBADMIN', '#eshb-booking-info-modal .booking-info-modal-close', this.closeBookingInfoModal )
            .on( 'change.ESHBADMIN', '#eshb-calendar-filter-period', this.updateCalendarRangeField )
            .on( 'click.ESHBADMIN', '.has-required-notice button, .has-required-notice input', this.disableClick )
            ;

            const scrollContainer = document.querySelector('.eshb-booking-info-calendar .eshb-booking-info-calendar-tables .eshb-accomodations-dates-table-wrapper');
            const scrollSpeed = 3;

            if(scrollContainer){
                scrollContainer.addEventListener('wheel', function (e) {    
                // e.deltaY is vertical scroll, we use it to scroll horizontally
                if (e.deltaY !== 0) {
                    e.preventDefault();    
                    scrollContainer.scrollLeft += e.deltaY * scrollSpeed;
                }
                });
            }
            
        },
        
        clickTest: function () { 
            alert('working');
        },
        closeBookingInfoModal: function (e) { 
            e.preventDefault();
            $('#eshb-booking-info-modal').fadeOut();
        },
        disableClick: function(e) {
            e.preventDefault();
            return false;
        },
        updateCalendarRangeField: function (){
            let period = $(this).val();
            if(period != 'custom' ) {
                $('#eshb-calendar-filter-date-range').css('display', 'none');
                $('#eshb-calendar-filter-period .period-navigator').css('display', 'block');
            }else{
                $('#eshb-calendar-filter-date-range').css('display', 'block');
                $('#eshb-calendar-filter-period .period-navigator').css('display', 'none');
            }
        },
        openBookingInfoModal: function (e) { 
            e.preventDefault();

            let bookingId = $(this).data('booking-id');
            let sourceType = $(this).data('source-type');
            let bookingInfo = '';

            console.log(bookingId);	
           
            $.ajax({
                url: eshb_ajax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eshb_get_booking_data_tables',
                    post: bookingId,
                    nonce: eshb_ajax.nonce
                },
                success: function (response) {
                    console.log(response);
                    if (response) {
                        
                        
                        bookingInfo = response;
                        $('#eshb-booking-info-modal .booking-info-modal-content').html(bookingInfo);
                        $('#eshb-booking-info-modal').fadeIn();
                    } else {
                        alert('Error fetching booking info.');
                    }
                },
                error: function () {
                    alert('Error fetching booking info.');
                }
            });
            
        },
        
    }


    ESHBADMIN.init();
    

})(jQuery);