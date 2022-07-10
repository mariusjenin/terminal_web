let repetition_select = $("select[name='repetition']");
let input_date_start = $(".input_date_start");


/**
 * Permet d'afficher les bons champs dans le formulaire en fonction de la valeur du champ repetition
 */
repetition_select.change(function () {
    switch ($(this).val()) {
        case "OUVRES" :
        case "WEEKEND":
            if (input_date_start.hasClass("d-flex")) {
                input_date_start.fadeOut(300, function () {
                    input_date_start.addClass("d-none");
                    input_date_start.removeClass("d-flex");
                })
            }
            break;
        case "UNIQUE" :
        case "HEBDOMADAIRE" :
        case "MENSUEL" :
        case "ANNUEL":
            if (input_date_start.hasClass("d-none")) {
                input_date_start.fadeIn(300, function () {
                    input_date_start.addClass("d-flex");
                    input_date_start.removeClass("d-none");
                })
            }
            break;
    }
});

repetition_select.change();

/**
 * Actualise tous les arrêts/horaires
 * @param res
 */
function refreshHourStops(res) {
    let num = 1;
    let box_stops_create_itinerary = $(".box_stops_create_itinerary");
    $(".box_stops_create_itinerary .stop_box").remove();
    setTimeout(function () {
        for (let [key, value] of Object.entries(res)) {
            box_stops_create_itinerary.append(
                ` <div class="stop_box d-flex flex-column justify-content-center align-items-center pt-3 px-2 pb-2 m-3 position-relative">
                    <div class="count_number position-absolute d-flex justify-content-center align-items-center p-1">
                        ${num}
                    </div>
                    <label class="title_box mb-2 text-center">
                        ${value["name"]}
                    </label>
                    <input class="form-control" type="time" name="hour_trip[]" value="${value['time']}" required>
                </div>`
            );
            num++;
        }
    }, 0);
}

/**
 * Affiche une alerte avec le message d'erreur
 * @param jqXHR
 * @param textStatus
 * @param err
 */
function errorAlert(jqXHR, textStatus, err) {
    alert(jqXHR.responseJSON.error);
}