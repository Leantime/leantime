tinymce.PluginManager.add('slashcommands', function (editor) {
    var insertActions = [
        {
            text: 'Confetti',
            icon: '🎉',
            action: function () {
                editor.execCommand('mceInsertContent', false, '<a class="confetti">🎉 Congrats</a>');
                confetti.start();
            }
        },
        {
            type: 'separator'
        },
        {
            text: 'Checklist list',
            icon: 'checklist',
            action: function () {
                editor.execCommand('insertChecklist', false);
            }
        }
    ];

    // Register the slash commands autocompleter
    editor.ui.registry.addAutocompleter('slashcommands', {
        ch: '/',
        minChars: 0,
        columns: 1,
        fetch: function (pattern) {
            const matchedActions = insertActions.filter(function (action) {
                return action.type === 'separator' ||
                    action.text.toLowerCase().indexOf(pattern.toLowerCase()) !== -1;
            });

            return new Promise((resolve) => {
                var results = matchedActions.map(function (action) {
                    return {
                        meta: action,
                        text: action.text,
                        icon: action.icon,
                        value: action.text,
                        type: action.type
                    }
                });
                resolve(results);
            });
        },
        onAction: function (autocompleteApi, rng, action, meta) {
            editor.selection.setRng(rng);
            // Some actions don't delete the "slash", so we delete all the slash
            // command content before performing the action
            editor.execCommand('Delete');
            meta.action();
            autocompleteApi.hide();
        }
    });
    return {};
});
