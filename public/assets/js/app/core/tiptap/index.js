/**
 * Tiptap Editor Module for Leantime
 *
 * Main entry point that exports the tiptapController
 * and all editor functionality.
 *
 * @module tiptap
 */

// Use require for Node/Webpack compatibility
const { Editor } = require('@tiptap/core');
const StarterKit = require('@tiptap/starter-kit').default;
const Placeholder = require('@tiptap/extension-placeholder').default;
const Link = require('@tiptap/extension-link').default;
const Image = require('@tiptap/extension-image').default;
const TaskList = require('@tiptap/extension-task-list').default;
const TaskItem = require('@tiptap/extension-task-item').default;
const Table = require('@tiptap/extension-table').default;
const TableRow = require('@tiptap/extension-table-row').default;
const TableCell = require('@tiptap/extension-table-cell').default;
const TableHeader = require('@tiptap/extension-table-header').default;
const Highlight = require('@tiptap/extension-highlight').default;
const Underline = require('@tiptap/extension-underline').default;
const Typography = require('@tiptap/extension-typography').default;

/**
 * EditorRegistry - Manages Tiptap editor instances
 */
var EditorRegistry = (function() {
    // Private storage using closure
    var instances = new WeakMap();
    var elementIds = new Map();
    var elements = new Set();

    return {
        register: function(element, editor) {
            if (instances.has(element)) {
                this.destroy(element);
            }
            instances.set(element, editor);
            elements.add(element);
            if (element.id) {
                elementIds.set(element.id, element);
            }
            element.setAttribute('data-tiptap-editor', 'true');
        },

        get: function(elementOrId) {
            var element = elementOrId;
            if (typeof elementOrId === 'string') {
                element = elementIds.get(elementOrId) || document.getElementById(elementOrId);
            }
            if (!element) return null;
            return instances.get(element) || null;
        },

        has: function(element) {
            return instances.has(element);
        },

        destroy: function(element) {
            var editor = instances.get(element);
            if (!editor) return false;

            try {
                // Sync content to original textarea if present
                var textarea = this.findTextarea(element);
                if (textarea) {
                    textarea.value = editor.getHTML();
                }
                editor.destroy();
            } catch (e) {
                console.warn('[TiptapRegistry] Error destroying editor:', e);
            }

            instances.delete(element);
            elements.delete(element);
            if (element.id) {
                elementIds.delete(element.id);
            }
            element.removeAttribute('data-tiptap-editor');
            return true;
        },

        destroyWithin: function(container) {
            var editors = container.querySelectorAll('[data-tiptap-editor]');
            var count = 0;
            var self = this;

            editors.forEach(function(element) {
                if (self.destroy(element)) {
                    count++;
                }
            });

            if (container.hasAttribute && container.hasAttribute('data-tiptap-editor')) {
                if (this.destroy(container)) {
                    count++;
                }
            }
            return count;
        },

        destroyAll: function() {
            var count = 0;
            var elementsArray = Array.from(elements);
            var self = this;

            elementsArray.forEach(function(element) {
                if (self.destroy(element)) {
                    count++;
                }
            });
            return count;
        },

        getAll: function() {
            var result = [];
            elements.forEach(function(element) {
                var editor = instances.get(element);
                if (editor) {
                    result.push({ element: element, editor: editor });
                }
            });
            return result;
        },

        get count() {
            return elements.size;
        },

        findTextarea: function(element) {
            if (element.tagName === 'TEXTAREA') {
                return element;
            }
            var textareaId = element.getAttribute('data-textarea-id');
            if (textareaId) {
                return document.getElementById(textareaId);
            }
            var sibling = element.previousElementSibling || element.nextElementSibling;
            if (sibling && sibling.tagName === 'TEXTAREA') {
                return sibling;
            }
            var parent = element.parentElement;
            if (parent) {
                var textarea = parent.querySelector('textarea');
                if (textarea) {
                    return textarea;
                }
            }
            return null;
        }
    };
})();

/**
 * Default editor options
 */
var defaultOptions = {
    placeholder: "Type '/' for commands or start writing...",
    autosave: false,
    autosaveKey: null,
    autosaveInterval: 30000,
    uploadUrl: '/api/files',
    toolbar: null, // 'complex', 'simple', 'notes', or false to disable
    onUpdate: null,
    onBlur: null,
    onFocus: null,
    onCreate: null,
};

/**
 * Create a Tiptap editor instance
 */
function createTiptapEditor(elementOrSelector, options) {
    options = Object.assign({}, defaultOptions, options || {});

    var element;
    if (typeof elementOrSelector === 'string') {
        element = document.querySelector(elementOrSelector);
    } else {
        element = elementOrSelector;
    }

    if (!element) {
        console.error('[TiptapEditor] Element not found:', elementOrSelector);
        return null;
    }

    var textarea = null;

    // Check if element is a textarea
    if (element.tagName === 'TEXTAREA') {
        textarea = element;

        // Check if wrapper already exists (editor being re-initialized)
        var existingWrapper = textarea.closest('.tiptap-wrapper');
        if (!existingWrapper && textarea.nextElementSibling && textarea.nextElementSibling.classList.contains('tiptap-wrapper')) {
            existingWrapper = textarea.nextElementSibling;
        }

        var wrapper, editorEl;

        if (existingWrapper) {
            // Reuse existing wrapper, but clean up old toolbars and editor element
            wrapper = existingWrapper;

            // Remove any existing toolbars
            var oldToolbars = wrapper.querySelectorAll('.tiptap-toolbar');
            oldToolbars.forEach(function(tb) { tb.remove(); });

            // Remove old editor element if exists
            var oldEditorEl = wrapper.querySelector('.tiptap-editor');
            if (oldEditorEl) {
                // Destroy any existing editor instance
                EditorRegistry.destroy(oldEditorEl);
                oldEditorEl.remove();
            }

            // Create new editor container
            editorEl = document.createElement('div');
            editorEl.className = 'tiptap-editor';
            if (textarea.id) {
                editorEl.setAttribute('data-textarea-id', textarea.id);
            }
            wrapper.appendChild(editorEl);
        } else {
            // Create new wrapper
            wrapper = document.createElement('div');
            wrapper.className = 'tiptap-wrapper';

            // Create editor container
            editorEl = document.createElement('div');
            editorEl.className = 'tiptap-editor';
            if (textarea.id) {
                editorEl.setAttribute('data-textarea-id', textarea.id);
            }

            // Hide textarea but keep for form submission
            textarea.style.display = 'none';

            // Insert wrapper after textarea
            textarea.parentNode.insertBefore(wrapper, textarea.nextSibling);
            wrapper.appendChild(textarea);
            wrapper.appendChild(editorEl);
        }

        element = editorEl;
    } else {
        // Look for textarea in parent
        var parent = element.parentElement;
        if (parent) {
            textarea = parent.querySelector('textarea');
        }

        // Also check for and remove existing toolbars in the wrapper
        var wrapper = element.closest('.tiptap-wrapper');
        if (wrapper) {
            var oldToolbars = wrapper.querySelectorAll('.tiptap-toolbar');
            oldToolbars.forEach(function(tb) { tb.remove(); });
        }
    }

    // Get initial content
    var initialContent = textarea ? textarea.value : (element.innerHTML || '');

    // Build extensions
    var extensions = [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4],
            },
        }),
        Placeholder.configure({
            placeholder: options.placeholder,
            emptyEditorClass: 'is-editor-empty',
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                rel: 'noopener noreferrer',
            },
        }),
        Image.configure({
            inline: false,
            allowBase64: false,
        }),
        TaskList,
        TaskItem.configure({
            nested: true,
        }),
        Highlight.configure({
            multicolor: true,
        }),
        Underline,
        Typography,
    ];

    // Add table extensions if needed
    if (options.tables !== false) {
        extensions.push(
            Table.configure({
                resizable: true,
            }),
            TableRow,
            TableCell,
            TableHeader
        );
    }

    // Create editor
    var editor = new Editor({
        element: element,
        extensions: extensions,
        content: initialContent,
        autofocus: false,
        editable: true,
        injectCSS: false,

        onCreate: function(params) {
            element.setAttribute('data-tiptap-editor', 'true');
            if (options.onCreate) {
                options.onCreate(params);
            }
        },
        onUpdate: function(params) {
            // Sync to textarea
            if (textarea) {
                textarea.value = params.editor.getHTML();
            }
            if (options.onUpdate) {
                options.onUpdate(params);
            }
        },
        onBlur: function(params) {
            // Sync to textarea on blur
            if (textarea) {
                textarea.value = params.editor.getHTML();
            }
            if (options.onBlur) {
                options.onBlur(params);
            }
        },
        onFocus: function(params) {
            if (options.onFocus) {
                options.onFocus(params);
            }
        },
    });

    // Register with registry
    EditorRegistry.register(element, editor);

    // Create toolbar if configured
    var toolbar = null;
    if (options.toolbar && window.leantime && window.leantime.tiptapToolbar) {
        toolbar = window.leantime.tiptapToolbar.create(editor, options.toolbar);
        window.leantime.tiptapToolbar.attach({ element: element }, toolbar);
    }

    // Return wrapper object with useful methods
    return {
        editor: editor,
        element: element,
        textarea: textarea,
        toolbar: toolbar,
        getHTML: function() { return editor.getHTML(); },
        getText: function() { return editor.getText(); },
        getJSON: function() { return editor.getJSON(); },
        setContent: function(content) { editor.commands.setContent(content); },
        insertContent: function(content) { editor.commands.insertContent(content); },
        focus: function(position) { editor.commands.focus(position || 'end'); },
        blur: function() { editor.commands.blur(); },
        isEmpty: function() { return editor.isEmpty; },
        isEditable: function() { return editor.isEditable; },
        setEditable: function(editable) { editor.setEditable(editable); },
        destroy: function() {
            if (toolbar) {
                toolbar.destroy();
            }
            if (textarea) {
                textarea.value = editor.getHTML();
            }
            editor.destroy();
            EditorRegistry.destroy(element);
        }
    };
}

/**
 * Initialize editors by selector
 */
function initEditorsBySelector(selector, options) {
    var editors = [];
    var textareas = document.querySelectorAll(selector);

    textareas.forEach(function(textarea) {
        if (textarea.getAttribute('data-tiptap-initialized') === 'true') {
            return;
        }
        var editor = createTiptapEditor(textarea, options);
        if (editor) {
            textarea.setAttribute('data-tiptap-initialized', 'true');
            editors.push(editor);
        }
    });

    return editors;
}

/**
 * Setup HTMX lifecycle hooks
 */
function setupHtmxHooks() {
    // Clean up editors before HTMX replaces content
    document.body.addEventListener('htmx:beforeSwap', function(event) {
        var target = event.detail.target;
        if (!target) return;

        var count = EditorRegistry.destroyWithin(target);
        if (count > 0) {
            console.log('[Tiptap] Destroyed', count, 'editor(s) before HTMX swap');
        }
    });

    // Initialize new editors after HTMX swaps content
    document.body.addEventListener('htmx:afterSwap', function(event) {
        var target = event.detail.target;
        if (!target) return;

        setTimeout(function() {
            if (window.leantime && window.leantime.tiptapController) {
                window.leantime.tiptapController.initEditors(target);
            }
        }, 50);
    });

    // Sync editor content before form submission
    document.body.addEventListener('htmx:beforeRequest', function(event) {
        var element = event.detail.elt;
        if (!element) return;

        var form = element.closest('form') || element;
        var editors = form.querySelectorAll('[data-tiptap-editor]');

        editors.forEach(function(editorEl) {
            var editor = EditorRegistry.get(editorEl);
            if (editor) {
                var textareaId = editorEl.getAttribute('data-textarea-id');
                var textarea = textareaId ? document.getElementById(textareaId) : null;
                if (textarea) {
                    textarea.value = editor.getHTML();
                }
            }
        });
    });

    console.log('[Tiptap] HTMX integration initialized');
}

/**
 * Extension registry for plugins
 */
var extensionRegistry = new Map();
var slashCommandRegistry = new Map();
var toolbarButtonRegistry = new Map();

/**
 * Tiptap Controller - Main interface for managing editors
 */
var tiptapController = {
    registry: EditorRegistry,

    initComplex: function(elementOrSelector, options) {
        var entityId = (options && options.entityId) ||
            (document.querySelector('input[name="id"]') ? document.querySelector('input[name="id"]').value : 'new');
        var projectId = (options && options.projectId) || (window.leantime && window.leantime.projectId) || '';
        var path = window.location.pathname;

        var mergedOptions = Object.assign({
            placeholder: "Start writing your description...\nType '/' for commands",
            autosave: true,
            autosaveKey: 'leantime-tiptap-complex-' + path + '-' + projectId + '-' + entityId,
            tables: true,
            toolbar: 'complex',
        }, options || {});

        return createTiptapEditor(elementOrSelector, mergedOptions);
    },

    initSimple: function(elementOrSelector, options) {
        var formId = (options && options.formId) || 'comment';
        var path = window.location.pathname;

        var mergedOptions = Object.assign({
            placeholder: 'Write a comment...',
            autosave: true,
            autosaveKey: 'leantime-tiptap-simple-' + path + '-' + formId,
            tables: false,
            toolbar: 'simple',
        }, options || {});

        return createTiptapEditor(elementOrSelector, mergedOptions);
    },

    initNotes: function(elementOrSelector, options) {
        var noteId = (options && options.noteId) ||
            (document.querySelector('input[name="id"]') ? document.querySelector('input[name="id"]').value : 'new');
        var notebookId = (options && options.notebookId) ||
            (document.querySelector('input[name="canvasId"]') ? document.querySelector('input[name="canvasId"]').value : '');

        var mergedOptions = Object.assign({
            placeholder: "Start writing your note...\nType '/' for commands",
            autosave: true,
            autosaveKey: 'leantime-tiptap-notes-' + notebookId + '-' + noteId,
            tables: true,
            toolbar: 'notes',
        }, options || {});

        return createTiptapEditor(elementOrSelector, mergedOptions);
    },

    initInline: function(elementOrSelector, options) {
        var mergedOptions = Object.assign({
            placeholder: 'Click to edit...',
            autosave: false,
            tables: false,
            toolbar: false,
        }, options || {});

        return createTiptapEditor(elementOrSelector, mergedOptions);
    },

    initEditors: function(container) {
        container = container || document;
        var editors = [];

        // Initialize complex editors
        container.querySelectorAll('textarea.tiptapComplex').forEach(function(textarea) {
            if (textarea.getAttribute('data-tiptap-initialized') !== 'true') {
                var editor = tiptapController.initComplex(textarea);
                if (editor) {
                    textarea.setAttribute('data-tiptap-initialized', 'true');
                    editors.push(editor);
                }
            }
        });

        // Initialize simple editors
        container.querySelectorAll('textarea.tiptapSimple').forEach(function(textarea) {
            if (textarea.getAttribute('data-tiptap-initialized') !== 'true') {
                var editor = tiptapController.initSimple(textarea);
                if (editor) {
                    textarea.setAttribute('data-tiptap-initialized', 'true');
                    editors.push(editor);
                }
            }
        });

        // Initialize notes editors
        container.querySelectorAll('textarea.tiptapNotes').forEach(function(textarea) {
            if (textarea.getAttribute('data-tiptap-initialized') !== 'true') {
                var editor = tiptapController.initNotes(textarea);
                if (editor) {
                    textarea.setAttribute('data-tiptap-initialized', 'true');
                    editors.push(editor);
                }
            }
        });

        if (editors.length > 0) {
            console.log('[Tiptap] Initialized', editors.length, 'editor(s)');
        }

        return editors;
    },

    // Backwards compatibility methods
    initComplexEditor: function() {
        return initEditorsBySelector('textarea.tiptapComplex', {
            placeholder: "Start writing your description...",
            tables: true,
        });
    },

    initSimpleEditor: function(callback) {
        var editors = initEditorsBySelector('textarea.tiptapSimple', {
            placeholder: 'Write a comment...',
            tables: false,
        });
        if (callback && editors.length > 0) {
            callback(editors);
        }
        return editors;
    },

    initNotesEditor: function(callback) {
        var editors = initEditorsBySelector('textarea.tiptapNotes', {
            placeholder: "Start writing your note...",
            tables: true,
            onBlur: callback,
        });
        return editors;
    },

    getEditor: function(elementOrId) {
        return EditorRegistry.get(elementOrId);
    },

    destroyAll: function() {
        return EditorRegistry.destroyAll();
    },

    registerExtension: function(name, extension) {
        extensionRegistry.set(name, extension);
        console.log('[Tiptap] Registered extension:', name);
    },

    registerSlashCommand: function(command, handler) {
        slashCommandRegistry.set(command, handler);
        console.log('[Tiptap] Registered slash command:', command);
    },

    registerToolbarButton: function(name, config) {
        toolbarButtonRegistry.set(name, config);
        console.log('[Tiptap] Registered toolbar button:', name);
    },

    getSlashCommands: function() {
        return slashCommandRegistry;
    },

    getToolbarButtons: function() {
        return toolbarButtonRegistry;
    },
};

// Make available globally
window.leantime = window.leantime || {};
window.leantime.tiptapController = tiptapController;

// Auto-initialize HTMX hooks when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupHtmxHooks);
} else {
    setupHtmxHooks();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        tiptapController: tiptapController,
        EditorRegistry: EditorRegistry,
        createTiptapEditor: createTiptapEditor,
    };
}
