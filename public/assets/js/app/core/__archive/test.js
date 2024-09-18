jquery(".datetime-dropdown").each(function (dropdown) {
    dropdown.click(event => event.stopPropagation());

    const datetime = dropdown.data("datetime");

    leantime.initDatePicker(dropdown);

    leantime.dateController.toggleTime(dropdown, dropdown.querySelector('button'));
});
