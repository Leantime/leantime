tinymce.PluginManager.add('slashcommands', function (editor) {

    var insertActions = [
        {
            text: 'Text',
            icon: 'p',
            action: function () {
                editor.execCommand('mceInsertContent', false, '<p>Text</p>')
                editor.selection.select(editor.selection.getNode());
            }
        },
        {
            text: 'Heading 1',
            icon: 'h1',
            action: function () {
                editor.execCommand('mceInsertContent', false, '<h1>Heading 1</h1>')
                editor.selection.select(editor.selection.getNode());
            }
        },
        {
            text: 'Heading 2',
            icon: 'h2',
            action: function () {
                editor.execCommand('mceInsertContent', false, '<h2>Heading 2</h2>');
                editor.selection.select(editor.selection.getNode());
            }
        },
        {
            text: 'Heading 3',
            icon: 'h3',
            action: function () {
                editor.execCommand('mceInsertContent', false, '<h3>Heading 3</h3>');
                editor.selection.select(editor.selection.getNode());
            }
        },
        {
            type: 'separator'
        },
        {
            text: 'Table',
            icon: 'table',
            action: function () {
                editor.execCommand('mceInsertTable', true);
            }
        },
        {
            text: 'Checklist list',
            icon: 'checklist',
            action: function () {
                editor.execCommand('insertChecklist', false);
            }
        },
        {
            text: 'Bulleted list',
            icon: 'unordered-list',
            action: function () {
                editor.execCommand('InsertUnorderedList', false);
            }
        },
        {
            type: 'separator'
        },
        {
            text: 'Templates',
            icon: 'template',
            action: function () {
                editor.execCommand('mceTemplate');
            }
        },
        {
            text: 'Confetti',
            icon: '🎉',
            action: function () {
                editor.execCommand('mceInsertContent', false, '<a class="confetti">🎉 Confetti <span class="mirrorElement">🎉</span></a>&nbsp;');
                confetti.start();
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
