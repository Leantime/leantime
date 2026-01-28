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
     * Check if the current selection is in a node that only allows inline content
     * (like detailsSummary). Block commands should not run in these contexts.
     */
    function isInInlineOnlyContext(editor) {
        var state = editor.state;
        var selection = state.selection;
        var $from = selection.$from;

        // Walk up the document tree to check parent nodes
        for (var depth = $from.depth; depth >= 0; depth--) {
            var node = $from.node(depth);
            var nodeType = node.type;

            // Only check for specific nodes that don't allow block content
            if (nodeType.name === 'detailsSummary') {
                return true;
            }
        }

        return false;
    }

    /**
     * Toolbar button definitions
     */
    var toolbarButtons = {
        bold: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path></svg>',
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
            command: function(editor, button) {
                // Don't run in inline-only contexts (like detailsSummary)
                if (isInInlineOnlyContext(editor)) {
                    return;
                }
                showHeadingPopover(editor, button);
            },
            isActive: function(editor) { return editor.isActive('heading'); },
            isDisabled: function(editor) { return isInInlineOnlyContext(editor); }
        },
        quote: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"></path><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3z"></path></svg>',
            title: 'Quote',
            command: function(editor) {
                if (isInInlineOnlyContext(editor)) {
                    return;
                }
                editor.chain().focus().toggleBlockquote().run();
            },
            isActive: function(editor) { return editor.isActive('blockquote'); },
            isDisabled: function(editor) { return isInInlineOnlyContext(editor); }
        },
        bulletList: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="6" x2="20" y2="6"></line><line x1="9" y1="12" x2="20" y2="12"></line><line x1="9" y1="18" x2="20" y2="18"></line><circle cx="4" cy="6" r="1" fill="currentColor"></circle><circle cx="4" cy="12" r="1" fill="currentColor"></circle><circle cx="4" cy="18" r="1" fill="currentColor"></circle></svg>',
            title: 'Bullet List',
            command: function(editor) {
                if (isInInlineOnlyContext(editor)) {
                    return;
                }
                editor.chain().focus().toggleBulletList().run();
            },
            isActive: function(editor) { return editor.isActive('bulletList'); },
            isDisabled: function(editor) { return isInInlineOnlyContext(editor); }
        },
        orderedList: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" y1="6" x2="21" y2="6"></line><line x1="10" y1="12" x2="21" y2="12"></line><line x1="10" y1="18" x2="21" y2="18"></line><path d="M4 6h1v4"></path><path d="M4 10h2"></path><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path></svg>',
            title: 'Numbered List',
            command: function(editor) {
                if (isInInlineOnlyContext(editor)) {
                    return;
                }
                editor.chain().focus().toggleOrderedList().run();
            },
            isActive: function(editor) { return editor.isActive('orderedList'); },
            isDisabled: function(editor) { return isInInlineOnlyContext(editor); }
        },
        taskList: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="6" height="6" rx="1"></rect><path d="M5 11V8h2"></path><line x1="12" y1="8" x2="21" y2="8"></line><rect x="3" y="13" width="6" height="6" rx="1"></rect><path d="M5 16l1.5 1.5L9 14"></path><line x1="12" y1="16" x2="21" y2="16"></line></svg>',
            title: 'Checklist',
            command: function(editor) {
                if (isInInlineOnlyContext(editor)) {
                    return;
                }
                editor.chain().focus().toggleTaskList().run();
            },
            isActive: function(editor) { return editor.isActive('taskList'); },
            isDisabled: function(editor) { return isInInlineOnlyContext(editor); }
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
        },
        superscript: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 19 5-5"/><path d="m9 19-5-5"/><path d="M19 9h-4l3.5-4a1.5 1.5 0 0 0-3-1"/></svg>',
            title: 'Superscript',
            command: function(editor) { editor.chain().focus().toggleSuperscript().run(); },
            isActive: function(editor) { return editor.isActive('superscript'); }
        },
        subscript: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 5 5 5"/><path d="m9 5-5 5"/><path d="M19 21h-4l3.5-4a1.5 1.5 0 0 0-3-1"/></svg>',
            title: 'Subscript',
            command: function(editor) { editor.chain().focus().toggleSubscript().run(); },
            isActive: function(editor) { return editor.isActive('subscript'); }
        },
        alignLeft: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>',
            title: 'Align Left',
            command: function(editor) { editor.chain().focus().setTextAlign('left').run(); },
            isActive: function(editor) { return editor.isActive({ textAlign: 'left' }); }
        },
        alignCenter: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="5" y1="18" x2="19" y2="18"/></svg>',
            title: 'Align Center',
            command: function(editor) { editor.chain().focus().setTextAlign('center').run(); },
            isActive: function(editor) { return editor.isActive({ textAlign: 'center' }); }
        },
        alignRight: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/></svg>',
            title: 'Align Right',
            command: function(editor) { editor.chain().focus().setTextAlign('right').run(); },
            isActive: function(editor) { return editor.isActive({ textAlign: 'right' }); }
        },
        textColor: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16"/><path d="m6 16 6-12 6 12"/><path d="M8 12h8"/></svg>',
            title: 'Text Color',
            command: function(editor, button) {
                showColorPopover(editor, button);
            },
            isActive: function() { return false; }
        },
        highlight: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11-6 6v3h9l3-3"/><path d="m22 12-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4"/></svg>',
            title: 'Highlight',
            command: function(editor, button) {
                showHighlightPopover(editor, button);
            },
            isActive: function(editor) { return editor.isActive('highlight'); }
        },
        fontFamily: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><text x="4" y="17" font-size="14" font-family="serif" fill="currentColor" stroke="none">Aa</text></svg>',
            title: 'Font Family',
            command: function(editor, button) {
                showFontFamilyPopover(editor, button);
            },
            isActive: function() { return false; }
        },
        fontSize: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>',
            title: 'Font Size',
            command: function(editor, button) {
                showFontSizePopover(editor, button);
            },
            isActive: function() { return false; }
        },
        more: {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>',
            title: 'More options',
            command: function(editor, button) {
                showMoreMenu(editor, button);
            },
            isActive: function() { return false; }
        }
    };

    /**
     * Items in the "More" dropdown menu
     */
    var moreMenuItems = [
        { key: 'superscript', label: 'Superscript', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4 19 5-5"/><path d="m9 19-5-5"/><path d="M19 9h-4l3.5-4a1.5 1.5 0 0 0-3-1"/></svg>' },
        { key: 'subscript', label: 'Subscript', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4 5 5 5"/><path d="m9 5-5 5"/><path d="M19 21h-4l3.5-4a1.5 1.5 0 0 0-3-1"/></svg>' },
        { type: 'divider' },
        { key: 'alignLeft', label: 'Align Left', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>' },
        { key: 'alignCenter', label: 'Align Center', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="5" y1="18" x2="19" y2="18"/></svg>' },
        { key: 'alignRight', label: 'Align Right', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/></svg>' },
        { type: 'divider' },
        { key: 'horizontalRule', label: 'Horizontal Line', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/></svg>' },
        { key: 'clearFormatting', label: 'Clear Formatting', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/><line x1="2" y1="2" x2="22" y2="22" stroke-width="2"/></svg>' }
    ];

    /**
     * Close any open more menu
     */
    function closeMoreMenu() {
        var existingMenu = document.querySelector('.tiptap-more-menu');
        if (existingMenu) {
            existingMenu.remove();
        }
    }

    /**
     * Show more options dropdown menu
     */
    function showMoreMenu(editor, button) {
        closeMoreMenu();
        closeColorPopover();
        closeFontPopover();

        var menu = document.createElement('div');
        menu.className = 'tiptap-more-menu';

        var menuContent = '<div class="tiptap-more-menu__list">';
        moreMenuItems.forEach(function(item) {
            if (item.type === 'divider') {
                menuContent += '<div class="tiptap-more-menu__divider"></div>';
            } else {
                var activeClass = '';
                var buttonDef = toolbarButtons[item.key];
                if (buttonDef && buttonDef.isActive && buttonDef.isActive(editor)) {
                    activeClass = ' is-active';
                }
                menuContent += '<button type="button" class="tiptap-more-menu__item' + activeClass + '" data-command="' + item.key + '">' +
                    '<span class="tiptap-more-menu__icon">' + item.icon + '</span>' +
                    '<span class="tiptap-more-menu__label">' + item.label + '</span>' +
                    (item.hasSubmenu ? '<span class="tiptap-more-menu__arrow">›</span>' : '') +
                '</button>';
            }
        });
        menuContent += '</div>';

        menu.innerHTML = menuContent;

        // Position menu relative to button
        var buttonRect = button.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        var viewportHeight = window.innerHeight;

        menu.style.position = 'absolute';
        menu.style.left = (buttonRect.right + scrollLeft - 200) + 'px'; // Align to right edge
        menu.style.zIndex = '10001';

        document.body.appendChild(menu);

        // Get menu dimensions after adding to DOM
        var menuRect = menu.getBoundingClientRect();

        // Check if menu would go off-screen at the bottom
        var spaceBelow = viewportHeight - buttonRect.bottom;
        var spaceAbove = buttonRect.top;

        if (spaceBelow < menuRect.height + 10 && spaceAbove > menuRect.height + 10) {
            // Position above the button
            menu.style.top = (buttonRect.top + scrollTop - menuRect.height - 4) + 'px';
        } else {
            // Position below the button (default)
            menu.style.top = (buttonRect.bottom + scrollTop + 4) + 'px';
        }

        // Ensure menu doesn't go off-screen horizontally
        if (menuRect.left < 10) {
            menu.style.left = '10px';
        }

        // Add click handlers
        menu.querySelectorAll('.tiptap-more-menu__item').forEach(function(menuItem) {
            menuItem.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var commandKey = menuItem.getAttribute('data-command');

                // Special handling for items with submenus
                if (commandKey === 'textColor') {
                    closeMoreMenu();
                    showColorPopover(editor, button);
                    return;
                }
                if (commandKey === 'highlight') {
                    closeMoreMenu();
                    showHighlightPopover(editor, button);
                    return;
                }

                // Special handling for clear formatting
                if (commandKey === 'clearFormatting') {
                    editor.chain().focus().clearNodes().unsetAllMarks().run();
                    closeMoreMenu();
                    return;
                }

                // Special handling for horizontal rule
                if (commandKey === 'horizontalRule') {
                    editor.chain().focus().setHorizontalRule().run();
                    closeMoreMenu();
                    return;
                }

                // Use the button definition for other commands
                var buttonDef = toolbarButtons[commandKey];
                if (buttonDef && buttonDef.command) {
                    buttonDef.command(editor, menuItem);
                    closeMoreMenu();
                }
            });
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', function closeOnClickOutside(e) {
                if (!menu.contains(e.target) && e.target !== button) {
                    closeMoreMenu();
                    document.removeEventListener('click', closeOnClickOutside);
                }
            });
        }, 10);

        // Close on Escape
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape') {
                closeMoreMenu();
                document.removeEventListener('keydown', closeOnEscape);
            }
        });
    }

    /**
     * Toolbar configurations
     */
    var toolbarConfigs = {
        complex: {
            position: 'top',
            buttons: ['bold', 'italic', 'strike', '|', 'fontFamily', 'fontSize', 'textColor', 'highlight', '|', 'heading', 'quote', '|', 'bulletList', 'orderedList', 'taskList', '|', 'link', 'image', '|', 'table', 'code', '|', 'more'],
            expandable: ['table', 'code']
        },
        simple: {
            position: 'bottom',
            buttons: ['bold', 'italic', '|', 'link', 'image', '|', 'bulletList', '|', 'more']
        },
        notes: {
            position: 'top',
            buttons: ['bold', 'italic', 'strike', '|', 'fontFamily', 'fontSize', 'textColor', 'highlight', '|', 'heading', 'quote', '|', 'bulletList', 'orderedList', 'taskList', '|', 'link', 'image', '|', 'table', 'code', '|', 'more']
        }
    };

    /**
     * Close any open color popovers
     */
    function closeColorPopover() {
        var existingPopover = document.querySelector('.tiptap-color-popover');
        if (existingPopover) {
            existingPopover.remove();
        }
    }

    /**
     * Show color picker popover
     */
    function showColorPopover(editor, button) {
        closeColorPopover();

        var colors = [
            { name: 'Default', value: null },
            { name: 'Gray', value: '#6b7280' },
            { name: 'Red', value: '#ef4444' },
            { name: 'Orange', value: '#f97316' },
            { name: 'Yellow', value: '#eab308' },
            { name: 'Green', value: '#22c55e' },
            { name: 'Blue', value: '#3b82f6' },
            { name: 'Purple', value: '#a855f7' },
            { name: 'Pink', value: '#ec4899' }
        ];

        var popover = document.createElement('div');
        popover.className = 'tiptap-color-popover';

        var colorGrid = '<div class="tiptap-color-popover__grid">';
        colors.forEach(function(color) {
            var style = color.value ? 'background-color: ' + color.value : 'background: linear-gradient(135deg, #fff 45%, #ff0000 45%, #ff0000 55%, #fff 55%)';
            colorGrid += '<button type="button" class="tiptap-color-popover__btn" data-color="' + (color.value || '') + '" title="' + color.name + '" style="' + style + '"></button>';
        });
        colorGrid += '</div>';

        popover.innerHTML = colorGrid;

        // Position popover below button
        var buttonRect = button.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        popover.style.position = 'absolute';
        popover.style.top = (buttonRect.bottom + scrollTop + 4) + 'px';
        popover.style.left = (buttonRect.left + scrollLeft) + 'px';
        popover.style.zIndex = '10001';

        document.body.appendChild(popover);

        // Add click handlers to color buttons
        popover.querySelectorAll('.tiptap-color-popover__btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var colorValue = btn.getAttribute('data-color');
                if (colorValue) {
                    editor.chain().focus().setColor(colorValue).run();
                } else {
                    editor.chain().focus().unsetColor().run();
                }
                closeColorPopover();
            });
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', function closeOnClickOutside(e) {
                if (!popover.contains(e.target) && e.target !== button) {
                    closeColorPopover();
                    document.removeEventListener('click', closeOnClickOutside);
                }
            });
        }, 10);

        // Close on Escape
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape') {
                closeColorPopover();
                document.removeEventListener('keydown', closeOnEscape);
            }
        });
    }

    /**
     * Show highlight picker popover
     */
    function showHighlightPopover(editor, button) {
        closeColorPopover();

        var highlightColors = [
            { name: 'Remove', value: null },
            { name: 'Yellow', value: '#fef08a' },
            { name: 'Green', value: '#bbf7d0' },
            { name: 'Blue', value: '#bfdbfe' },
            { name: 'Purple', value: '#e9d5ff' },
            { name: 'Pink', value: '#fbcfe8' },
            { name: 'Orange', value: '#fed7aa' }
        ];

        var popover = document.createElement('div');
        popover.className = 'tiptap-color-popover';

        var colorGrid = '<div class="tiptap-color-popover__grid">';
        highlightColors.forEach(function(color) {
            var style = color.value ? 'background-color: ' + color.value : 'background: linear-gradient(135deg, #fff 45%, #ff0000 45%, #ff0000 55%, #fff 55%)';
            colorGrid += '<button type="button" class="tiptap-color-popover__btn" data-color="' + (color.value || '') + '" title="' + color.name + '" style="' + style + '"></button>';
        });
        colorGrid += '</div>';

        popover.innerHTML = colorGrid;

        // Position popover below button
        var buttonRect = button.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        popover.style.position = 'absolute';
        popover.style.top = (buttonRect.bottom + scrollTop + 4) + 'px';
        popover.style.left = (buttonRect.left + scrollLeft) + 'px';
        popover.style.zIndex = '10001';

        document.body.appendChild(popover);

        // Add click handlers to color buttons
        popover.querySelectorAll('.tiptap-color-popover__btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var colorValue = btn.getAttribute('data-color');
                if (colorValue) {
                    editor.chain().focus().toggleHighlight({ color: colorValue }).run();
                } else {
                    editor.chain().focus().unsetHighlight().run();
                }
                closeColorPopover();
            });
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', function closeOnClickOutside(e) {
                if (!popover.contains(e.target) && e.target !== button) {
                    closeColorPopover();
                    document.removeEventListener('click', closeOnClickOutside);
                }
            });
        }, 10);

        // Close on Escape
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape') {
                closeColorPopover();
                document.removeEventListener('keydown', closeOnEscape);
            }
        });
    }

    /**
     * Close any open font popovers
     */
    function closeFontPopover() {
        var existingPopover = document.querySelector('.tiptap-font-popover');
        if (existingPopover) {
            existingPopover.remove();
        }
        var existingHeadingPopover = document.querySelector('.tiptap-heading-popover');
        if (existingHeadingPopover) {
            existingHeadingPopover.remove();
        }
    }

    /**
     * Show heading level picker popover
     */
    function showHeadingPopover(editor, button) {
        closeFontPopover();

        var headings = [
            { name: 'Paragraph', level: 0 },
            { name: 'Heading 1', level: 1 },
            { name: 'Heading 2', level: 2 },
            { name: 'Heading 3', level: 3 },
            { name: 'Heading 4', level: 4 }
        ];

        var popover = document.createElement('div');
        popover.className = 'tiptap-heading-popover tiptap-font-popover';

        var list = '<div class="tiptap-font-popover__list">';
        headings.forEach(function(h) {
            var isActive = h.level === 0
                ? !editor.isActive('heading')
                : editor.isActive('heading', { level: h.level });
            var activeClass = isActive ? ' is-active' : '';
            var fontSize = h.level === 0 ? '14px' : (20 - h.level * 2) + 'px';
            var fontWeight = h.level === 0 ? 'normal' : 'bold';
            list += '<button type="button" class="tiptap-font-popover__btn' + activeClass + '" data-level="' + h.level + '" style="font-size: ' + fontSize + '; font-weight: ' + fontWeight + ';">' + h.name + '</button>';
        });
        list += '</div>';

        popover.innerHTML = list;

        // Position popover below button
        var buttonRect = button.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        popover.style.position = 'absolute';
        popover.style.top = (buttonRect.bottom + scrollTop + 4) + 'px';
        popover.style.left = (buttonRect.left + scrollLeft) + 'px';
        popover.style.zIndex = '10001';

        document.body.appendChild(popover);

        // Add click handlers
        popover.querySelectorAll('.tiptap-font-popover__btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var level = parseInt(btn.getAttribute('data-level'), 10);
                if (level === 0) {
                    // Convert to paragraph
                    editor.chain().focus().setParagraph().run();
                } else {
                    // Select all content in the current block, unset colors, then apply heading
                    var state = editor.state;
                    var selection = state.selection;
                    var $from = selection.$from;

                    // Get the parent block node
                    var parentPos = $from.start($from.depth);
                    var parentEnd = $from.end($from.depth);

                    // Select the entire block content, unset color marks, then apply heading
                    editor.chain()
                        .focus()
                        .setTextSelection({ from: parentPos, to: parentEnd })
                        .unsetMark('textStyle')
                        .toggleHeading({ level: level })
                        .run();
                }
                closeFontPopover();
            });
        });

        // Close popover when clicking outside
        setTimeout(function() {
            document.addEventListener('click', function closeHandler(e) {
                if (!popover.contains(e.target) && e.target !== button) {
                    closeFontPopover();
                    document.removeEventListener('click', closeHandler);
                }
            });
        }, 0);
    }

    /**
     * Show font family picker popover
     */
    function showFontFamilyPopover(editor, button) {
        closeFontPopover();

        var fonts = [
            { name: 'Default', value: null },
            { name: 'Arial', value: 'Arial, sans-serif' },
            { name: 'Georgia', value: 'Georgia, serif' },
            { name: 'Times New Roman', value: '"Times New Roman", serif' },
            { name: 'Courier New', value: '"Courier New", monospace' },
            { name: 'Verdana', value: 'Verdana, sans-serif' },
            { name: 'Trebuchet MS', value: '"Trebuchet MS", sans-serif' },
            { name: 'Comic Sans MS', value: '"Comic Sans MS", cursive' }
        ];

        var popover = document.createElement('div');
        popover.className = 'tiptap-font-popover';

        var list = '<div class="tiptap-font-popover__list">';
        fonts.forEach(function(font) {
            var style = font.value ? 'font-family: ' + font.value : '';
            list += '<button type="button" class="tiptap-font-popover__btn" data-font="' + (font.value || '') + '" style="' + style + '">' + font.name + '</button>';
        });
        list += '</div>';

        popover.innerHTML = list;

        // Position popover below button
        var buttonRect = button.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        popover.style.position = 'absolute';
        popover.style.top = (buttonRect.bottom + scrollTop + 4) + 'px';
        popover.style.left = (buttonRect.left + scrollLeft) + 'px';
        popover.style.zIndex = '10001';

        document.body.appendChild(popover);

        // Add click handlers
        popover.querySelectorAll('.tiptap-font-popover__btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var fontValue = btn.getAttribute('data-font');
                if (fontValue) {
                    editor.chain().focus().setFontFamily(fontValue).run();
                } else {
                    editor.chain().focus().unsetFontFamily().run();
                }
                closeFontPopover();
            });
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', function closeOnClickOutside(e) {
                if (!popover.contains(e.target) && e.target !== button) {
                    closeFontPopover();
                    document.removeEventListener('click', closeOnClickOutside);
                }
            });
        }, 10);

        // Close on Escape
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape') {
                closeFontPopover();
                document.removeEventListener('keydown', closeOnEscape);
            }
        });
    }

    /**
     * Show font size picker popover
     */
    function showFontSizePopover(editor, button) {
        closeFontPopover();

        var sizes = [
            { name: 'Small', value: '12px' },
            { name: 'Normal', value: null },
            { name: '14px', value: '14px' },
            { name: '16px', value: '16px' },
            { name: '18px', value: '18px' },
            { name: '20px', value: '20px' },
            { name: '24px', value: '24px' },
            { name: '28px', value: '28px' },
            { name: '32px', value: '32px' },
            { name: '36px', value: '36px' },
            { name: '48px', value: '48px' }
        ];

        var popover = document.createElement('div');
        popover.className = 'tiptap-font-popover';

        var list = '<div class="tiptap-font-popover__list">';
        sizes.forEach(function(size) {
            var style = size.value ? 'font-size: ' + size.value : '';
            list += '<button type="button" class="tiptap-font-popover__btn" data-size="' + (size.value || '') + '" style="' + style + '">' + size.name + '</button>';
        });
        list += '</div>';

        popover.innerHTML = list;

        // Position popover below button
        var buttonRect = button.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        popover.style.position = 'absolute';
        popover.style.top = (buttonRect.bottom + scrollTop + 4) + 'px';
        popover.style.left = (buttonRect.left + scrollLeft) + 'px';
        popover.style.zIndex = '10001';

        document.body.appendChild(popover);

        // Add click handlers
        popover.querySelectorAll('.tiptap-font-popover__btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var sizeValue = btn.getAttribute('data-size');
                if (sizeValue) {
                    editor.chain().focus().setFontSize(sizeValue).run();
                } else {
                    editor.chain().focus().unsetFontSize().run();
                }
                closeFontPopover();
            });
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', function closeOnClickOutside(e) {
                if (!popover.contains(e.target) && e.target !== button) {
                    closeFontPopover();
                    document.removeEventListener('click', closeOnClickOutside);
                }
            });
        }, 10);

        // Close on Escape
        document.addEventListener('keydown', function closeOnEscape(e) {
            if (e.key === 'Escape') {
                closeFontPopover();
                document.removeEventListener('keydown', closeOnEscape);
            }
        });
    }

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
                if (buttonDef) {
                    // Update active state
                    if (buttonDef.isActive) {
                        if (buttonDef.isActive(editor)) {
                            button.classList.add('is-active');
                            button.setAttribute('aria-pressed', 'true');
                        } else {
                            button.classList.remove('is-active');
                            button.setAttribute('aria-pressed', 'false');
                        }
                    }
                    // Update disabled state
                    if (buttonDef.isDisabled) {
                        if (buttonDef.isDisabled(editor)) {
                            button.classList.add('is-disabled');
                            button.setAttribute('aria-disabled', 'true');
                        } else {
                            button.classList.remove('is-disabled');
                            button.setAttribute('aria-disabled', 'false');
                        }
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
