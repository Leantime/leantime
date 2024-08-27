leantime.selects = (function () {

    var initSelect = function(element, choices, enableSearch) {

        const select = new Choices(element, {
            choices: choices,
            editItems: false,
            addChoices: choices,
            addItems: true,
            allowHTML: true,
            searchEnabled: enableSearch,
            duplicateItemsAllowed: false,

            renderSelectedChoices: 'auto',
            loadingText: 'Loading...',
            noResultsText: 'No results found',
            noChoicesText: 'No choices to choose from',
            itemSelectText: '',
            uniqueItemText: 'Only unique values can be added',
            customAddItemText: 'Only values matching specific conditions can be added',
            addItemText: (value) => {
                return `Press Enter to add "${value}"`;
            },
            maxItemText: (maxItemCount) => {
            return `Only ${maxItemCount} values can be added`;
        },
    });

        select.passedElement.element.addEventListener(
            'addItem',
            function(event) {
                // do something creative here...
                console.log(event.detail.id);
                console.log(event.detail.value);
                console.log(event.detail.label);
                console.log(event.detail.customProperties);
                console.log(event.detail.groupValue);
            },
            false,
        );

        select.passedElement.element.addEventListener(
            'addChoice',
            function(event) {
                // do something creative here...
                console.log(event.detail.id);
                console.log(event.detail.value);
                console.log(event.detail.label);
                console.log(event.detail.customProperties);
                console.log(event.detail.groupValue);
            },
            false,
        );
    }

    var initTags = function(element, choices, enableSearch, autoCompleteTags) {

        let choiceList = choices.split(",");
        const select = new Choices(element, {
            editItems: false,
            addItems: true,
            allowHTML: true,
            addChoices: choiceList,
            searchEnabled: enableSearch,
            duplicateItemsAllowed: false,
            addItemText: (value) => {
                return `Press Enter to add "${value}"`;
            },
            placeholderValue: '🏷️ Add a tag',
            renderSelectedChoices: 'auto',
            loadingText: 'Loading...',
            noResultsText: 'No results found',
            noChoicesText: 'No choices to choose from',
            itemSelectText: '',
            uniqueItemText: 'Only unique values can be added',
            customAddItemText: 'Only values matching specific conditions ' +
                'can be added',
            searchPlaceholderValue: 'Search for tags',
            removeItemButton: true,
            maxItemText: (maxItemCount) => {
                return `Only ${maxItemCount} values can be added`;
            },
        });

        if(autoCompleteTags) {
            select.setChoices(function (callback) {
                return fetch(
                    leantime.appUrl + '/api/tags'
                ).then(function (res) {
                    console.log(res);
                    return res.json();
                })
                    .then(function (data) {
                        console.log(data);
                        return data.map(function (release) {
                            return {label: release, value: release};
                        });

                    });
            });
        }

        select.passedElement.element.addEventListener(
            'addItem',
            function(event) {
                // do something creative here...
                console.log(event.detail.id);
                console.log(event.detail.value);
                console.log(event.detail.label);
                console.log(event.detail.customProperties);
                console.log(event.detail.groupValue);
            },
            false,
        );

        select.passedElement.element.addEventListener(
            'addChoice',
            function(event) {
                // do something creative here...
                console.log(event.detail.id);
                console.log(event.detail.value);
                console.log(event.detail.label);
                console.log(event.detail.customProperties);
                console.log(event.detail.groupValue);
            },
            false,
        );
    }

    return {
        initSelect:initSelect,
        initTags:initTags
    };

})();
