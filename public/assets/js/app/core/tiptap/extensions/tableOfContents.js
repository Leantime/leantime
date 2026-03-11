/**
 * Table of Contents Extension for Tiptap
 *
 * Generates a table of contents from document headings.
 * Custom implementation for navigation within documents.
 *
 * @module tiptap/extensions/tableOfContents
 */

import { Node, mergeAttributes, Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';

// Plugin key for TOC state
var tocPluginKey = new PluginKey('table-of-contents');

/**
 * Generate a slug from text
 */
function slugify(text) {
    return (text || '')
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .substring(0, 50);
}

/**
 * Extract headings from the document
 */
function extractHeadings(doc) {
    var headings = [];
    var slugCounts = {};

    doc.descendants(function(node, pos) {
        if (node.type.name === 'heading') {
            var text = node.textContent;
            var level = node.attrs.level || 1;
            var baseSlug = slugify(text);

            // Handle duplicate slugs
            if (slugCounts[baseSlug] === undefined) {
                slugCounts[baseSlug] = 0;
            } else {
                slugCounts[baseSlug]++;
            }

            var slug = slugCounts[baseSlug] === 0 ? baseSlug : baseSlug + '-' + slugCounts[baseSlug];

            headings.push({
                id: slug,
                text: text,
                level: level,
                pos: pos,
            });
        }
    });

    return headings;
}

/**
 * Table of Contents Node - Rendered TOC block
 */
var TableOfContents = Node.create({
    name: 'tableOfContents',
    group: 'block',
    atom: true,
    draggable: true,

    addAttributes: function() {
        return {
            // Maximum depth of headings to include (1-6)
            maxDepth: {
                default: 3,
            },
            // Whether to include numbering
            numbered: {
                default: false,
            },
        };
    },

    parseHTML: function() {
        return [
            { tag: 'div[data-table-of-contents]' },
            { tag: 'nav.tiptap-toc' },
        ];
    },

    renderHTML: function(props) {
        return [
            'nav',
            mergeAttributes({
                class: 'tiptap-toc',
                'data-table-of-contents': '',
                'data-max-depth': props.node.attrs.maxDepth,
                'data-numbered': props.node.attrs.numbered ? 'true' : 'false',
            }, props.HTMLAttributes),
            ['div', { class: 'tiptap-toc__content' }, 'Table of Contents'],
        ];
    },

    addNodeView: function() {
        return function(props) {
            var node = props.node;
            var editor = props.editor;
            var getPos = props.getPos;

            var container = document.createElement('nav');
            container.className = 'tiptap-toc';
            container.setAttribute('data-table-of-contents', '');

            // Header
            var header = document.createElement('div');
            header.className = 'tiptap-toc__header';

            var title = document.createElement('span');
            title.className = 'tiptap-toc__title';
            title.textContent = 'Table of Contents';
            header.appendChild(title);

            // Settings button
            var settingsBtn = document.createElement('button');
            settingsBtn.type = 'button';
            settingsBtn.className = 'tiptap-toc__settings-btn';
            settingsBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>';
            settingsBtn.title = 'TOC Settings';
            header.appendChild(settingsBtn);

            // Delete button
            var deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'tiptap-toc__delete-btn';
            deleteBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
            deleteBtn.title = 'Remove table of contents';
            header.appendChild(deleteBtn);

            container.appendChild(header);

            // Content area
            var content = document.createElement('div');
            content.className = 'tiptap-toc__content';
            container.appendChild(content);

            // Settings panel (hidden by default)
            var settingsPanel = document.createElement('div');
            settingsPanel.className = 'tiptap-toc__settings-panel';
            settingsPanel.style.display = 'none';
            settingsPanel.innerHTML =
                '<label class="tiptap-toc__setting">' +
                    '<span>Max depth:</span>' +
                    '<select class="tiptap-toc__depth-select">' +
                        '<option value="1">H1 only</option>' +
                        '<option value="2">H1-H2</option>' +
                        '<option value="3" selected>H1-H3</option>' +
                        '<option value="4">H1-H4</option>' +
                        '<option value="5">H1-H5</option>' +
                        '<option value="6">All headings</option>' +
                    '</select>' +
                '</label>' +
                '<label class="tiptap-toc__setting">' +
                    '<input type="checkbox" class="tiptap-toc__numbered-check"> ' +
                    '<span>Show numbers</span>' +
                '</label>';
            container.appendChild(settingsPanel);

            var depthSelect = settingsPanel.querySelector('.tiptap-toc__depth-select');
            var numberedCheck = settingsPanel.querySelector('.tiptap-toc__numbered-check');

            // Set initial values
            depthSelect.value = node.attrs.maxDepth;
            numberedCheck.checked = node.attrs.numbered;

            /**
             * Render the TOC
             */
            function renderTOC() {
                var headings = extractHeadings(editor.state.doc);
                var maxDepth = node.attrs.maxDepth;
                var numbered = node.attrs.numbered;

                // Filter by max depth
                headings = headings.filter(function(h) {
                    return h.level <= maxDepth;
                });

                if (headings.length === 0) {
                    content.innerHTML = '<div class="tiptap-toc__empty">No headings found</div>';
                    return;
                }

                var list = document.createElement('ul');
                list.className = 'tiptap-toc__list';

                // Number counters for each level
                var counters = [0, 0, 0, 0, 0, 0];

                headings.forEach(function(heading, index) {
                    var item = document.createElement('li');
                    item.className = 'tiptap-toc__item tiptap-toc__item--level-' + heading.level;

                    var link = document.createElement('a');
                    link.className = 'tiptap-toc__link';
                    link.href = '#' + heading.id;

                    if (numbered) {
                        // Reset lower level counters and increment current
                        for (var i = heading.level; i < 6; i++) {
                            counters[i] = 0;
                        }
                        counters[heading.level - 1]++;

                        // Build number string
                        var numberParts = [];
                        for (var j = 0; j < heading.level; j++) {
                            if (counters[j] > 0) {
                                numberParts.push(counters[j]);
                            }
                        }

                        var numberSpan = document.createElement('span');
                        numberSpan.className = 'tiptap-toc__number';
                        numberSpan.textContent = numberParts.join('.') + '.';
                        link.appendChild(numberSpan);
                    }

                    var textSpan = document.createElement('span');
                    textSpan.className = 'tiptap-toc__text';
                    textSpan.textContent = heading.text;
                    link.appendChild(textSpan);

                    // Click to scroll
                    link.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Find the heading in the document and scroll to it
                        var editorElement = editor.view.dom;
                        var headingElements = editorElement.querySelectorAll('h1, h2, h3, h4, h5, h6');

                        headingElements.forEach(function(el) {
                            if (slugify(el.textContent) === heading.id.split('-').slice(0, -1).join('-') ||
                                slugify(el.textContent) === heading.id) {
                                el.scrollIntoView({ behavior: 'smooth', block: 'start' });

                                // Briefly highlight
                                el.classList.add('tiptap-toc__highlight');
                                setTimeout(function() {
                                    el.classList.remove('tiptap-toc__highlight');
                                }, 2000);
                            }
                        });

                        // Also try to focus the editor at that position
                        editor.commands.focus();
                        try {
                            editor.commands.setTextSelection(heading.pos);
                        } catch (e) {
                            // Position might have changed
                        }
                    });

                    item.appendChild(link);
                    list.appendChild(item);
                });

                content.innerHTML = '';
                content.appendChild(list);
            }

            // Initial render
            renderTOC();

            // Settings button handler
            settingsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                settingsPanel.style.display = settingsPanel.style.display === 'none' ? 'block' : 'none';
            });

            // Depth change handler
            depthSelect.addEventListener('change', function() {
                if (typeof getPos === 'function') {
                    var pos = getPos();
                    editor.chain().focus().command(function(cmdProps) {
                        cmdProps.tr.setNodeMarkup(pos, undefined, {
                            maxDepth: parseInt(depthSelect.value, 10),
                            numbered: numberedCheck.checked,
                        });
                        return true;
                    }).run();
                }
            });

            // Numbered change handler
            numberedCheck.addEventListener('change', function() {
                if (typeof getPos === 'function') {
                    var pos = getPos();
                    editor.chain().focus().command(function(cmdProps) {
                        cmdProps.tr.setNodeMarkup(pos, undefined, {
                            maxDepth: parseInt(depthSelect.value, 10),
                            numbered: numberedCheck.checked,
                        });
                        return true;
                    }).run();
                }
            });

            // Delete button handler
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof getPos === 'function') {
                    var pos = getPos();
                    editor.chain().focus().command(function(cmdProps) {
                        cmdProps.tr.delete(pos, pos + node.nodeSize);
                        return true;
                    }).run();
                }
            });

            return {
                dom: container,
                update: function(updatedNode) {
                    if (updatedNode.type.name !== 'tableOfContents') {
                        return false;
                    }

                    node = updatedNode;
                    depthSelect.value = node.attrs.maxDepth;
                    numberedCheck.checked = node.attrs.numbered;
                    renderTOC();
                    return true;
                },
                stopEvent: function(event) {
                    return event.target === settingsBtn ||
                           event.target === deleteBtn ||
                           event.target === depthSelect ||
                           event.target === numberedCheck ||
                           settingsPanel.contains(event.target) ||
                           event.target.closest('.tiptap-toc__link');
                },
            };
        };
    },

    addCommands: function() {
        var self = this;
        return {
            setTableOfContents: function(options) {
                options = options || {};
                return function(props) {
                    return props.commands.insertContent({
                        type: self.name,
                        attrs: {
                            maxDepth: options.maxDepth || 3,
                            numbered: options.numbered || false,
                        },
                    });
                };
            },
        };
    },

    addKeyboardShortcuts: function() {
        return {
            'Mod-Alt-t': function() {
                return this.editor.commands.setTableOfContents();
            },
        };
    },
});

/**
 * TOC Tracking Extension - Updates TOC when headings change
 *
 * This is a separate extension that can be used independently
 * to track headings for external TOC rendering (e.g., sidebar)
 */
var TOCTracker = Extension.create({
    name: 'tocTracker',

    addStorage: function() {
        return {
            headings: [],
        };
    },

    addProseMirrorPlugins: function() {
        var extension = this;

        return [
            new Plugin({
                key: tocPluginKey,
                view: function(editorView) {
                    // Initial extraction
                    var headings = extractHeadings(editorView.state.doc);
                    extension.storage.headings = headings;

                    // Emit initial event
                    editorView.dom.dispatchEvent(new CustomEvent('toc:update', {
                        detail: { headings: headings },
                        bubbles: true,
                    }));

                    return {
                        update: function(view, prevState) {
                            // Only update if document changed
                            if (view.state.doc.eq(prevState.doc)) {
                                return;
                            }

                            var newHeadings = extractHeadings(view.state.doc);

                            // Check if headings actually changed
                            var changed = newHeadings.length !== extension.storage.headings.length ||
                                newHeadings.some(function(h, i) {
                                    var old = extension.storage.headings[i];
                                    return !old || h.text !== old.text || h.level !== old.level;
                                });

                            if (changed) {
                                extension.storage.headings = newHeadings;

                                view.dom.dispatchEvent(new CustomEvent('toc:update', {
                                    detail: { headings: newHeadings },
                                    bubbles: true,
                                }));
                            }
                        },
                    };
                },
            }),
        ];
    },
});

/**
 * Create the Table of Contents extension bundle
 */
function createTableOfContentsExtension(options) {
    options = options || {};
    var extensions = [TableOfContents];

    // Optionally include the tracker for external TOC rendering
    if (options.enableTracker !== false) {
        extensions.push(TOCTracker);
    }

    return extensions;
}

/**
 * Get current headings from the editor
 */
function getHeadings(editor) {
    return extractHeadings(editor.state.doc);
}

export { createTableOfContentsExtension, TableOfContents, TOCTracker, getHeadings, extractHeadings, slugify };
