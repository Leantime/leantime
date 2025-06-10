/* global tinymce */

tinymce.PluginManager.add('aiTools', function (editor) {

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

            onSubmit: async function (api) {

                // Change button text to "Loading" again
                api.block('Loading...')

                const data = api.getData()
                const input = data.llamadorianContent
                let prompt = {
                    prompt: data.prompt,
                    content: input
                }

                try {
                    // Close dialog immediately and start streaming
                    api.close()
                    
                    // Insert a placeholder and start streaming
                    const placeholder = '<span id="ai-generation-placeholder" style="background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px;">✨ Generating...</span>';
                    editor.insertContent(placeholder);
                    
                    await streamResponseToEditor(prompt);
                    
                } catch (error) {
                    console.log('something went wrong' + error)
                    // Remove placeholder and show error
                    const placeholderElement = editor.dom.get('ai-generation-placeholder');
                    if (placeholderElement) {
                        editor.dom.remove(placeholderElement);
                    }
                    editor.insertContent('<span style="color: red;">Error generating content. Please try again.</span>');
                }
            }
        })
    }

    /**
     * Stream response directly to editor with real-time updates
     * @param prompt
     * @returns {Promise<void>}
     */
    async function streamResponseToEditor(prompt) {
        const copilotEndpoint = leantime.appUrl + '/copilot/action/editorTools';

        const response = await fetch(copilotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(prompt)
        });

        // Handle streaming response from Copilot
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let accumulatedResponse = '';
        let placeholderElement = editor.dom.get('ai-generation-placeholder');
        let firstChunk = true;

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop(); // Keep incomplete line in buffer

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const data = JSON.parse(line.slice(6));
                        
                        if (data.chunk) {
                            accumulatedResponse += data.chunk;
                            
                            // Convert newlines to HTML breaks for proper display
                            const htmlContent = accumulatedResponse.replace(/\n/g, '<br>');
                            
                            // Replace placeholder with accumulated response
                            if (placeholderElement) {
                                if (firstChunk) {
                                    // Replace placeholder with first chunk
                                    placeholderElement.innerHTML = htmlContent;
                                    placeholderElement.removeAttribute('id');
                                    placeholderElement.style.backgroundColor = 'transparent';
                                    placeholderElement.style.padding = '0';
                                    firstChunk = false;
                                } else {
                                    // Update existing content
                                    placeholderElement.innerHTML = htmlContent;
                                }
                            }
                        } else if (data.done) {
                            // Ensure final content is set
                            if (placeholderElement) {
                                let finalContent;
                                if (data.complete_response) {
                                    finalContent = data.complete_response.replace(/\n/g, '<br>');
                                } else {
                                    finalContent = accumulatedResponse.replace(/\n/g, '<br>');
                                }
                                placeholderElement.innerHTML = finalContent;
                            }
                            return;
                        }
                    } catch (e) {
                        // Skip invalid JSON
                    }
                }
            }
        }
    }

    /**
     * Legacy function - kept for backward compatibility
     * @param prompt
     * @returns {Promise<string>}
     */
    async function getResponseFromLeo(prompt) {
        const copilotEndpoint = leantime.appUrl + '/copilot/action/editorTools';

        const response = await fetch(copilotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(prompt)
        });

        // Handle streaming response from Copilot
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let fullResponse = '';
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop(); // Keep incomplete line in buffer

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const data = JSON.parse(line.slice(6));
                        if (data.chunk) {
                            fullResponse += data.chunk;
                        } else if (data.done && data.complete_response) {
                            return data.complete_response.replace(/\n/g, '<br>');
                        }
                    } catch (e) {
                        // Skip invalid JSON
                    }
                }
            }
        }

        return fullResponse.replace(/\n/g, '<br>');
    }

    /* Add a chatgpt icon */

    console.log("Add ui elements");

    editor.ui.registry.addIcon(
        'aiTools',
        '<span class="fancyLink">🪄 LEO AI</span>\n'
    )

    /* Add a button that opens a window */
    editor.ui.registry.addButton('aiTools', {
        text: '<span class="fancyLink"><i class="fa-solid fa-wand-magic-sparkles"></i> Ai Tools</span>',
        tooltip: 'Leo Helper',
        onAction: function () {
            /* Open window */
            openDialog()
        }
    })

    /* Adds a menu item, which can then be included in any menu via the menu/menubar configuration */
    editor.ui.registry.addMenuItem('aiTools', {
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
