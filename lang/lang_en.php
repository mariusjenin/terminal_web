<?php
/**
 * Associatives arrays defining strings in english
 */

define('LANG_IDENTIFICATION', [
    "email_empty" => "The e-mail must be filled in",

    "sign_up_bad_syntax_email" => "The e-mail is not correct",
    "sign_up_pwd_too_short" => "The password must be at least 6 characters long",
    "sign_up_pwd_differents" => "The passwords are different",
    "sign_up_account_existing" => "This account already exists",
    "sign_up_error_save" => "Unable to register\n Please try again later",
    "sign_up_success" => "Your account has been created",

    "sign_in_email_unknown" => 'The email does not correspond to any user',
    "sign_in_bad_login_password" => 'The password is incorrect',
    "sign_in_success" => "You have been connected",

    "sign_in_auto_error" => 'Your account could not be retrieved',
    "sign_in_auto_success" => 'You have been connected',

    "no_email" => "Please enter your login email",
    "bad_id_password" => "Incorrect login or password",
    "error_logout" => "Unable to disconnect",

    "sign_in" => "Connection",
    "email" => "Email",
    "your_email" => "Your email",
    "password" => "Password",
    "your_password" => "Your password",
    "logout" => "Logout"
]);

define('LANG_DATA', [
    "data_generated_success" => "The data was successfully generated",
    "data_saved_success" => "The data was successfully recorded",
    "data_saved_error" => "An error prevented the data from being recorded",
    "data_not_found" => "No results found",
    "token_missing" => "A token must be provided",
    "data_missing" => "Data must be provided",
    "bad_token" => "The token provided is wrong",
    "incorrect_id" => "The id provided is incorrect",
    "cant_access_to_data" => "You do not have access to this data",
    "error_value" => "Some values are not appropriate"
]);

define('LANG_GLOBAL', [
    "menu" => "Menu",
    "home" => "Home",
]);

define('LANG_TRIP', [
    "future_trips_actual_position" => "Your position",
    "future_trips_now" => "now",
    "future_trips_bad_stops" => "Error when retrieving stops for a future trip",
    "bad_trip" => "The selected trip is wrong",

    "trips_of" => "Trips of",
    "see_all_itineraries" => "See all itineraries",
    "no_trips_for_itinerary" => "No journey for this itinerary at present",
    "start" => "Start",
    "end" => "End",
    "new_trip" => "Create a new trip on this itinerary",
    "new_trip_title" => "Create a trip for the itinerary",
    "edit_trip_title" => "Edit a trip of the itinerary",
    "create_this_trip" => "Create this trip",
    "edit_this_trip" => "Edit this Trip",
    "see_trips_of_same_itinerary" => "See the trips of the same itinerary",
    "hour_start_trip" => "Time of start of the trip",
    "regularite" => "Regularity of the trip",
    "first_date_trip" => "(First) date of trip.",
    "nb_places" => "Number of seats in the vehicle",
    "driver" => "Driver of the trip",
    "hours" => "Hours"
]);

define('LANG_ITINERARY', [
    "itineraries" => "Itineraries",
    "create_itinerary" => "Create an itinerary",
    "create_this_itinerary" => "Create this itineray",
    "stops" => "Stops",
    "no_trips" => "No trip for this itinerary at the moment",
    "from_m_to_f" => "Monday to Friday",
    "weekend" => "Weekend",
    "path" => "Path",
    "center" => "Center",
    "name_itinerary" => "Name of the itinerary",
    "name_itinerary_desc" => "(Users will see this name in the application)",
    "click_to_display" => "Click on the map to add stops",
    "error_during_itinerary_creation" => "A problem occurred during the creation of the itinerary",
    "see_itinerary_path_detailed" => "See the overview of the itinerary in detail"

]);

define('LANG_USER', [
    "create_account_privilege" => "Create a privileged account",
    "create_account" => "Create an account",
    "see_all_users" => "See all users",
    "driver" => "Driver",
    "admin" => "Adminstrator",
    "driver_desc" => "access to the mobile application as a driver",
    "admin_desc" => "access to the web platform to manage itineraries, trips and users",
    "email_account_to_create" => "Email of the account to be created",
    "confirm" => "Confirmation",
    "create_this_account" => "Create this account",
    "users_list" => "List of users",
    "filtrate_result" => "Filter results",
    "no_user_with_email" => "No users with this email",
    "new_password" => "New password",
    "users" => "Users",
]);

define('LANG_RESERVATION', [
    "reservation_duplicate" => "The reservation already exists",
    "reservation_error" => "An error occurred during your booking",
    "reservation_success" => "Your reservation has been registered",
    "reservation_not_soon" => "No trips are planned in the next few hours for this itinerary",
    "reservation_pos_too_far" => "You are too far from this itinerary to book from your location",

    "bad_reservation" => "The selected reservation is wrong",

    "reservation_cancelled" => "Your booking has been cancelled",
    "reservation_cancel_error" => "An error occurred during the cancellation of your booking"
]);

define('LANG_DATE', [
    "format_hour" => "H:i",
    "format_date" => "Y-m-d",

//    DAYS
    "monday" => "Monday",
    "tuesday" => "Tuesday",
    "wednesday" => "Wednesday",
    "thursday" => "Thursday",
    "friday" => "Friday",
    "saterday" => "Saturday",
    "sunday" => "Sunday",
]);