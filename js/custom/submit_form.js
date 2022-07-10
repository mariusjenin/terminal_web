/**
 * Permet de submit un formulaire et de donner des fonctions pour traiter le success et le fail
 * @param event
 * @param data
 * @param done_function Une fonction unaire (`résultat -> void`) permettant de traiter un succès
 * @param fail_function Une fonction ternaire (`(requeteXHR, codeStatut, erreur) -> void`) permettant de traiter une erreur
 * @returns {boolean}
 */
function submit_form(
    event,
    data = {},
    done_function = function (res) {
        window.location.href = res;
    },
    fail_function = function (jqXHR, textStatus, err) {
        let error = $('.error_form');

        error.removeClass('d-none');
        error.addClass('d-flex');

        error.text(jqXHR.responseJSON.error);
    }
) {
    //S'il n y a pas de fonction pour traiter la reponse c'est qu'il faut juste rediriger la page à l'url fourni

    let error = $('.error_form');

    error.removeClass('d-flex');
    error.addClass('d-none');

    let fd = new FormData(event.target);
    for (let key in data) {
        let value = data[key];
        if (Array.isArray(value)) {
            for (let key_2 in value) {
                if (value.hasOwnProperty(key_2)) {
                    fd.append(key + '[]', value[key_2]);
                }
            }
        } else {
            fd.append(key, value);
        }
    }

    // for(var pair of fd.entries()) {
    //     console.log(pair[0]+ ', '+ pair[1]);
    // }

    $.ajax(
        {
            url: event.target.action,
            data: fd,
            type: event.target.dataset.method,
            processData: false,
            contentType: false,
            enctype: event.target.enctype
        }
    ).done(function (res) {
        done_function(res);
    }).fail(function (jqXHR, textStatus, err) {
        console.log(jqXHR.response)
        console.log(textStatus)
        console.log(err)
        fail_function(jqXHR, textStatus, err); // attention on veut pouvoir accéder à la XHRHttpRequest dans le callback
    });
    return false;
}
