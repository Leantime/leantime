/**
 * Tiptap Slash Commands Extension for Leantime
 *
 * Provides Notion-style "/" commands for quick content insertion
 */

const { Extension } = require('@tiptap/core');
const { PluginKey, Plugin } = require('@tiptap/pm/state');
const Suggestion = require('@tiptap/suggestion').default;

/**
 * Default slash commands available in the editor
 */
var defaultCommands = [
    // Sorted alphabetically by label
    {
        name: 'columns2',
        label: '2 Columns',
        description: 'Create two-column layout',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="3" width="7" height="18" rx="1"/></svg>',
        command: function(editor) {
            editor.chain().focus().setColumns(2).run();
        }
    },
    {
        name: 'columns3',
        label: '3 Columns',
        description: 'Create three-column layout',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="5" height="18" rx="1"/><rect x="9.5" y="3" width="5" height="18" rx="1"/><rect x="17" y="3" width="5" height="18" rx="1"/></svg>',
        command: function(editor) {
            editor.chain().focus().setColumns(3).run();
        }
    },
    {
        name: 'columns4',
        label: '4 Columns',
        description: 'Create four-column layout',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="4" height="18" rx="1"/><rect x="7" y="3" width="4" height="18" rx="1"/><rect x="13" y="3" width="4" height="18" rx="1"/><rect x="19" y="3" width="4" height="18" rx="1"/></svg>',
        command: function(editor) {
            editor.chain().focus().setColumns(4).run();
        }
    },
    {
        name: 'sidebarLeft',
        label: 'Sidebar Left',
        description: 'Narrow left, wide right layout',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="11" y="3" width="10" height="18" rx="1"/></svg>',
        command: function(editor) {
            editor.chain().focus().setColumnLayout(2, 'sidebar-left').run();
        }
    },
    {
        name: 'sidebarRight',
        label: 'Sidebar Right',
        description: 'Wide left, narrow right layout',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="10" height="18" rx="1"/><rect x="16" y="3" width="5" height="18" rx="1"/></svg>',
        command: function(editor) {
            editor.chain().focus().setColumnLayout(2, 'sidebar-right').run();
        }
    },
    {
        name: 'sidebarBoth',
        label: 'Sidebar Both',
        description: 'Sidebars on both sides of content',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="4" height="18" rx="1"/><rect x="8" y="3" width="8" height="18" rx="1"/><rect x="18" y="3" width="4" height="18" rx="1"/></svg>',
        command: function(editor) {
            editor.chain().focus().setColumnLayout(3, 'sidebar-both').run();
        }
    },
    {
        name: 'bulletList',
        label: 'Bullet List',
        description: 'Create a simple bullet list',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleBulletList().run();
        }
    },
    {
        name: 'codeBlock',
        label: 'Code Block',
        description: 'Add a code snippet',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleCodeBlock().run();
        }
    },
    {
        name: 'details',
        label: 'Collapsible',
        description: 'Add collapsible section',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 9l7 7 7-7"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>',
        command: function(editor) {
            editor.chain().focus().setDetails().run();
        }
    },
    {
        name: 'mermaid',
        label: 'Diagram',
        description: 'Insert a Mermaid diagram',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="8" y="14" width="7" height="7" rx="1"/><line x1="6.5" y1="10" x2="6.5" y2="14"/><line x1="6.5" y1="14" x2="11.5" y2="14"/><line x1="17.5" y1="10" x2="17.5" y2="14"/><line x1="17.5" y1="14" x2="11.5" y2="14"/></svg>',
        command: function(editor) {
            if (window.leantime && window.leantime.tiptapMermaid) {
                window.leantime.tiptapMermaid.showMermaidDialog(editor);
            } else {
                editor.chain().focus().setMermaid().run();
            }
        }
    },
    {
        name: 'horizontalRule',
        label: 'Divider',
        description: 'Add a horizontal divider',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/></svg>',
        command: function(editor) {
            editor.chain().focus().setHorizontalRule().run();
        }
    },
    {
        name: 'embed',
        label: 'Embed',
        description: 'Embed video, docs, or other content',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        command: function(editor) {
            if (window.leantime && window.leantime.tiptapEmbed) {
                window.leantime.tiptapEmbed.showDialog(editor);
            }
        }
    },
    {
        name: 'emoji',
        label: 'Emoji',
        description: 'Insert an emoji',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
        command: function(editor) {
            if (window.leantime && window.leantime.tiptapEmoji) {
                window.leantime.tiptapEmoji.showEmojiPickerDialog(editor);
            }
        }
    },
    {
        name: 'heading1',
        label: 'Heading 1',
        description: 'Large section heading',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="m17 12 3-2v8"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleHeading({ level: 1 }).run();
        }
    },
    {
        name: 'heading2',
        label: 'Heading 2',
        description: 'Medium section heading',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="M21 18h-4c0-4 4-3 4-6 0-1.5-2-2.5-4-1"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleHeading({ level: 2 }).run();
        }
    },
    {
        name: 'heading3',
        label: 'Heading 3',
        description: 'Small section heading',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="M17.5 10.5c1.7-1 3.5 0 3.5 1.5a2 2 0 0 1-2 2"/><path d="M17 17.5c2 1.5 4 .3 4-1.5a2 2 0 0 0-2-2"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleHeading({ level: 3 }).run();
        }
    },
    {
        name: 'image',
        label: 'Image',
        description: 'Upload or embed an image',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
        command: function(editor) {
            // Trigger the image button in toolbar if available
            var toolbar = editor.view.dom.closest('.tiptap-wrapper');
            if (toolbar) {
                var imageBtn = toolbar.querySelector('[data-command="image"]');
                if (imageBtn) {
                    imageBtn.click();
                    return;
                }
            }
            // Fallback to prompt
            var url = window.prompt('Enter image URL:');
            if (url) {
                editor.chain().focus().setImage({ src: url }).run();
            }
        }
    },
    {
        name: 'mathInline',
        label: 'Inline Math',
        description: 'Insert inline LaTeX formula',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><text x="6" y="17" font-size="14" fill="currentColor" stroke="none">x²</text></svg>',
        command: function(editor) {
            var latex = window.prompt('Enter LaTeX formula:', 'x^2');
            if (latex) {
                editor.chain().focus().setMathInline({ latex: latex }).run();
            }
        }
    },
    {
        name: 'math',
        label: 'Math Equation',
        description: 'Insert a LaTeX math block',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4l6-16h4"/><path d="M4 12h6"/></svg>',
        command: function(editor) {
            if (window.leantime && window.leantime.tiptapMath) {
                window.leantime.tiptapMath.showMathDialog(editor, '', true);
            } else {
                editor.chain().focus().setMathBlock().run();
            }
        }
    },
    {
        name: 'numberedList',
        label: 'Numbered List',
        description: 'Create a numbered list',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleOrderedList().run();
        }
    },
    {
        name: 'blockquote',
        label: 'Quote',
        description: 'Add a blockquote',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3z"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleBlockquote().run();
        }
    },
    {
        name: 'table',
        label: 'Table',
        description: 'Insert a table',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>',
        command: function(editor) {
            editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
        }
    },
    {
        name: 'toc',
        label: 'Table of Contents',
        description: 'Insert auto-generated TOC',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="5" y1="10" x2="21" y2="10"/><line x1="5" y1="14" x2="21" y2="14"/><line x1="5" y1="18" x2="21" y2="18"/><circle cx="3" cy="10" r="1" fill="currentColor"/><circle cx="3" cy="14" r="1" fill="currentColor"/><circle cx="3" cy="18" r="1" fill="currentColor"/></svg>',
        command: function(editor) {
            editor.chain().focus().setTableOfContents().run();
        }
    },
    {
        name: 'taskList',
        label: 'Task List',
        description: 'Create a checklist with tasks',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="6" height="6" rx="1"/><path d="m3 17 2 2 4-4"/><line x1="13" y1="6" x2="21" y2="6"/><line x1="13" y1="12" x2="21" y2="12"/><line x1="13" y1="18" x2="21" y2="18"/></svg>',
        command: function(editor) {
            editor.chain().focus().toggleTaskList().run();
        }
    },
    {
        name: 'template',
        label: 'Template',
        description: 'Insert a document template',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>',
        command: function(editor) {
            showTemplatePicker(editor);
        }
    }
];

/**
 * Template picker popup
 */
var templateCache = null;

function fetchTemplates() {
    return new Promise(function(resolve, reject) {
        if (templateCache) {
            resolve(templateCache);
            return;
        }

        fetch(leantime.appUrl + '/wiki/templates', {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to fetch templates');
            return response.json();
        })
        .then(function(data) {
            templateCache = data;
            resolve(data);
        })
        .catch(function(error) {
            console.error('[Templates] Error:', error);
            resolve([]);
        });
    });
}

function showTemplatePicker(editor) {
    // Close any existing picker
    var existing = document.querySelector('.tiptap-template-picker');
    if (existing) {
        existing.remove();
    }

    // Create picker
    var picker = document.createElement('div');
    picker.className = 'tiptap-template-picker';
    picker.innerHTML =
        '<div class="tiptap-template-picker__overlay"></div>' +
        '<div class="tiptap-template-picker__content">' +
            '<div class="tiptap-template-picker__header">' +
                '<h3>Insert Template</h3>' +
                '<button type="button" class="tiptap-template-picker__close" aria-label="Close">&times;</button>' +
            '</div>' +
            '<div class="tiptap-template-picker__search">' +
                '<input type="text" placeholder="Search templates..." class="tiptap-template-picker__search-input" />' +
            '</div>' +
            '<div class="tiptap-template-picker__body">' +
                '<div class="tiptap-template-picker__loading">Loading templates...</div>' +
            '</div>' +
        '</div>';

    document.body.appendChild(picker);

    // Close handlers
    function closePicker() {
        picker.remove();
    }

    picker.querySelector('.tiptap-template-picker__overlay').addEventListener('click', closePicker);
    picker.querySelector('.tiptap-template-picker__close').addEventListener('click', closePicker);

    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            closePicker();
            document.removeEventListener('keydown', escHandler);
        }
    });

    // Fetch and render templates
    var body = picker.querySelector('.tiptap-template-picker__body');
    var searchInput = picker.querySelector('.tiptap-template-picker__search-input');

    fetchTemplates().then(function(templates) {
        function renderTemplates(filteredTemplates) {
            if (filteredTemplates.length === 0) {
                body.innerHTML = '<div class="tiptap-template-picker__empty">No templates found</div>';
                return;
            }

            // Group by category
            var categories = {};
            filteredTemplates.forEach(function(tpl) {
                var cat = tpl.category || 'Other';
                if (!categories[cat]) {
                    categories[cat] = [];
                }
                categories[cat].push(tpl);
            });

            var html = '';
            Object.keys(categories).forEach(function(category) {
                html += '<div class="tiptap-template-picker__category">' +
                    '<div class="tiptap-template-picker__category-title">' + escapeHtml(category) + '</div>';

                categories[category].forEach(function(tpl, index) {
                    html += '<div class="tiptap-template-picker__item" data-index="' + templates.indexOf(tpl) + '">' +
                        '<div class="tiptap-template-picker__item-title">' + escapeHtml(tpl.title) + '</div>' +
                        (tpl.description ? '<div class="tiptap-template-picker__item-desc">' + escapeHtml(tpl.description) + '</div>' : '') +
                    '</div>';
                });

                html += '</div>';
            });

            body.innerHTML = html;

            // Click handlers for templates
            body.querySelectorAll('.tiptap-template-picker__item').forEach(function(el) {
                el.addEventListener('click', function() {
                    var idx = parseInt(el.getAttribute('data-index'), 10);
                    var tpl = templates[idx];
                    if (tpl && tpl.content) {
                        editor.chain().focus().insertContent(tpl.content).run();
                        closePicker();
                    }
                });
            });
        }

        renderTemplates(templates);

        // Search functionality
        searchInput.addEventListener('input', function() {
            var query = searchInput.value.toLowerCase();
            if (!query) {
                renderTemplates(templates);
                return;
            }

            var filtered = templates.filter(function(tpl) {
                return tpl.title.toLowerCase().includes(query) ||
                    (tpl.description && tpl.description.toLowerCase().includes(query)) ||
                    (tpl.category && tpl.category.toLowerCase().includes(query));
            });
            renderTemplates(filtered);
        });

        searchInput.focus();
    });
}

/**
 * Create suggestion popup element
 */
function createSuggestionPopup() {
    var popup = document.createElement('div');
    popup.className = 'tiptap-slash-popup';
    popup.style.display = 'none';
    document.body.appendChild(popup);
    return popup;
}

/**
 * Track if we're using keyboard navigation (to ignore mouse hover during keyboard use)
 */
var isKeyboardNavigating = false;
var keyboardNavTimeout = null;

/**
 * Update only the active state without re-rendering (for keyboard navigation)
 */
function updateActiveItem(popup, selectedIndex) {
    var items = popup.querySelectorAll('.tiptap-slash-popup__item');
    items.forEach(function(item, index) {
        if (index === selectedIndex) {
            item.classList.add('tiptap-slash-popup__item--active');
            // Scroll into view without re-rendering
            item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
            item.classList.remove('tiptap-slash-popup__item--active');
        }
    });
}

/**
 * Render command items in popup
 */
function renderCommandItems(items, popup, selectedIndex, onSelect, onHover) {
    if (items.length === 0) {
        popup.innerHTML = '<div class="tiptap-slash-popup__empty">No commands found</div>';
        return;
    }

    var html = '<div class="tiptap-slash-popup__header">Commands</div>';
    html += '<div class="tiptap-slash-popup__items">';
    html += items.map(function(item, index) {
        var activeClass = index === selectedIndex ? 'tiptap-slash-popup__item--active' : '';
        return '<div class="tiptap-slash-popup__item ' + activeClass + '" data-index="' + index + '">' +
            '<div class="tiptap-slash-popup__icon">' + (item.icon || '') + '</div>' +
            '<div class="tiptap-slash-popup__content">' +
                '<div class="tiptap-slash-popup__label">' + escapeHtml(item.label) + '</div>' +
                '<div class="tiptap-slash-popup__description">' + escapeHtml(item.description || '') + '</div>' +
            '</div>' +
        '</div>';
    }).join('');
    html += '</div>';

    popup.innerHTML = html;

    // Add click and hover handlers
    popup.querySelectorAll('.tiptap-slash-popup__item').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var index = parseInt(el.getAttribute('data-index'), 10);
            if (items[index]) {
                onSelect(items[index]);
            }
        });

        // Update active state on hover (only if not using keyboard)
        el.addEventListener('mouseenter', function() {
            // Skip if we're in the middle of keyboard navigation
            if (isKeyboardNavigating) {
                return;
            }

            var index = parseInt(el.getAttribute('data-index'), 10);
            // Remove active class from all items
            popup.querySelectorAll('.tiptap-slash-popup__item').forEach(function(item) {
                item.classList.remove('tiptap-slash-popup__item--active');
            });
            // Add active class to hovered item
            el.classList.add('tiptap-slash-popup__item--active');
            // Notify parent of hover for keyboard navigation sync
            if (onHover) {
                onHover(index);
            }
        });
    });
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Position popup near cursor
 * Note: popup uses position:fixed, so coordinates are relative to viewport
 */
function positionPopup(popup, clientRect) {
    if (!clientRect) {
        popup.style.display = 'none';
        return;
    }

    // Show popup first to get accurate dimensions
    popup.style.visibility = 'hidden';
    popup.style.display = 'block';

    // Use requestAnimationFrame to ensure DOM has rendered
    requestAnimationFrame(function() {
        var popupHeight = popup.offsetHeight || 300;
        var popupWidth = popup.offsetWidth || 280;
        var viewportHeight = window.innerHeight;
        var viewportWidth = window.innerWidth;

        // For position:fixed, use clientRect directly (viewport-relative)
        var cursorTop = clientRect.top;
        var cursorBottom = clientRect.bottom;
        var left = clientRect.left;

        // Calculate space above and below
        var spaceBelow = viewportHeight - cursorBottom - 16; // 16px margin
        var spaceAbove = cursorTop - 16; // 16px margin

        var top;

        // Position below cursor if there's enough space, otherwise above
        if (spaceBelow >= popupHeight || spaceBelow >= spaceAbove) {
            // Position below
            top = cursorBottom + 8;
            // If still overflows, constrain to viewport
            if (top + popupHeight > viewportHeight - 8) {
                popup.style.maxHeight = (viewportHeight - top - 16) + 'px';
            }
        } else {
            // Position above cursor
            top = cursorTop - popupHeight - 8;
            // If top goes negative, position at top with constrained height
            if (top < 8) {
                top = 8;
                popup.style.maxHeight = (cursorTop - 24) + 'px';
            }
        }

        // Adjust if popup would go off-screen (right)
        if (left + popupWidth > viewportWidth) {
            left = viewportWidth - popupWidth - 16;
        }

        // Adjust if popup would go off-screen (left)
        if (left < 16) {
            left = 16;
        }

        popup.style.top = top + 'px';
        popup.style.left = left + 'px';
        popup.style.visibility = 'visible';
    });
}

/**
 * Create the SlashCommands extension
 */
function createSlashCommandsExtension(customCommands) {
    var commands = defaultCommands.slice();

    // Add any custom commands registered via tiptapController
    if (window.leantime && window.leantime.tiptapController) {
        var registeredCommands = window.leantime.tiptapController.getSlashCommands();
        if (registeredCommands && registeredCommands.size > 0) {
            registeredCommands.forEach(function(cmdConfig, cmdName) {
                commands.push({
                    name: cmdName,
                    label: cmdConfig.label || cmdName,
                    description: cmdConfig.description || '',
                    icon: cmdConfig.icon || '',
                    command: cmdConfig.action || cmdConfig.command
                });
            });
        }
    }

    // Add custom commands passed as parameter
    if (customCommands && customCommands.length) {
        commands = commands.concat(customCommands);
    }

    var popup = null;
    var currentItems = [];
    var selectedIndex = 0;
    var commandRef = null;

    function selectItem(item) {
        if (commandRef && item && item.command) {
            commandRef(item);
        }
    }

    return Extension.create({
        name: 'slashCommands',

        addOptions: function() {
            return {
                suggestion: {
                    char: '/',
                    startOfLine: false,
                    pluginKey: new PluginKey('slashCommands'),

                    items: function(props) {
                        var query = (props.query || '').toLowerCase();
                        if (!query) {
                            return commands;
                        }
                        return commands.filter(function(cmd) {
                            return cmd.label.toLowerCase().includes(query) ||
                                (cmd.description && cmd.description.toLowerCase().includes(query)) ||
                                cmd.name.toLowerCase().includes(query);
                        });
                    },

                    command: function(props) {
                        var item = props.props;
                        var editor = props.editor;
                        var range = props.range;

                        // Delete the slash command text
                        editor.chain().focus().deleteRange(range).run();

                        // Execute the command
                        if (item && item.command) {
                            item.command(editor);
                        }
                    },

                    render: function() {
                        return {
                            onStart: function(props) {
                                if (!popup) {
                                    popup = createSuggestionPopup();
                                }

                                selectedIndex = 0;
                                currentItems = props.items || [];
                                commandRef = props.command;
                                isKeyboardNavigating = false;

                                renderCommandItems(currentItems, popup, selectedIndex, function(item) {
                                    selectItem(item);
                                }, function(hoveredIndex) {
                                    // Sync selectedIndex when mouse hovers
                                    selectedIndex = hoveredIndex;
                                });
                                positionPopup(popup, props.clientRect ? props.clientRect() : null);
                            },

                            onUpdate: function(props) {
                                selectedIndex = 0;
                                currentItems = props.items || [];
                                commandRef = props.command;

                                renderCommandItems(currentItems, popup, selectedIndex, function(item) {
                                    selectItem(item);
                                }, function(hoveredIndex) {
                                    // Sync selectedIndex when mouse hovers
                                    selectedIndex = hoveredIndex;
                                });
                                positionPopup(popup, props.clientRect ? props.clientRect() : null);
                            },

                            onKeyDown: function(props) {
                                var event = props.event;

                                if (event.key === 'ArrowDown') {
                                    event.preventDefault();
                                    // Set keyboard navigating flag
                                    isKeyboardNavigating = true;
                                    clearTimeout(keyboardNavTimeout);
                                    keyboardNavTimeout = setTimeout(function() {
                                        isKeyboardNavigating = false;
                                    }, 500);

                                    selectedIndex = (selectedIndex + 1) % currentItems.length;
                                    // Just update active state, don't re-render
                                    updateActiveItem(popup, selectedIndex);
                                    return true;
                                }

                                if (event.key === 'ArrowUp') {
                                    event.preventDefault();
                                    // Set keyboard navigating flag
                                    isKeyboardNavigating = true;
                                    clearTimeout(keyboardNavTimeout);
                                    keyboardNavTimeout = setTimeout(function() {
                                        isKeyboardNavigating = false;
                                    }, 500);

                                    selectedIndex = (selectedIndex - 1 + currentItems.length) % currentItems.length;
                                    // Just update active state, don't re-render
                                    updateActiveItem(popup, selectedIndex);
                                    return true;
                                }

                                if (event.key === 'Enter' || event.key === 'Tab') {
                                    event.preventDefault();
                                    if (currentItems[selectedIndex]) {
                                        selectItem(currentItems[selectedIndex]);
                                    }
                                    return true;
                                }

                                if (event.key === 'Escape') {
                                    event.preventDefault();
                                    if (popup) {
                                        popup.style.display = 'none';
                                    }
                                    return true;
                                }

                                return false;
                            },

                            onExit: function() {
                                if (popup) {
                                    popup.style.display = 'none';
                                }
                                currentItems = [];
                                selectedIndex = 0;
                                commandRef = null;
                                isKeyboardNavigating = false;
                                clearTimeout(keyboardNavTimeout);
                            }
                        };
                    }
                }
            };
        },

        addProseMirrorPlugins: function() {
            return [
                Suggestion({
                    editor: this.editor,
                    ...this.options.suggestion
                })
            ];
        }
    });
}

// Export
module.exports = {
    createSlashCommandsExtension: createSlashCommandsExtension,
    defaultCommands: defaultCommands
};
