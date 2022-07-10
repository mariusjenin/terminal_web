let email_user_like = $("input[name=email_user_like]");
let no_email_user = $(".no_email_user");

let inputs_password = $("input[name=password],input[name=password2]");

inputs_password.on("input", function () {
    let val = $(this).val();
    let name_bro;
    switch ($(this).attr('name')) {
        case "password":
            name_bro = "password2";
            break;
        case "password2":
            name_bro = "password";
            break;
    }
    let input_bro = $($(this).parents()[1]).find("input[name=" + name_bro + "]");
    let check_btn = $($(this).parents()[2]).find(".btn_submit");

    if (val.length === 0 || input_bro.val().length === 0) {
        check_btn.addClass("deactivated")
    } else {
        check_btn.removeClass("deactivated")
    }
});

/**
 * Permet de filtrer les résultats
 */
email_user_like.on("input", function () {
    let val = $(this).val();
    let users = $(".line_user_content>div:first-child");
    let regex_str = ".*";
    for (let i = 0; i < val.length; i++) {
        regex_str += val[i] + ".*";
    }
    regex_str += "";
    let regex = new RegExp(regex_str);

    let all_hidden = 1;
    users.each(function () {
        let text = $(this).text();
        let parent = $($(this).parents()[1]);
        if (regex.test(text)) {
            all_hidden = 0;
            parent.removeClass("d-none")
            parent.addClass("d-flex")
        } else {
            parent.find("input[name=password]").val("");
            parent.find("input[name=password2]").val("");
            parent.find(".btn_submit").addClass("deactivated");
            parent.removeClass("d-flex")
            parent.addClass("d-none")
        }
    })

    if (all_hidden === 1) {
        no_email_user.removeClass("d-none")
        no_email_user.addClass("d-block")
    } else {
        no_email_user.removeClass("d-block")
        no_email_user.addClass("d-none")
    }
});


let btns_delete = $(".btn_delete");
let btns_submit = $(".btn_submit");
let user_delete;
let user_submit;

btns_delete.click(function (e) {
    user_delete = $($(this).parents()[1])
})

btns_submit.click(function (e) {
    user_submit = $($(this).parents()[1])
})

modal_post_button(btns_delete, LANG["delete_user_title"],
    LANG["delete_user_desc"],
    undefined,
    function (res) {
        user_delete.fadeOut(200, function () {
            $(this).remove();
        })
    });

modal_post_button(btns_submit, LANG["edit_user_title"],
    LANG["edit_user_desc"],
    function (elem) {
        let parent = $($(elem).parents()[1]);
        let password = parent.find("input[name=password]").val();
        let password2 = parent.find("input[name=password2]").val();
        return {password: password, password2: password2};
    },
    function (res) {
        user_submit.find("input[name=password]").val("");
        user_submit.find("input[name=password2]").val("");
        user_submit.find(".btn_submit").addClass("deactivated");
        alert(JSON.parse(res).success);
    },
    function (jqXHR, textStatus, err) {
        alert(jqXHR.responseJSON.error);
    },
    function (elem) {
        return !$(elem).hasClass("deactivated");
    });
