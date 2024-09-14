import Choices from "leantime.choices.js";
import { appUrl } from "./instance-info.module.js";

export const initSelect = function (element, enableSearch) {
  const select = new Choices(element, {
    editItems: false,
    addChoices: false,
    addItems: true,
    allowHTML: true,
    searchEnabled: enableSearch,
    duplicateItemsAllowed: false,

    renderSelectedChoices: "auto",
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
      containerOuter: ["choices", "select"],
      containerInner: ["choices__inner", "select-bordered"],
      input: ["choices__input", "input", "input-bordered", "w-full"],
      inputCloned: [
        "choices__input--cloned",
        "input",
        "input-bordered",
        "w-full",
      ],
      list: ["choices__list", "menu"],
      listItems: ["choices__list--multiple", "menu-item"],
      listSingle: ["choices__list--single", "menu-item"],
      listDropdown: ["choices__list--dropdown", "dropdown-content", "menu"],
      item: ["choices__item", "badge"],
      itemSelectable: ["choices__item--selectable", "badge-primary"],
      itemDisabled: ["choices__item--disabled"],
      itemChoice: ["choices__item--choice", "badge-outline"],
      description: ["choices__description", "text-sm", "label"],
      placeholder: ["choices__placeholder", "label-text"],
      group: ["choices__group", "group"],
      groupHeading: ["choices__heading", "label"],
      button: ["choices__button", "btn"],
      activeState: ["is-active", "active"],
      focusState: ["is-focused", "focus"],
      openState: ["is-open", "dropdown-open"],
      disabledState: ["is-disabled", "btn-disabled"],
      highlightedState: ["is-highlighted", "bg-primary"],
      selectedState: ["is-selected", "selected"],
      flippedState: ["is-flipped", "dropdown-top"],
      loadingState: ["is-loading", "loading"],
      notice: ["choices__notice", "alert", "alert-info"],
      addChoice: ["choices__item--selectable"],
      noResults: ["has-no-results", "alert", "alert-warning"],
      noChoices: ["has-no-choices", "alert", "alert-error"],
    },
  });

  select.passedElement.element.addEventListener(
    "addItem",
    function (event) {
      // do something creative here...
      console.log(event.detail.id);
      console.log(event.detail.value);
      console.log(event.detail.label);
      console.log(event.detail.customProperties);
      console.log(event.detail.groupValue);
    },
    false
  );

  select.passedElement.element.addEventListener(
    "addChoice",
    function (event) {
      // do something creative here...
      console.log(event.detail.id);
      console.log(event.detail.value);
      console.log(event.detail.label);
      console.log(event.detail.customProperties);
      console.log(event.detail.groupValue);
    },
    false
  );
};

export const initTags = function (element, enableSearch, autoCompleteTags) {
  // let choiceList = choices.split(",");
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
      containerOuter: ["choices", "select"],
      containerInner: ["choices__inner", "select-bordered"],
      input: ["choices__input", "input", "input-bordered", "w-full"],
      inputCloned: [
        "choices__input--cloned",
        "input",
        "input-bordered",
        "w-full",
      ],
      list: ["choices__list", "menu"],
      listItems: ["choices__list--multiple", "menu-item"],
      listSingle: ["choices__list--single", "menu-item"],
      listDropdown: ["choices__list--dropdown", "dropdown-content", "menu"],
      item: ["choices__item", "badge"],
      itemSelectable: ["choices__item--selectable", "badge-primary"],
      itemDisabled: ["choices__item--disabled"],
      itemChoice: ["choices__item--choice", "badge-outline"],
      description: ["choices__description", "text-sm", "label"],
      placeholder: ["choices__placeholder", "label-text"],
      group: ["choices__group", "group"],
      groupHeading: ["choices__heading", "label"],
      button: ["choices__button", "btn"],
      activeState: ["is-active", "active"],
      focusState: ["is-focused", "focus"],
      openState: ["is-open", "dropdown-open"],
      disabledState: ["is-disabled", "btn-disabled"],
      highlightedState: ["is-highlighted", "bg-primary"],
      selectedState: ["is-selected", "selected"],
      flippedState: ["is-flipped", "dropdown-top"],
      loadingState: ["is-loading", "loading"],
      notice: ["choices__notice", "alert", "alert-info"],
      addChoice: ["choices__item--selectable"],
      noResults: ["has-no-results", "alert", "alert-warning"],
      noChoices: ["has-no-choices", "alert", "alert-error"],
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
      console.log(event.detail.id);
      console.log(event.detail.value);
      console.log(event.detail.label);
      console.log(event.detail.customProperties);
      console.log(event.detail.groupValue);
    },
    false
  );

  select.passedElement.element.addEventListener(
    "addChoice",
    function (event) {
      // do something creative here...
      console.log(event.detail.id);
      console.log(event.detail.value);
      console.log(event.detail.label);
      console.log(event.detail.customProperties);
      console.log(event.detail.groupValue);
    },
    false
  );
};

export default {
  initSelect: initSelect,
  initTags: initTags,
};
