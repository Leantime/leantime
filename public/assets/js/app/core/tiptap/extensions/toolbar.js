/**
 * Tiptap Toolbar Extension
 *
 * Creates a configurable toolbar for the Tiptap editor.
 * Supports different configurations for complex (top) and simple (bottom) editors.
 *
 * @module tiptap/extensions/toolbar
 */

(function() {
    'use strict';

    /**
     * Toolbar button definitions
     */
    var toolbarButtons = {
        bold: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path></svg>',
            title: 'Bold (Ctrl+B)',
            command: function(editor) { editor.chain().focus().toggleBold().run(); },
            isActive: function(editor) { return editor.isActive('bold'); }
        },
        italic: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="4" x2="10" y2="4"></line><line x1="14" y1="20" x2="5" y2="20"></line><line x1="15" y1="4" x2="9" y2="20"></line></svg>',
            title: 'Italic (Ctrl+I)',
            command: function(editor) { editor.chain().focus().toggleItalic().run(); },
            isActive: function(editor) { return editor.isActive('italic'); }
        },
        strike: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><path d="M16 6C16 6 14.5 4 12 4C9.5 4 7 6 7 8.5C7 11 9 12 12 12"></path><path d="M8 18C8 18 9.5 20 12 20C14.5 20 17 18 17 15.5C17 13 15 12 12 12"></path></svg>',
            title: 'Strikethrough',
            command: function(editor) { editor.chain().focus().toggleStrike().run(); },
            isActive: function(editor) { return editor.isActive('strike'); }
        },
        heading: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v16"></path><path d="M18 4v16"></path><path d="M6 12h12"></path></svg>',
            title: 'Heading',
            command: function(editor) { editor.chain().focus().toggleHeading({ level: 2 }).run(); },
            isActive: function(editor) { return editor.isActive('heading'); }
        },
        quote: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"></path><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3z"></path></svg>',
            title: 'Quote',
            command: function(editor) { editor.chain().focus().toggleBlockquote().run(); },
            isActive: function(editor) { return editor.isActive('blockquote'); }
        },
        bulletList: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="6" x2="20" y2="6"></line><line x1="9" y1="12" x2="20" y2="12"></line><line x1="9" y1="18" x2="20" y2="18"></line><circle cx="4" cy="6" r="1" fill="currentColor"></circle><circle cx="4" cy="12" r="1" fill="currentColor"></circle><circle cx="4" cy="18" r="1" fill="currentColor"></circle></svg>',
            title: 'Bullet List',
            command: function(editor) { editor.chain().focus().toggleBulletList().run(); },
            isActive: function(editor) { return editor.isActive('bulletList'); }
        },
        orderedList: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" y1="6" x2="21" y2="6"></line><line x1="10" y1="12" x2="21" y2="12"></line><line x1="10" y1="18" x2="21" y2="18"></line><path d="M4 6h1v4"></path><path d="M4 10h2"></path><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path></svg>',
            title: 'Numbered List',
            command: function(editor) { editor.chain().focus().toggleOrderedList().run(); },
            isActive: function(editor) { return editor.isActive('orderedList'); }
        },
        taskList: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="6" height="6" rx="1"></rect><path d="M5 11V8h2"></path><line x1="12" y1="8" x2="21" y2="8"></line><rect x="3" y="13" width="6" height="6" rx="1"></rect><path d="M5 16l1.5 1.5L9 14"></path><line x1="12" y1="16" x2="21" y2="16"></line></svg>',
            title: 'Checklist',
            command: function(editor) { editor.chain().focus().toggleTaskList().run(); },
            isActive: function(editor) { return editor.isActive('taskList'); }
        },
        link: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>',
            title: 'Link (Ctrl+K)',
            command: function(editor) {
                // Check if we're already on a link (to unlink)
                if (editor.isActive('link')) {
                    editor.chain().focus().unsetLink().run();
                    return;
                }

                // Check if there's selected text
                var selection = editor.state.selection;
                var hasSelection = !selection.empty;

                // Use setTimeout to ensure prompt appears above modal
                setTimeout(function() {
                    var url = window.prompt('Enter URL:', 'https://');
                    if (url === null) return; // User cancelled

                    if (url && url !== 'https://') {
                        // Ensure URL has protocol
                        if (!/^https?:\/\//i.test(url) && !/^mailto:/i.test(url)) {
                            url = 'https://' + url;
                        }

                        if (hasSelection) {
                            // Apply link to selected text
                            editor.chain().focus().setLink({ href: url }).run();
                        } else {
                            // No selection - prompt for link text
                            var linkText = window.prompt('Enter link text:', url);
                            if (linkText) {
                                editor.chain().focus()
                                    .insertContent('<a href="' + url + '">' + linkText + '</a>')
                                    .run();
                            }
                        }
                    }
                }, 10);
            },
            isActive: function(editor) { return editor.isActive('link'); }
        },
        image: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
            title: 'Image',
            command: function(editor, button) {
                // Show image options popover
                showImagePopover(editor, button);
            },
            isActive: function() { return false; }
        },
        table: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>',
            title: 'Table',
            command: function(editor) {
                editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
            },
            isActive: function(editor) { return editor.isActive('table'); }
        },
        code: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
            title: 'Code Block',
            command: function(editor) { editor.chain().focus().toggleCodeBlock().run(); },
            isActive: function(editor) { return editor.isActive('codeBlock'); }
        },
        undo: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"></path><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"></path></svg>',
            title: 'Undo (Ctrl+Z)',
            command: function(editor) { editor.chain().focus().undo().run(); },
            isActive: function() { return false; }
        },
        redo: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"></path><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3L21 13"></path></svg>',
            title: 'Redo (Ctrl+Shift+Z)',
            command: function(editor) { editor.chain().focus().redo().run(); },
            isActive: function() { return false; }
        }
    };

    /**
     * Toolbar configurations
     */
    var toolbarConfigs = {
        complex: {
            position: 'top',
            buttons: ['bold', 'italic', 'strike', '|', 'heading', 'quote', '|', 'bulletList', 'orderedList', 'taskList', '|', 'link', 'image', '|', 'table', 'code'],
            expandable: ['table', 'code']
        },
        simple: {
            position: 'bottom',
            buttons: ['bold', 'italic', '|', 'link', 'image', '|', 'bulletList']
        },
        notes: {
            position: 'top',
            buttons: ['bold', 'italic', 'strike', '|', 'heading', 'quote', '|', 'bulletList', 'orderedList', 'taskList', '|', 'link', 'image', '|', 'table', 'code']
        }
    };

    /**
     * Close any open image popovers
     */
    function closeImagePopover() {
        var existingPopover = document.querySelector('.tiptap-image-popover');
        if (existingPopover) {
            existingPopover.remove();
        }
    }

    /**
     * Show image upload/URL popover
     */
    function showImagePopover(editor, button) {
        // Close any existing popover
        closeImagePopover();

        // Create popover
        var popover = document.createElement('div');
        popover.className = 'tiptap-image-popover';

        // Get module info from the page context
        var moduleId = '';
        var module = 'ticket';

        // Try to get ticket ID from URL or form
        var ticketIdInput = document.querySelector('input[name="id"], input[name="itemId"], input[name="ticketId"]');
        if (ticketIdInput && ticketIdInput.value) {
            moduleId = ticketIdInput.value;
        }

        // Check if we're in a wiki/doc context
        if (window.location.href.indexOf('/wiki/') > -1 || window.location.href.indexOf('/docs/') > -1) {
            module = 'wiki';
            var wikiIdInput = document.querySelector('input[name="id"]');
            if (wikiIdInput) moduleId = wikiIdInput.value;
        }

        // Check project context
        if (window.location.href.indexOf('/projects/') > -1) {
            module = 'project';
        }

        // Fallback to current project
        if (!moduleId && window.leantime && window.leantime.currentProject) {
            moduleId = window.leantime.currentProject;
            module = 'project';
        }

        popover.innerHTML =
            '<div class="tiptap-image-popover__content">' +
                '<div class="tiptap-image-popover__option tiptap-image-popover__upload">' +
                    '<input type="file" accept="image/*" class="tiptap-image-popover__file-input" />' +
                    '<span class="tiptap-image-popover__icon">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>' +
                    '</span>' +
                    '<span>Upload image</span>' +
                '</div>' +
                '<div class="tiptap-image-popover__divider"></div>' +
                '<div class="tiptap-image-popover__url-section">' +
                    '<input type="text" placeholder="Or paste image URL..." class="tiptap-image-popover__url-input" />' +
                    '<button type="button" class="tiptap-image-popover__url-btn">Insert</button>' +
                '</div>' +
            '</div>';

        // Position popover below button
        var buttonRect = button.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        popover.style.position = 'absolute';
        popover.style.top = (buttonRect.bottom + scrollTop + 4) + 'px';
        popover.style.left = (buttonRect.left + scrollLeft) + 'px';
        popover.style.zIndex = '10001';

        document.body.appendChild(popover);

        // File input handler
        var fileInput = popover.querySelector('.tiptap-image-popover__file-input');
        var uploadOption = popover.querySelector('.tiptap-image-popover__upload');

        uploadOption.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;

            // Show loading state
            uploadOption.innerHTML = '<span class="tiptap-image-popover__loading">Uploading...</span>';

            // Upload to Leantime API
            var formData = new FormData();
            formData.append('file', file);

            var uploadUrl = leantime.appUrl + '/api/files';
            if (module && moduleId) {
                uploadUrl += '?module=' + module + '&moduleId=' + moduleId;
            }

            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Upload failed');
                return response.json();
            })
            .then(function(data) {
                // Build image URL from response
                var imageUrl = leantime.appUrl + '/files/get?module=' + data.module +
                    '&encName=' + data.encName +
                    '&ext=' + data.extension +
                    '&realName=' + data.realName;

                // Insert image into editor
                editor.chain().focus().setImage({ src: imageUrl, alt: data.realName }).run();
                closeImagePopover();
            })
            .catch(function(err) {
                console.error('Image upload failed:', err);
                uploadOption.innerHTML = '<span style="color: var(--error-color)">Upload failed. Try again.</span>';
                setTimeout(function() {
                    closeImagePopover();
                }, 2000);
            });
        });

        // URL input handler
        var urlInput = popover.querySelector('.tiptap-image-popover__url-input');
        var urlBtn = popover.querySelector('.tiptap-image-popover__url-btn');

        urlBtn.addEventListener('click', function() {
            var url = urlInput.value.trim();
            if (url) {
                editor.chain().focus().setImage({ src: url }).run();
                closeImagePopover();
            }
        });

        urlInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                urlBtn.click();
            }
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', function closeOnClickOutside(e) {
                if (!popover.contains(e.target) && e.target !== button) {
                    closeImagePopover();
                    document.removeEventListener('click', closeOnClickOutside);
                }
            });
        }, 10);

        // Close on Escape
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape') {
                closeImagePopover();
                document.removeEventListener('keydown', closeOnEscape);
            }
        });

        // Focus URL input
        setTimeout(function() {
            urlInput.focus();
        }, 50);
    }

    /**
     * Create a toolbar for the editor
     */
    function createToolbar(editor, config) {
        var toolbarConfig = toolbarConfigs[config] || toolbarConfigs.complex;

        // Create toolbar container
        var toolbar = document.createElement('div');
        toolbar.className = 'tiptap-toolbar tiptap-toolbar--' + toolbarConfig.position;
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Text formatting');

        // Create button group
        var buttonGroup = document.createElement('div');
        buttonGroup.className = 'tiptap-toolbar__group';

        // Add buttons
        toolbarConfig.buttons.forEach(function(buttonName) {
            if (buttonName === '|') {
                // Separator
                var separator = document.createElement('div');
                separator.className = 'tiptap-toolbar__separator';
                buttonGroup.appendChild(separator);
                return;
            }

            var buttonDef = toolbarButtons[buttonName];
            if (!buttonDef) return;

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'tiptap-toolbar__button';
            button.setAttribute('data-command', buttonName);
            button.setAttribute('title', buttonDef.title);
            button.setAttribute('aria-label', buttonDef.title);
            button.innerHTML = buttonDef.icon;

            // Click handler
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                buttonDef.command(editor, button);
            });

            buttonGroup.appendChild(button);
        });

        toolbar.appendChild(buttonGroup);

        // Add dynamically registered plugin buttons
        if (window.leantime && window.leantime.tiptapController) {
            var registeredButtons = window.leantime.tiptapController.getToolbarButtons();
            if (registeredButtons && registeredButtons.size > 0) {
                // Add separator before plugin buttons
                var pluginSeparator = document.createElement('div');
                pluginSeparator.className = 'tiptap-toolbar__separator';
                buttonGroup.appendChild(pluginSeparator);

                registeredButtons.forEach(function(buttonConfig, name) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'tiptap-toolbar__button tiptap-toolbar__button--plugin';
                    button.setAttribute('data-command', name);
                    button.setAttribute('title', buttonConfig.title || buttonConfig.label || name);
                    button.setAttribute('aria-label', buttonConfig.title || buttonConfig.label || name);
                    button.innerHTML = buttonConfig.icon || '<span>' + (buttonConfig.label || name) + '</span>';

                    // Click handler
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (buttonConfig.action) {
                            buttonConfig.action(editor, button);
                        } else if (buttonConfig.command) {
                            buttonConfig.command(editor, button);
                        }
                    });

                    buttonGroup.appendChild(button);
                });
            }
        }

        // Update button states on editor changes
        var updateButtonStates = function() {
            var buttons = toolbar.querySelectorAll('.tiptap-toolbar__button');
            buttons.forEach(function(button) {
                var commandName = button.getAttribute('data-command');
                var buttonDef = toolbarButtons[commandName];
                if (buttonDef && buttonDef.isActive) {
                    if (buttonDef.isActive(editor)) {
                        button.classList.add('is-active');
                        button.setAttribute('aria-pressed', 'true');
                    } else {
                        button.classList.remove('is-active');
                        button.setAttribute('aria-pressed', 'false');
                    }
                }
            });
        };

        // Listen for editor updates
        editor.on('selectionUpdate', updateButtonStates);
        editor.on('transaction', updateButtonStates);

        // Initial state update
        setTimeout(updateButtonStates, 0);

        return {
            element: toolbar,
            position: toolbarConfig.position,
            update: updateButtonStates,
            destroy: function() {
                editor.off('selectionUpdate', updateButtonStates);
                editor.off('transaction', updateButtonStates);
                if (toolbar.parentNode) {
                    toolbar.parentNode.removeChild(toolbar);
                }
            }
        };
    }

    /**
     * Attach toolbar to editor wrapper
     */
    function attachToolbar(editorWrapper, toolbar) {
        var wrapper = editorWrapper.element.closest('.tiptap-wrapper');
        if (!wrapper) {
            wrapper = editorWrapper.element.parentElement;
        }

        if (toolbar.position === 'top') {
            wrapper.insertBefore(toolbar.element, wrapper.firstChild);
        } else {
            wrapper.appendChild(toolbar.element);
        }
    }

    // Export
    window.leantime = window.leantime || {};
    window.leantime.tiptapToolbar = {
        create: createToolbar,
        attach: attachToolbar,
        buttons: toolbarButtons,
        configs: toolbarConfigs,

        // Allow registering custom buttons
        registerButton: function(name, definition) {
            toolbarButtons[name] = definition;
        },

        // Allow customizing configs
        setConfig: function(name, config) {
            toolbarConfigs[name] = config;
        }
    };

    console.log('[Tiptap] Toolbar extension loaded');
})();
