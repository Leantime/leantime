import TomSelect from "tom-select/dist/esm/tom-select.complete.js";
import { appUrl } from "../core/instance-info.module.mjs";
import { selectManager } from "./componentManager/SelectManager.mjs";

function getOptions(selectElement) {
  const items = [];

  if (jQuery(selectElement).children()) {
    jQuery(selectElement)
      .children()
      .each(function (option) {
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

export const initComponent = function () {

}

export const initSelect = function (element, enableSearch, additionalClasses) {
    return selectManager.initializeSelect(element, {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
};

export const initTags = function (
  element,
  enableSearch,
  autoCompleteTags,
  additionalClasses,
  maxItemCount = 4
) {
  let outerClasses = ["select"];

  if (additionalClasses !== "") {
    const selectClasses = additionalClasses.trim().split(" ");
    outerClasses = selectClasses;
  }

  const select = new Choices(element, {
    editItems: true,
    addItems: true,
    allowHTML: true,
    addChoices: true,
    searchEnabled: enableSearch,
    duplicateItemsAllowed: false,
    addItemText: (value) => {
      return `Press Enter to add "${value}"`;
    },
    placeholderValue: "🏷️ Add a tag",
    noChoicesText: "No choices option",
    uniqueItemText: "Only unique values can be added",
    searchPlaceholderValue: "Search for tags",
    removeItemButton: true,
    maxItemCount: maxItemCount,
    maxItemText: (maxItemCount) => {
      return `Only ${maxItemCount} values can be added`;
    },
    classNames: {
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
  initTags: initTags,
};
