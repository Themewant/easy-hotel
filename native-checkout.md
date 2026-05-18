#Todo list
1. Create a custom checkout page (use html and css already designed). In checkout page, booking price is calculated dynamically based on selected additional services, extras and coupon if applied. Price update should be instantly. no api request or ajax. You can use localizational data or client side code or something for calculation.
2. Insert booking after payment success.
3. Capture payment after booking inserted.
4. Now payment gateway is only PayPal. But structure it in a way that new payment gateways can be added easily. e.g. Stripe, etc.
5. Send confirmation email to customer after booking inserted. class.core.php file handle sending emails. e.g. $this->send_html_email();
6. Send confirmation email to admin after booking inserted. class.core.php file handle sending emails. e.g. $this->send_html_email();

#Procedure
1. Add a booking type in plugin settings booking-type select field. (E:\local\app\almaris\app\public\wp-content\plugins\easy-hotel\admin\includes\admin-settings.php)
2. Send selected reservation data to custom checkout page when click booking form submit button.
3. After payment processing insert booking with status `on-hold`. see create_woocommerce_booking_on_checkout() method in class.booking.php file.
4. Capture payment after booking inserted. see capture_payment_after_checkout() method in class.booking.php file.
5. Update booking status to `processing`.
6. all code and files should be inside `app/public/wp-content/plugins/easy-hotel/admin/includes/native-checkout/` directory.