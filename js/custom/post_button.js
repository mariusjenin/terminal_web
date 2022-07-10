/**
 * Permet à un element de faire une requête post et de définir des callbacks
 * @param url
 * @param data
 * @param done
 * @param fail
 */
function post_button(
    url,
    data = {},
    done = function (res) {
        window.location.href = res;
    },
    fail = function (jqXHR, textStatus, err) {
    }
) {
    $.ajax(
        {
            url: url,
            type: 'POST',
            data: data
        }
    ).done(function (res) {
        done(res);
    }).fail(function (jqXHR, textStatus, err) {
        fail(jqXHR, textStatus, err);
    });
}