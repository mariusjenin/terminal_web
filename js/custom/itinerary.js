let btn_switch_planning_itinerary = $(".btn_switch_planning_itinerary");

btn_switch_planning_itinerary.click(function () {
    $(this).toggleClass("active");
    $($(this.parentElement.parentElement).find(".table_itineraries")[0].parentElement).slideToggle("500");
});

modal_post_button($(".btn_delete_itinerary"), LANG["delete_itinerary_title"], LANG["delete_itinerary_desc"]);