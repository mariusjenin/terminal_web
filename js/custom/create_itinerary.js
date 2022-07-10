const no_stops = `<div class="no_stops">${LANG["no_stops_for_the_moment"]}</div>`;

const stop_str = LANG["stop"];

const markerHtmlStyles = `
  width: 1.5rem;
  height: 1.5rem;
  display: block;
  left: -0.75rem;
  top: -0.25rem;
  position: relative;
  border-radius: 2rem 2rem 0 2rem ;
  transform: rotate(45deg);
  border: 1px solid #000000`;

let initial_coords = [49.348388163438, 6.589050292968];
let max_zoom = 22;
let initial_zoom = 7;
let zoom_fly_bounds = 8;
let listMarker = {};
let current_color = "#ffffff";
let current_num = 0;

/**
 * Génère une couleur aléatoire relativement éloignée de la précèdente
 * @param crnt_olor
 * @returns {string}
 */
function getRandomColor(crnt_olor = current_color) {
    let letters = '0123456789ABCDEF';
    let color = '#';
    let letter = "";
    for (let i = 0; i < 6; i++) {
        do {
            letter = letters[Math.floor(Math.random() * 16)];
        } while (letter >= crnt_olor[i + 1] - 2 && letter <= crnt_olor[i + 1] + 2);
        color += letter;
    }
    current_color = color;
    return color;
}

/**
 * Donne le code css pour une icone d'un marker
 * @returns {string}
 */
function getMarkerStyle() {
    return `background-color: ${getRandomColor()};` + markerHtmlStyles
}

/**
 * Donne l'icone pour un marker
 * @returns {*}
 */
function getIcon() {
    return L.divIcon({
        className: "my-custom-pin",
        iconAnchor: [0, 24],
        labelAnchor: [-6, 0],
        popupAnchor: [0, -36],
        html: `<span style="${getMarkerStyle()}" >`
    });
}

let stops_list = $(".itinerary_edit_box_list");
let no_stops_elem = $(".no_stops");

/**
 * Affiche le div no-stops s'il n'y a pas d'arrêts
 */
function actualizeNoStops() {
    let stop_boxes = $(".stop_box[data-num]");
    if (stops_list.find(".no_stops").length === 0 && stop_boxes.length === 0) {
        stops_list.append(no_stops);
        no_stops_elem = $(".no_stops");
    }
}

actualizeNoStops();

/**
 * Affiche le popup sur la carte corrspondant au num
 * @param num
 */
function displayPopup(num) {
    listMarker[num].openPopup();
}

/**
 * Modifie le popup sur la carte corrspondant au num
 * @param str
 * @param num
 */
function modifyPopup(str, num) {
    listMarker[num].unbindPopup();
    listMarker[num].bindPopup(str).openPopup();
}

/**
 * Ferme le popup sur la carte corrspondant au num
 * @param str
 * @param num
 */
function closePopup(num) {
    listMarker[num].closePopup();
}

let inExchange = false;

/**
 * Echange de place 2 Arrêts
 * @param num_1
 * @param num_2
 */
function exchangeStopBox(num_1, num_2) {
    if (inExchange === false) {
        inExchange = true;
        if (num_1 > num_2) {
            let num_tmp = num_1;
            num_1 = num_2;
            num_2 = num_tmp;
        }

        let stop_box_1 = $(".stop_box[data-num='" + num_1 + "']");
        let stop_box_2 = $(".stop_box[data-num='" + num_2 + "']");


        stop_box_2.fadeTo(300, 0);
        stop_box_1.fadeTo(300, 0, function () {


            stop_box_1.css("order", num_2);
            stop_box_2.css("order", num_1);
            stop_box_1.attr("data-num", num_2);
            stop_box_2.attr("data-num", num_1);
            stop_box_1.find(".counter_stop").html(num_2 + 1);
            stop_box_2.find(".counter_stop").html(num_1 + 1);
            let input_1 = stop_box_1.find("input");
            let input_2 = stop_box_2.find("input");

            let name_1 = stop_str + " n°" + (num_1 + 1);
            let name_2 = stop_str + " n°" + (num_2 + 1);
            if (input_1.val() === name_1) {
                input_1.val(name_2);
                listMarker[num_1].unbindPopup();
                listMarker[num_1].bindPopup(name_2).openPopup();
            }
            if (input_2.val() === name_2) {
                input_2.val(name_1);
                listMarker[num_2].unbindPopup();
                listMarker[num_2].bindPopup(name_1).openPopup();
            }


            let tmp = listMarker[num_1];
            listMarker[num_1] = listMarker[num_2];
            listMarker[num_2] = tmp;

            let right_stop_1 = stop_box_1.find(".btn_right_stop");
            let left_stop_1 = stop_box_1.find(".btn_left_stop");
            let right_stop_2 = stop_box_2.find(".btn_right_stop");
            let left_stop_2 = stop_box_2.find(".btn_left_stop");
            let deactivated_right_1 = right_stop_1.hasClass("deactivated");
            let deactivated_left_1 = left_stop_1.hasClass("deactivated");
            let deactivated_right_2 = right_stop_2.hasClass("deactivated");
            let deactivated_left_2 = left_stop_2.hasClass("deactivated");

            if (deactivated_right_1) {
                if (!deactivated_right_2) {
                    right_stop_2.addClass("deactivated");
                    right_stop_1.removeClass("deactivated");
                }
            } else {
                if (deactivated_right_2) {
                    right_stop_1.addClass("deactivated");
                    right_stop_2.removeClass("deactivated");
                }
            }
            if (deactivated_left_1) {
                if (!deactivated_left_2) {
                    left_stop_2.addClass("deactivated");
                    left_stop_1.removeClass("deactivated");
                }
            } else {
                if (deactivated_left_2) {
                    left_stop_1.addClass("deactivated");
                    left_stop_2.removeClass("deactivated");
                }
            }

            stop_box_2.fadeTo(300, 1);
            stop_box_1.fadeTo(300, 1, function () {
                inExchange = false;
            });
            drawItinerary();
        });
    }
}


/**
 * Ajoute un Arrêt
 * @param cur_num
 * @param cur_col
 * @param marker
 */
function addStopBox(cur_num, cur_col, marker) {
    marker.addTo(mymap);
    listMarker[cur_num] = marker;
    let stop_box_to_activate = $(".stop_box[data-num='" + (cur_num - 1) + "']");
    stop_box_to_activate.find(".btn_right_stop.deactivated").removeClass("deactivated");

    let deactivated_left = "";
    if (cur_num === 0) {
        deactivated_left = "deactivated";
    }

    let name = stop_str + " n°" + (cur_num + 1);
    let stop_box = `
    <div onmouseleave="closePopup($(this).attr('data-num'))" onmouseover="displayPopup($(this).attr('data-num'))" data-num="${cur_num}" class="m-3 p-2 stop_box stop_box_1" style="order: ${cur_num};">
        <input oninput="modifyPopup($(this).val(),$($(this).parent()).attr('data-num'))" class="form-control mb-2" type="text"
               placeholder="${LANG["name_of_the_itinerary"]}"
               value="${name}"
               required>
        <div class="d-flex justify-content-center align-items-center">
            <div style="border-color: ${cur_col}" class="flex-grow-0 flex-shrink-1 counter_stop d-flex justify-content-center align-items-center">
                ${cur_num + 1}
            </div>
            <div class="flex-grow-1 flex-shrink-1 d-flex justify-content-center align-items-center">
                <div onclick="if(!$(this).hasClass('deactivated')){let num = parseInt($($(this).parents()[2]).attr('data-num'));exchangeStopBox(num-1,num)}" class="btn_hoverable btn_left_stop ${deactivated_left} d-flex justify-content-center align-items-center mr-2">
                    <img src="./img/icons/triangle.png">
                </div>
                <div onclick="if(!$(this).hasClass('deactivated')){let num = parseInt($($(this).parents()[2]).attr('data-num'));exchangeStopBox(num,num+1)}" class="btn_hoverable btn_right_stop deactivated d-flex justify-content-center align-items-center ml-2">
                    <img src="./img/icons/triangle.png">
                </div>
            </div>
            <div onclick="removeStopBox($($(this).parents()[1]).attr('data-num'))" class="flex-grow-0 flex-shrink-1 btn_hoverable btn_delete_stop d-flex justify-content-center align-items-center">
                <img src="./img/icons/bin.png">
            </div>
        </div>
    </div>`;
    marker.bindPopup(name).openPopup();
    stops_list.append(stop_box);
    no_stops_elem.remove();
    current_num++;
    drawItinerary();
    centerAroundBounds();
}


var mymap = L.map('map_create_itinerary').setView(initial_coords, 15);

/**
 * Supprime un arrêt
 * @param num
 */
function removeStopBox(num) {
    let stop_box = $(".stop_box[data-num='" + num + "']");
    let marker = listMarker[num];

    stop_box.fadeOut('500', function () {
        $(this).remove()
        marker.removeFrom(mymap);
        delete listMarker[num];

        drawItinerary()
        centerAroundBounds();

        let num_iterator = parseInt(num) + 1;
        while (true) {
            let stop_box = $(".stop_box[data-num='" + num_iterator + "']");
            if (stop_box.length === 0) {
                break;
            } else {
                let num_this_new = num_iterator - 1;
                stop_box.attr("data-num", num_this_new);
                stop_box.css("order", num_this_new);
                stop_box.find(".counter_stop").html(num_this_new + 1);
                listMarker[num_this_new] = listMarker[num_iterator];
                delete listMarker[num_iterator];
                let input = stop_box.find("input");
                if (input.val() === stop_str + " n°" + (num_iterator + 1)) {
                    let name = stop_str + " n°" + (num_iterator);
                    input.val(name);
                    modifyPopup(name, num_this_new);
                }
                num_iterator++;
            }
        }

        if (num + "" === "0") {
            let stop_box_to_deactivate_left = $(".stop_box[data-num='" + num + "']");
            stop_box_to_deactivate_left.find(".btn_left_stop").addClass("deactivated");
        }
        if (num + "" === num_iterator + "") {
            let stop_box_to_deactivate_right = $(".stop_box[data-num='" + (num - 1) + "']");
            stop_box_to_deactivate_right.find(".btn_right_stop").addClass("deactivated");
        }


        actualizeNoStops();
    });
    current_num--;
}


L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: LANG["credit_OpenStreetMap_MapBox"],
    maxZoom: max_zoom,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: tokenMapBox
}).addTo(mymap);
mymap.setZoom(initial_zoom);

/**
 * Centre la carte autour des coordonnées des arrêts
 */
function centerAroundBounds() {
    let checkbox_center = $(".switch_fit_bounds input[type=checkbox]")[0];
    let values = Object.values(listMarker);
    if (checkbox_center.checked && values.length > 0) {
        mymap.flyToBounds(new L.featureGroup(values).getBounds(), {maxZoom: zoom_fly_bounds});
    }
}


let in_drag = false;

/**
 * Effectue les affichages nécessaires lors d'un click sur la carte
 * @param e
 */
function onMapClick(e) {
    if (!in_drag) {
        var marker = L.marker(e.latlng, {icon: getIcon(), draggable: true, autoPan: true})
        marker.on('dragstart', function (event) {
            in_drag = true;
            mymap.once('zoomend moveend', function () {
                in_drag = false;
            });
        });
        marker.on('drag', function (event) {
            drawItinerary();
        });
        marker.on('dragend', function (event) {
            drawItinerary();
            centerAroundBounds();
        });
        addStopBox(current_num, current_color, marker);
    }
}

mymap.on('click', onMapClick);
mymap.on('zoomend', function () {
    zoom_fly_bounds = mymap.getZoom();
});

/**
 * Ferme tous les popup sur la carte
 */
function closeAllPopup() {
    mymap.closePopup();
}

let polyline;
let itinerary_is_detailed = false;

/**
 * Dessine un itinéraire simple (à vol d'oiseau entre les arrêts)
 */
function drawItinerary() {
    let checkbox_itinerary = $(".switch_see_itinerary input[type=checkbox]")[0];

    if (polyline !== undefined) {
        polyline.removeFrom(mymap);
    }
    let markers = Object.values(listMarker);
    if (checkbox_itinerary.checked) {
        let coords = markers.map(function (m) {
            let latlng = m.getLatLng();
            return [latlng.lat, latlng.lng]
        });
        polyline = L.polyline(coords, {color: 'black'}).addTo(mymap);
        itinerary_is_detailed = false;
    }
    if (markers.length > 1) {
        $($(".btn_display_itinerary")[0]).removeClass("deactivated");
    } else {
        $($(".btn_display_itinerary")[0]).addClass("deactivated");
    }
}

/**
 * Dessine un itinéraire détaillé (selon les routes grâce à des données du serveur)
 */
function drawItineraryDetailed(url) {
    let markers = Object.values(listMarker);

    if (!$($(".btn_display_itinerary")[0]).hasClass("deactivated") && markers.length > 1) {
        let coords = markers.map(function (m) {
            let latlng = m.getLatLng();
            return [latlng.lng, latlng.lat]
        });


        $($(".btn_display_itinerary")[0]).addClass("deactivated");
        post_button(url, {coords: coords}, function (res) {

            if (polyline !== undefined) {
                polyline.removeFrom(mymap);
            }

            res = Object.values(res).map(function (a) {
                return [a[1], a[0]]
            });
            polyline = L.polyline(res, {color: 'black'}).addTo(mymap);
            itinerary_is_detailed = true;
        }, function (jqXHR, textStatus, err) {
            $($(".btn_display_itinerary")[0]).removeClass("deactivated");
            alert(LANG["points_too_far_from_routes"]);
        });
    }
}

/**
 * Créé l'itinéraire qui a été construit
 * @param event
 * @returns {boolean}
 */
function submit_create_itinerary(event) {
    let markers = Object.values(listMarker);
    if (markers.length > 1) {
        let data = {};

        let stops = [];
        for (let i in markers) {
            let m = markers[i];
            let latlng = m.getLatLng();
            let input = $(".stop_box[data-num='" + i + "'] input");
            stops.push([latlng.lat, latlng.lng, input.val()]);
        }

        data["stops"] = stops;

        return submit_form(event, data);
    } else {
        let error = $('.error_form');

        error.removeClass('d-none');
        error.addClass('d-flex');
        error.text(LANG["itinerary_with_atleast_two_points"])
    }
    return false;
}