<?php
/**
 * Associatives arrays defining strings in french
 */

define('LANG_IDENTIFICATION', [
    "email_empty" => "L'e-mail doit être renseigné",

    "sign_up_bad_syntax_email" => "L'e-mail n'est pas correct",
    "sign_up_pwd_too_short" => "Le mot de passe doit être de 6 caractères minimum",
    "sign_up_pwd_differents" => "Les mot de passes sont différents",
    "sign_up_account_existing" => "Ce compte existe déjà",
    "sign_up_error_save" => "Impossible de vous inscrire\n Veuillez réessayer plus tard",
    "sign_up_success" => "Votre compte à été créé",

    "sign_in_email_unknown" => 'L\'email ne correspondant à aucun utilisateur',
    "sign_in_bad_login_password" => 'Le mot de passe est incorrect',
    "sign_in_success" => "Vous avez été connecté",

    "sign_in_auto_error" => 'Votre compte n\'a pas pu être récupéré',
    "sign_in_auto_success" => 'Vous avez été connecté',

    "no_email" => "Veuillez indiquer votre e-mail de connexion",
    "bad_id_password" => "Identifiant ou mot de passe incorrect",
    "error_logout" => "Impossible de se déconnecter",

    "sign_in" => "Connexion",
    "email" => "Email",
    "your_email" => "Votre email",
    "password" => "Mot de passe",
    "your_password" => "Votre mot de passe",
    "logout" => "Déconnexion"
]);

define('LANG_DATA', [
    "data_generated_success" => "Les données on été générées avec succès",
    "data_saved_success" => "Les données on été enregistrées avec succès",
    "data_saved_error" => "Une erreur a empeché les données d'être enregistrées",
    "data_not_found" => "Aucun résultat trouvé",
    "token_missing" => "Un token doit être fourni",
    "data_missing" => "Des données doivent être fournies",
    "bad_token" => "Le token fourni est mauvais",
    "incorrect_id" => "L'id fourni est incorrect",
    "cant_access_to_data" => "Vous n'avez pas accès à ces données",
    "error_value" => "Certaines valeurs ne conviennent pas"
]);

define('LANG_GLOBAL', [
    "menu" => "Menu",
    "home" => "Accueil",
]);

define('LANG_TRIP', [
    "future_trips_actual_position" => "Votre position",
    "future_trips_now" => "maintenant",
    "future_trips_bad_stops" => "Erreur lors de la récupération des arrêts d'un trajet à venir",
    "bad_trip" => " Le trajet selectionné est mauvais",

    "trips_of" => "Trajets de",
    "see_all_itineraries" => "Voir tous les itinéraires",
    "no_trips_for_itinerary" => "Pas de trajet pour cet itinéraire actuellement",
    "start" => "Départ",
    "end" => "Arrivée",
    "new_trip" => "Créer un nouveau trajet sur cet itinéraire",
    "new_trip_title" => "Créer un trajet pour l'itinéraire",
    "edit_trip_title" => "Modifier un trajet de l'itinéraire",
    "create_this_trip" => "Créer ce trajet",
    "edit_this_trip" => "Modifier ce trajet",
    "see_trips_of_same_itinerary" => "Voir les trajets du même itinéraire",
    "hour_start_trip" => "Heure de départ du trajet",
    "regularite" => "Régularité du trajet",
    "first_date_trip" => "(Première) date du trajet",
    "nb_places" => "Nombre de place dans le véhicule",
    "driver" => "Conducteur du trajet",
    "hours" => "Horaires"
]);

define('LANG_ITINERARY', [
    "itineraries" => "Itinéraires",
    "create_itinerary" => "Créer un itinéraire",
    "create_this_itinerary" => "Créer cet itinéraire",
    "stops" => "Arrêts",
    "no_trips" => "Pas de trajet pour cet itinéraire actuellement",
    "from_m_to_f" => "Du L au V",
    "weekend" => "Weekend",
    "path" => "Chemin",
    "center" => "Centrer",
    "name_itinerary" => "Nom de cet itinéraire",
    "name_itinerary_desc" => "(Les utilisateurs verront ce nom dans l'application)",
    "click_to_display" => "Cliquez sur la carte pour ajouter des arrêts",
    "error_during_itinerary_creation" => "Un problème a eu lieu pendant la création de l'itinéraire",
    "see_itinerary_path_detailed" => "Voir l'aperçu de l'itinéraire en détail"

]);

define('LANG_USER', [
    "create_account_privilege" => "Créer un compte à privilèges",
    "create_account" => "Créer un compte",
    "see_all_users" => "Voir tous les utilisateurs",
    "driver" => "Conducteur",
    "admin" => "Administrateur",
    "driver_desc" => "accès à l’application mobile en tant que conducteur",
    "admin_desc" => "accès à la plateforme Web pour gérer les itinéraires, les trajets et les utilisateurs",
    "email_account_to_create" => "Email du compte à créer",
    "confirm" => "Confirmation",
    "create_this_account" => "Créer ce compte",
    "users_list" => "Liste des utilisateurs",
    "filtrate_result" => "Filtrer les résultats",
    "no_user_with_email" => "Pas d'utilisateurs avec cet email",
    "new_password" => "Nouveau mot de passe",
    "users" => "Utilisateurs",
]);

define('LANG_RESERVATION', [
    "reservation_duplicate" => "La reservation existe déjà",
    "reservation_error" => "Une erreur a eu lieu pendant votre reservation",
    "reservation_success" => "Votre réservation a été enregistrée",
    "reservation_not_soon" => "Aucun trajet n'est prévu dans les prochaines heures pour cet itinéraire",
    "reservation_pos_too_far" => "Vous êtes trop loin de cet itinéraire pour réserver depuis votre position",

    "bad_reservation" => "La réservation selectionnée est mauvaise",

    "reservation_cancelled" => "Votre réservation a été annulée",
    "reservation_cancel_error" => "Une erreur a eu lieu pendant l'annulation de votre reservation"
]);

define('LANG_DATE', [
    "format_hour" => "H\hi",
    "format_date" => "d/m/Y",

//    DAYS
    "monday" => "Lundi",
    "tuesday" => "Mardi",
    "wednesday" => "Mercredi",
    "thursday" => "Jeudi",
    "friday" => "Vendredi",
    "saterday" => "Samedi",
    "sunday" => "Dimanche",
]);
