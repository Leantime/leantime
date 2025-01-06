import TomSelect from "tom-select/dist/esm/tom-select.complete.js";
import { appUrl } from "../core/instance-info.module.mjs";

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

export const initSelect = function (element, config= '') {

    let activePlugins = ['no_active_items'];

    console.log("initSelect");

    config = JSON.parse(config);


    if(config.search === true) {

        activePlugins.push('dropdown_input');
    }

    let selectConfig = {
        create: false,
        plugins: activePlugins,
        controlInput: null,
        allowEmptyOption: true,
        //searchField: null,
        openOnFocus: true,
        maxOptions: null,
        maxItems: 1,
        hideSelected: false,
        closeAfterSelect: true,
        loadingClass: "loading-select",
        duplicates: false,
        optionClass: 'option',
        itemClass: 'item',
        onDelete: function(data) {
            return false; // Disable remove element.
        },
        render: {
            option: function (data, escape) {
                return '<div>' + data.text + '</div>';
            },
            item: function (data, escape) {
                return '<div>' + data.text + '</div>';
            },
            option_create: function (data, escape) {
                return '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>';
            },
            no_results: function (data, escape) {
                return '<div class="no-results">No results found for "' + escape(data.input) + '"</div>';
            },
            not_loading: function (data, escape) {
                // no default content
            },
            optgroup: function (data) {
                let optgroup = document.createElement('div');
                optgroup.className = 'optgroup';
                optgroup.appendChild(data.options);
                return optgroup;
            },
            optgroup_header: function (data, escape) {
                return '<div class="optgroup-header">' + escape(data.label) + '</div>';
            },
            loading: function (data, escape) {
                return '<div class="spinner"></div>';
            },
            dropdown: function () {
                return '<div></div>';
            }
        }
    }

    const mergedConfig = { ...selectConfig, ...config };

    return new TomSelect(element, mergedConfig);

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

export const selects = {
  initSelect: initSelect,
  initTags: initTags,
};

export default selects;
