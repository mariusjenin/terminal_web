<?php
/**
 * Associatives arrays defining strings in german
 */


define('LANG_IDENTIFICATION', [
    "email_empty" => "Die E-Mail muss ausgefüllt werden",

    "sign_up_bad_syntax_email" => "Die E-Mail ist nicht korrekt",
    "sign_up_pwd_too_short" => "Das Passwort muss mindestens 6 Zeichen lang sein",
    "sign_up_pwd_differents" => "Die Passwörter sind unterschiedlich",
    "sign_up_account_existing" => "Dieses Konto existiert bereits",
    "sign_up_error_save" => "Registrierung nicht möglich Bitte versuchen Sie es später erneut",
    "sign_up_success" => "Ihr Konto wurde erstellt",

    "sign_in_email_unknown" => 'Die E-Mail gehört zu keinem Benutzer',
    "sign_in_bad_login_password" => 'Das Passwort ist falsch',
    "sign_in_success" => "Sie sind verbunden worden",

    "sign_in_auto_error" => 'Ihr Konto konnte nicht abgerufen werden',
    "sign_in_auto_success" => 'Sie sind verbunden worden',

    "no_email" => "Bitte geben Sie Ihre Login-E-Mail ein",
    "bad_id_password" => "Falsche Anmeldung oder falsches Passwort",
    "error_logout" => "Verbindung kann nicht getrennt werden",

    "sign_in" => "Verbindung",
    "email" => "E-Mail",
    "your_email" => "Ihre E-Mail",
    "password" => "Passwort",
    "your_password" => "Ihr Passwort",
    "logout" => "Abmelden"
]);

define('LANG_DATA', [
    "data_generated_success" => "Die Daten wurden erfolgreich erzeugt",
    "data_saved_success" => "Die Daten wurden erfolgreich aufgezeichnet",
    "data_saved_error" => "Ein Fehler hat die Aufzeichnung der Daten verhindert",
    "data_not_found" => "Keine Ergebnisse gefunden",
    "token_missing" => "Ein Token muss bereitgestellt werden",
    "data_missing" => "Daten müssen bereitgestellt werden",
    "bad_token" => "Das angegebene Token ist falsch",
    "incorrect_id" => "Die angegebene ID ist falsch",
    "cant_access_to_data" => "Sie haben keinen Zugriff auf diese Daten",
    "error_value" => "Einige Werte sind nicht geeignet"
]);

define('LANG_GLOBAL', [
    "menu" => "Menü",
    "home" => "Startseite",
]);

define('LANG_TRIP', [
    "future_trips_actual_position" => "Ihre Position",
    "future_trips_now" => "jetzt",
    "future_trips_bad_stops" => "Fehler beim Abrufen von Haltestellen für eine zukünftige Fahrt",
    "bad_trip" => "Der ausgewählte Trip ist falsch",

    "trips_of" => "Fahrten von",
    "see_all_itineraries" => "Alle Reiserouten anzeigen",
    "no_trips_for_itinerary" => "Zur Zeit keine Fahrt für diese Strecke",
    "start" => "Start",
    "end" => "Ende",
    "new_trip" => "Erstellen Sie eine neue Reise auf dieser Reiseroute",
    "new_trip_title" => "Eine Reise für die Reiseroute erstellen",
    "edit_trip_title" => "Bearbeiten einer Fahrt der Reiseroute",
    "create_this_trip" => "Diese Reise erstellen",
    "edit_this_trip" => "Diese Reise bearbeiten",
    "see_trips_of_same_itinerary" => "Siehe die Ausflüge der gleichen Reiseroute",
    "hour_start_trip" => "Uhrzeit des Starts der Fahrt",
    "regularite" => "Regelmäßigkeit der Fahrt",
    "first_date_trip" => "(Erstes) Datum der Reise",
    "nb_places" => "Anzahl der Sitze im Fahrzeug",
    "driver" => "Fahrer der Fahrt",
    "hours" => "Stunden"
]);

define('LANG_ITINERARY', [
    "itineraries" => "Reiserouten",
    "create_itinerary" => "Erstellen einer Reiseroute",
    "create_this_itinerary" => "Diese Reiseroute erstellen",
    "stops" => "Stoppt",
    "no_trips" => "Momentan keine Reise für diese Route",
    "from_m_to_f" => "Montag bis Freitag",
    "weekend" => "Wochenende",
    "path" => "Pfad",
    "center" => "Zentrum",
    "name_itinerary" => "Name der Reiseroute",
    "name_itinerary_desc" => "(Benutzer sehen diesen Namen in der Anwendung)",
    "click_to_display" => "Klicken Sie auf die Karte, um Haltestellen hinzuzufügen",
    "error_during_itinerary_creation" => "Bei der Erstellung der Reiseroute ist ein Problem aufgetreten",
    "see_itinerary_path_detailed" => "Sehen Sie sich die Übersicht der Reiseroute im Detail an"

]);

define('LANG_USER', [
    "create_account_privilege" => "Erstellen eines privilegierten Kontos",
    "create_account" => "Ein Konto erstellen",
    "see_all_users" => "Alle Benutzer anzeigen",
    "driver" => "Treiber",
    "admin" => "Adminstrator",
    "driver_desc" => "Zugriff auf die mobile Anwendung als Fahrer",
    "admin_desc" => "Zugriff auf die Web-Plattform zur Verwaltung von Reiserouten, Reisen und Benutzern",
    "email_account_to_create" => "E-Mail des zu erstellenden Kontos",
    "confirm" => "Konfirmation",
    "create_this_account" => "Dieses Konto erstellen",
    "users_list" => "Liste der Benutzer",
    "filtrate_result" => "Ergebnisse filtern",
    "no_user_with_email" => "Keine Benutzer mit dieser E-Mail",
    "new_password" => "Neues Passwort",
    "users" => "Benutzer",
]);

define('LANG_RESERVATION', [
    "reservation_duplicate" => "Die Reservierung ist bereits vorhanden",
    "reservation_error" => "Bei Ihrer Buchung ist ein Fehler aufgetreten",
    "reservation_success" => "Ihre Reservierung wurde registriert",
    "reservation_not_soon" => "Für diese Route sind in den nächsten Stunden keine Fahrten geplant",
    "reservation_pos_too_far" => "Sie sind zu weit von dieser Reiseroute entfernt, um von Ihrem Standort aus zu buchen",

    "bad_reservation" => "Die gewählte Reservierung ist falsch",

    "reservation_cancelled" => "Ihre Buchung wurde storniert",
    "reservation_cancel_error" => "Bei der Stornierung Ihrer Buchung ist ein Fehler aufgetreten"
]);

define('LANG_DATE', [
    "format_hour" => "H:i",
    "format_date" => "Y-m-d",

//    DAYS
    "monday" => "Montag",
    "tuesday" => "Dienstag",
    "wednesday" => "Mittwoch",
    "thursday" => "Donnerstag",
    "friday" => "Freitag",
    "saterday" => "Samstag",
    "sunday" => "Sonntag",
]);