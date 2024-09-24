import Choices from "choices.js"
import { appUrl } from "./instance-info.module.mjs";
import {decode} from 'html-entities';
import jQuery from 'jquery';


function getOptions(selectElement) {
    const items = [];

    if (jQuery(selectElement).children()) {

        jQuery(selectElement).children().each(function(option) {

            var optionClone = jQuery(this).clone();
            items.push({
                value: optionClone.val(),
                label: decode(optionClone.html()),
                selected: optionClone.attr("selected"),
                disabled: optionClone.attr("disabled"),
            });

            jQuery(this).remove();

        });

    }

    return items;
}

export const initSelect = function (element, enableSearch, additionalClass) {

    let outerClasses = [];
    outerClasses.push("select");
    outerClasses.push("choices");

    if(additionalClass !== '') {
        outerClasses.push(additionalClass);
    }

  const select = new Choices(element, {
    editItems: false,
    addChoices: false,
    addItems: false,
    allowHTML: true,
    searchEnabled: enableSearch,
    duplicateItemsAllowed: false,
    choices: getOptions(element),
    renderSelectedChoices: "always",
    loadingText: "Loading...",
    noResultsText: "No results found",
    noChoicesText: "No choices to choose from",
    itemSelectText: "",
    uniqueItemText: "Only unique values can be added",
    customAddItemText: "Only values matching specific conditions can be added",
    addItemText: (value) => {
      return `Press Enter to add "${value}"`;
    },
    maxItemText: (maxItemCount) => {
      return `Only ${maxItemCount} values can be added`;
    },
    classNames: {
        containerOuter: outerClasses,
        containerInner: ["choices__inner"],
        input: ["choices__input"],
        inputCloned: [
            "choices__input--cloned",
        ],
        list: ["choices__list"],
        listItems: ["choices__list--multiple"],
        listSingle: ["choices__list--single"],
        listDropdown: ["choices__list--dropdown"],
        item: ["choices__item"],
        itemSelectable: ["choices__item--selectable"],
        itemDisabled: ["choices__item--disabled"],
        itemChoice: ["choices__item--choice"],
        description: ["choices__description"],
        placeholder: ["choices__placeholder"],
        group: ["choices__group"],
        groupHeading: ["choices__heading"],
        button: ["choices__button"],
        activeState: ["is-active"],
        focusState: ["is-focused"],
        openState: ["is-open"],
        disabledState: ["is-disabled"],
        highlightedState: ["is-highlighted"],
        selectedState: ["is-selected"],
        flippedState: ["is-flipped"],
        loadingState: ["is-loading"],
        notice: ["choices__notice"],
        addChoice: ["choices__item--selectable"],
        noResults: ["has-no-results"],
        noChoices: ["has-no-choices"],
    },
  });

  select.passedElement.element.addEventListener(
    "addItem",
    function (event) {
      // do something creative here...
    },
    false
  );

  select.passedElement.element.addEventListener(
    "addChoice",
    function (event) {
      // do something creative here...
    },
    false
  );
};

export const initTags = function (element, enableSearch, autoCompleteTags, additionalClass) {

    let outerClasses = [];
    outerClasses.push("select");
    outerClasses.push("choices");

    if(additionalClass !== '') {
        outerClasses.push(additionalClass);
    }

  const select = new Choices(element, {
    editItems: false,
    addItems: true,
    allowHTML: true,
    addChoices: true,
    searchEnabled: enableSearch,
    duplicateItemsAllowed: false,
    addItemText: (value) => {
      return `Press Enter to add "${value}"`;
    },
    placeholderValue: "🏷️ Add a tag",
    renderSelectedChoices: "auto",
    loadingText: "Loading...",
    noResultsText: "No results found",
    noChoicesText: "No choices to choose from",
    itemSelectText: "",
    uniqueItemText: "Only unique values can be added",
    customAddItemText:
      "Only values matching specific conditions " + "can be added",
    searchPlaceholderValue: "Search for tags",
    removeItemButton: true,
    maxItemText: (maxItemCount) => {
      return `Only ${maxItemCount} values can be added`;
    },
    classNames: {
      containerOuter: outerClasses,
      containerInner: ["choices__inner"],
      input: ["choices__input"],
      inputCloned: [
        "choices__input--cloned",

      ],
      list: ["choices__list"],
      listItems: ["choices__list--multiple"],
      listSingle: ["choices__list--single"],
      listDropdown: ["choices__list--dropdown"],
      item: ["choices__item"],
      itemSelectable: ["choices__item--selectable"],
      itemDisabled: ["choices__item--disabled"],
      itemChoice: ["choices__item--choice"],
      description: ["choices__description"],
      placeholder: ["choices__placeholder"],
      group: ["choices__group"],
      groupHeading: ["choices__heading"],
      button: ["choices__button"],
      activeState: ["is-active"],
      focusState: ["is-focused"],
      openState: ["is-open"],
      disabledState: ["is-disabled"],
      highlightedState: ["is-highlighted"],
      selectedState: ["is-selected"],
      flippedState: ["is-flipped"],
      loadingState: ["is-loading"],
      notice: ["choices__notice"],
      addChoice: ["choices__item--selectable"],
      noResults: ["has-no-results"],
      noChoices: ["has-no-choices"],
    },
  });

  if (autoCompleteTags) {
    select.setChoices(function (callback) {
      return fetch(appUrl + "/api/tags")
        .then(function (res) {
          console.log(res);
          return res.json();
        })
        .then(function (data) {
          console.log(data);
          return data.map(function (release) {
            return { label: release, value: release };
          });
        });
    });
  }

  select.passedElement.element.addEventListener(
    "addItem",
    function (event) {
      // do something creative here...
    },
    false
  );

  select.passedElement.element.addEventListener(
    "addChoice",
    function (event) {
      // do something creative here...
    },
    false
  );
};

export default {
  initSelect: initSelect,
  initTags: initSelect
};
