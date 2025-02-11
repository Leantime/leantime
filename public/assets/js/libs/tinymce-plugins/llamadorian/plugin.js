/* global tinymce */

tinymce.PluginManager.add('llamadorian', function (editor) {

    const openDialog = function () {
        /**
         * Create a list of prompts for the select box
         */
        const PROMPTS = [
            {text: "Summarize", value: "summary"},
            {text: "Proofread", value: "proofread"},
            {text: "Structure Meeting Notes", value: "meetingMinutes"},
            {text: "Rewrite to be friendlier", value: "writeFriendlier"},
            {text: "Rewrite to be more professional", value: "writeProfessional"},
            {text: "Extract Action Items", value: "getActionItems"},
            {text: "Create Implementation Intention", value: "createII"},
            {text: "Brainstorm Solutions", value: "brainstorm"},
            {text: "Write a blog post about", value: "writeBlogPost"}
        ];

        return editor.windowManager.open({
            title: 'L.E.O. Writing Support',
            body: {
                type: 'panel',
                items: [
                    {
                        type: 'textarea',
                        name: 'llamadorianContent',
                        label: 'Provide your content here',
                    },
                    {
                        type: 'selectbox',
                        name: 'prompt',
                        label: 'Select what you would like to do',
                        items: PROMPTS
                    }
                ]
            },
            buttons: [
                {
                    type: 'cancel',
                    text: 'Close'
                },
                {
                    type: 'submit',
                    text: 'Generate',
                    primary: true
                }
            ],

            /**
             * Set the default input to the current selection
             */
            initialData: {
                llamadorianContent: tinymce.activeEditor.selection.getContent({format : 'text'}) ?? tinymce.activeEditor.getContent({format : 'text'})
            },

            onSubmit: function (api) {

                // Change button text to "Loading" again
                api.block('Loading...')

                const data = api.getData()
                const input = data.llamadorianContent
                let prompt = {
                    prompt: data.prompt,
                    content: input
                }

                getResponseFromLeo(prompt)
                    .then((res) => res.json())
                    .then((data) => {

                        // Insert the reply into the editor
                        editor.insertContent(data)
                        // Close the dialog
                        api.close()
                    })
                    .catch((error) => {
                        console.log('something went wrong' + error)
                    })
            }
        })
    }

    /**
     * Get the current selection and set it as the default input
     * @param prompt
     * @returns {Promise<Response>}
     */
    function getResponseFromLeo (prompt) {
        const baseUri = leantime.appUrl+'/llamadorian/editorTools'

        return fetch(baseUri, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(prompt)
        })
    }

    /* Add a chatgpt icon */
    editor.ui.registry.addIcon(
        'llamadorian',
        '<span class="fancyLink">🪄 LEO AI</span>\n'
    )

    /* Add a button that opens a window */
    editor.ui.registry.addButton('llamadorian', {
        text: '<span class="fancyLink"><i class="fa-solid fa-wand-magic-sparkles"></i> Ai Tools</span>',
        tooltip: 'Leo Helper',
        onAction: function () {
            /* Open window */
            openDialog()
        }
    })

    /* Adds a menu item, which can then be included in any menu via the menu/menubar configuration */
    editor.ui.registry.addMenuItem('llamadorian', {
        text: 'ChatGPT',

        onAction: function () {
            /* Open window */
            openDialog()
        }
    })

    editor.ui.registry.addContextMenu("AI Tools", {
        update: function(element) {
            return "AI Tools";
        },
        onAction: function() {
            openDialog();
        }
    });

    /* Return the metadata for the help plugin */
    return {
        getMetadata: function () {
            return {
                name: 'TinyMCE Llamadorian Plugin',
                url: 'https://marketplace.leantime.io/product/leantime-ai/'
            }
        }
    }
});
