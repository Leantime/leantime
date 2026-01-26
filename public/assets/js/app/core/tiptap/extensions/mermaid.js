/**
 * Mermaid Diagram Extension for Tiptap
 *
 * Enables inserting and editing Mermaid diagrams in the editor.
 * Uses the existing mermaid.js library already loaded in the project.
 *
 * @module tiptap/extensions/mermaid
 */

const { Node, mergeAttributes } = require('@tiptap/core');

// Default diagram template
var defaultDiagram = 'graph TD\n    A[Start] --> B{Decision}\n    B -->|Yes| C[Do Something]\n    B -->|No| D[Do Something Else]\n    C --> E[End]\n    D --> E';

// Counter for unique IDs
var mermaidIdCounter = 0;

/**
 * Create the Mermaid extension
 */
function createMermaidExtension() {
    return Node.create({
        name: 'mermaid',
        group: 'block',
        atom: true,
        draggable: true,

        addAttributes: function() {
            return {
                code: {
                    default: defaultDiagram,
                },
            };
        },

        parseHTML: function() {
            return [
                {
                    tag: 'div[data-mermaid]',
                    getAttrs: function(dom) {
                        return {
                            code: dom.getAttribute('data-code') || dom.textContent || defaultDiagram,
                        };
                    },
                },
                {
                    tag: 'pre.mermaid',
                    getAttrs: function(dom) {
                        return {
                            code: dom.textContent || defaultDiagram,
                        };
                    },
                },
            ];
        },

        renderHTML: function(props) {
            return [
                'div',
                mergeAttributes({
                    class: 'tiptap-mermaid',
                    'data-mermaid': '',
                    'data-code': props.node.attrs.code,
                }),
                ['pre', { class: 'mermaid-source' }, props.node.attrs.code],
            ];
        },

        addNodeView: function() {
            return function(props) {
                var node = props.node;
                var editor = props.editor;
                var getPos = props.getPos;

                // Create container
                var container = document.createElement('div');
                container.className = 'tiptap-mermaid';
                container.setAttribute('data-mermaid', '');

                // Create diagram display area
                var diagramContainer = document.createElement('div');
                diagramContainer.className = 'tiptap-mermaid__diagram';
                container.appendChild(diagramContainer);

                // Create edit button
                var editBtn = document.createElement('button');
                editBtn.type = 'button';
                editBtn.className = 'tiptap-mermaid__edit-btn';
                editBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
                editBtn.title = 'Edit diagram';
                container.appendChild(editBtn);

                // Create delete button
                var deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'tiptap-mermaid__delete-btn';
                deleteBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
                deleteBtn.title = 'Delete diagram';
                container.appendChild(deleteBtn);

                // Create edit mode container (hidden by default)
                var editContainer = document.createElement('div');
                editContainer.className = 'tiptap-mermaid__edit';
                editContainer.style.display = 'none';

                var textarea = document.createElement('textarea');
                textarea.className = 'tiptap-mermaid__textarea';
                textarea.value = node.attrs.code;
                textarea.placeholder = 'Enter Mermaid diagram code...';
                editContainer.appendChild(textarea);

                var buttonRow = document.createElement('div');
                buttonRow.className = 'tiptap-mermaid__buttons';

                var saveBtn = document.createElement('button');
                saveBtn.type = 'button';
                saveBtn.className = 'tiptap-mermaid__save-btn';
                saveBtn.textContent = 'Save';
                buttonRow.appendChild(saveBtn);

                var cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.className = 'tiptap-mermaid__cancel-btn';
                cancelBtn.textContent = 'Cancel';
                buttonRow.appendChild(cancelBtn);

                editContainer.appendChild(buttonRow);
                container.appendChild(editContainer);

                // Render function
                function renderDiagram(code) {
                    if (!window.mermaid) {
                        diagramContainer.innerHTML = '<div class="tiptap-mermaid__error">Mermaid library not loaded</div>';
                        return;
                    }

                    var id = 'mermaid-' + (++mermaidIdCounter);

                    try {
                        // Initialize mermaid if not done
                        if (!window.mermaidInitialized) {
                            window.mermaid.initialize({
                                startOnLoad: false,
                                theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'default',
                                securityLevel: 'strict',
                            });
                            window.mermaidInitialized = true;
                        }

                        // Render the diagram
                        window.mermaid.render(id, code).then(function(result) {
                            diagramContainer.innerHTML = result.svg;
                        }).catch(function(error) {
                            diagramContainer.innerHTML = '<div class="tiptap-mermaid__error">Invalid diagram syntax:<br>' + escapeHtml(error.message || String(error)) + '</div>';
                        });
                    } catch (error) {
                        diagramContainer.innerHTML = '<div class="tiptap-mermaid__error">Error rendering diagram:<br>' + escapeHtml(error.message || String(error)) + '</div>';
                    }
                }

                function escapeHtml(str) {
                    var div = document.createElement('div');
                    div.textContent = str;
                    return div.innerHTML;
                }

                // Initial render
                renderDiagram(node.attrs.code);

                // Edit button handler
                editBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    diagramContainer.style.display = 'none';
                    editBtn.style.display = 'none';
                    deleteBtn.style.display = 'none';
                    editContainer.style.display = 'block';
                    textarea.value = node.attrs.code;
                    textarea.focus();
                });

                // Save button handler
                saveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var newCode = textarea.value.trim();
                    if (newCode && typeof getPos === 'function') {
                        var pos = getPos();
                        editor.chain().focus().command(function(params) {
                            params.tr.setNodeMarkup(pos, undefined, { code: newCode });
                            return true;
                        }).run();
                    }
                    diagramContainer.style.display = 'block';
                    editBtn.style.display = '';
                    deleteBtn.style.display = '';
                    editContainer.style.display = 'none';
                    renderDiagram(newCode || node.attrs.code);
                });

                // Cancel button handler
                cancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    diagramContainer.style.display = 'block';
                    editBtn.style.display = '';
                    deleteBtn.style.display = '';
                    editContainer.style.display = 'none';
                });

                // Delete button handler
                deleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof getPos === 'function') {
                        var pos = getPos();
                        editor.chain().focus().command(function(params) {
                            params.tr.delete(pos, pos + node.nodeSize);
                            return true;
                        }).run();
                    }
                });

                return {
                    dom: container,
                    update: function(updatedNode) {
                        if (updatedNode.type.name !== 'mermaid') {
                            return false;
                        }
                        if (updatedNode.attrs.code !== node.attrs.code) {
                            node = updatedNode;
                            renderDiagram(updatedNode.attrs.code);
                        }
                        return true;
                    },
                    destroy: function() {
                        // Cleanup if needed
                    },
                    stopEvent: function(event) {
                        // Allow events on textarea and buttons
                        return event.target === textarea ||
                               event.target === saveBtn ||
                               event.target === cancelBtn ||
                               event.target === editBtn ||
                               event.target === deleteBtn;
                    },
                };
            };
        },

        addCommands: function() {
            var self = this;
            return {
                setMermaid: function(options) {
                    return function(props) {
                        return props.commands.insertContent({
                            type: self.name,
                            attrs: {
                                code: (options && options.code) || defaultDiagram,
                            },
                        });
                    };
                },
            };
        },

        addKeyboardShortcuts: function() {
            return {
                'Mod-Alt-m': function() {
                    return this.editor.commands.setMermaid();
                },
            };
        },
    });
}

/**
 * Show Mermaid dialog for inserting a new diagram
 */
function showMermaidDialog(editor) {
    // Create dialog overlay
    var overlay = document.createElement('div');
    overlay.className = 'tiptap-mermaid-dialog__overlay';

    var dialog = document.createElement('div');
    dialog.className = 'tiptap-mermaid-dialog';

    dialog.innerHTML =
        '<div class="tiptap-mermaid-dialog__header">' +
            '<h3>Insert Mermaid Diagram</h3>' +
            '<button type="button" class="tiptap-mermaid-dialog__close">&times;</button>' +
        '</div>' +
        '<div class="tiptap-mermaid-dialog__body">' +
            '<div class="tiptap-mermaid-dialog__templates">' +
                '<label>Template:</label>' +
                '<select class="tiptap-mermaid-dialog__template-select">' +
                    '<option value="flowchart">Flowchart</option>' +
                    '<option value="sequence">Sequence Diagram</option>' +
                    '<option value="gantt">Gantt Chart</option>' +
                    '<option value="pie">Pie Chart</option>' +
                    '<option value="mindmap">Mind Map</option>' +
                    '<option value="custom">Custom</option>' +
                '</select>' +
            '</div>' +
            '<textarea class="tiptap-mermaid-dialog__code" rows="10" placeholder="Enter Mermaid diagram code...">' + defaultDiagram + '</textarea>' +
            '<div class="tiptap-mermaid-dialog__preview">' +
                '<label>Preview:</label>' +
                '<div class="tiptap-mermaid-dialog__preview-area"></div>' +
            '</div>' +
        '</div>' +
        '<div class="tiptap-mermaid-dialog__footer">' +
            '<button type="button" class="tiptap-mermaid-dialog__cancel">Cancel</button>' +
            '<button type="button" class="tiptap-mermaid-dialog__insert">Insert Diagram</button>' +
        '</div>';

    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    // Get elements
    var codeArea = dialog.querySelector('.tiptap-mermaid-dialog__code');
    var templateSelect = dialog.querySelector('.tiptap-mermaid-dialog__template-select');
    var previewArea = dialog.querySelector('.tiptap-mermaid-dialog__preview-area');
    var closeBtn = dialog.querySelector('.tiptap-mermaid-dialog__close');
    var cancelBtn = dialog.querySelector('.tiptap-mermaid-dialog__cancel');
    var insertBtn = dialog.querySelector('.tiptap-mermaid-dialog__insert');

    // Templates
    var templates = {
        flowchart: 'graph TD\n    A[Start] --> B{Decision}\n    B -->|Yes| C[Do Something]\n    B -->|No| D[Do Something Else]\n    C --> E[End]\n    D --> E',
        sequence: 'sequenceDiagram\n    participant A as Alice\n    participant B as Bob\n    A->>B: Hello Bob!\n    B-->>A: Hi Alice!',
        gantt: 'gantt\n    title Project Timeline\n    dateFormat  YYYY-MM-DD\n    section Planning\n    Research           :a1, 2024-01-01, 7d\n    Design             :a2, after a1, 5d\n    section Development\n    Implementation     :a3, after a2, 10d\n    Testing            :a4, after a3, 5d',
        pie: 'pie title Distribution\n    "Category A" : 40\n    "Category B" : 30\n    "Category C" : 20\n    "Category D" : 10',
        mindmap: 'mindmap\n  root((Main Topic))\n    Branch A\n      Leaf 1\n      Leaf 2\n    Branch B\n      Leaf 3\n    Branch C',
        custom: '',
    };

    // Preview function
    var previewTimeout;
    function updatePreview() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(function() {
            var code = codeArea.value.trim();
            if (!code) {
                previewArea.innerHTML = '<span class="tiptap-mermaid-dialog__preview-empty">Enter code to see preview</span>';
                return;
            }

            if (window.mermaid) {
                var id = 'mermaid-preview-' + Date.now();
                try {
                    window.mermaid.render(id, code).then(function(result) {
                        previewArea.innerHTML = result.svg;
                    }).catch(function(error) {
                        previewArea.innerHTML = '<span class="tiptap-mermaid-dialog__preview-error">' + (error.message || 'Invalid syntax') + '</span>';
                    });
                } catch (error) {
                    previewArea.innerHTML = '<span class="tiptap-mermaid-dialog__preview-error">' + (error.message || 'Error') + '</span>';
                }
            }
        }, 500);
    }

    // Template change handler
    templateSelect.addEventListener('change', function() {
        var template = templates[templateSelect.value];
        if (template !== undefined) {
            codeArea.value = template;
            updatePreview();
        }
    });

    // Code change handler
    codeArea.addEventListener('input', updatePreview);

    // Close handlers
    function closeDialog() {
        overlay.remove();
    }

    closeBtn.addEventListener('click', closeDialog);
    cancelBtn.addEventListener('click', closeDialog);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeDialog();
        }
    });

    // Insert handler
    insertBtn.addEventListener('click', function() {
        var code = codeArea.value.trim();
        if (code) {
            editor.chain().focus().setMermaid({ code: code }).run();
        }
        closeDialog();
    });

    // Initial preview
    updatePreview();

    // Focus code area
    setTimeout(function() {
        codeArea.focus();
    }, 100);
}

module.exports = {
    createMermaidExtension: createMermaidExtension,
    showMermaidDialog: showMermaidDialog,
};
