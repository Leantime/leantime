/**
 * Math/LaTeX Extension for Tiptap
 *
 * Enables inserting and rendering LaTeX math formulas using KaTeX.
 * Supports both inline math ($ ... $) and block math ($$ ... $$).
 *
 * @module tiptap/extensions/math
 */

const { Node, mergeAttributes } = require('@tiptap/core');

// Import KaTeX directly from npm package (bundled with webpack)
// Note: KaTeX CSS is loaded separately via link tag in pageBottom.blade.php
const katex = require('katex');

// Make KaTeX available globally for consistency
window.katex = katex;

/**
 * Load KaTeX - returns immediately since it's bundled
 */
function loadKaTeX() {
    return Promise.resolve(katex);
}

/**
 * Render LaTeX to HTML
 */
function renderMath(latex, displayMode) {
    displayMode = displayMode === undefined ? false : displayMode;

    try {
        return katex.renderToString(latex, {
            throwOnError: false,
            displayMode: displayMode,
            strict: false,
            trust: false,
            output: 'html',
        });
    } catch (error) {
        return '<span class="tiptap-math__error">' + escapeHtml(error.message || 'Invalid LaTeX') + '</span>';
    }
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Math Inline Node - For inline math like $x^2$
 */
var MathInline = Node.create({
    name: 'mathInline',
    group: 'inline',
    inline: true,
    atom: true,

    addAttributes: function() {
        return {
            latex: {
                default: '',
            },
        };
    },

    parseHTML: function() {
        return [
            {
                tag: 'span[data-math-inline]',
                getAttrs: function(dom) {
                    return { latex: dom.getAttribute('data-latex') || dom.textContent };
                },
            },
            {
                tag: 'span.katex',
                getAttrs: function(dom) {
                    var annotation = dom.querySelector('annotation');
                    return { latex: annotation ? annotation.textContent : '' };
                },
            },
        ];
    },

    renderHTML: function(props) {
        return [
            'span',
            mergeAttributes({
                class: 'tiptap-math tiptap-math--inline',
                'data-math-inline': '',
                'data-latex': props.node.attrs.latex,
            }),
            props.node.attrs.latex,
        ];
    },

    addNodeView: function() {
        return function(props) {
            var node = props.node;
            var editor = props.editor;
            var getPos = props.getPos;

            var dom = document.createElement('span');
            dom.className = 'tiptap-math tiptap-math--inline';
            dom.setAttribute('data-math-inline', '');
            dom.setAttribute('data-latex', node.attrs.latex);

            // Render math
            function render() {
                loadKaTeX().then(function() {
                    dom.innerHTML = renderMath(node.attrs.latex, false);
                }).catch(function() {
                    dom.innerHTML = '<span class="tiptap-math__placeholder">$' + escapeHtml(node.attrs.latex) + '$</span>';
                });
            }

            render();

            // Double-click to edit
            dom.addEventListener('dblclick', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var newLatex = window.prompt('Edit LaTeX:', node.attrs.latex);
                if (newLatex !== null && typeof getPos === 'function') {
                    var pos = getPos();
                    editor.chain().focus().command(function(cmdProps) {
                        cmdProps.tr.setNodeMarkup(pos, undefined, { latex: newLatex });
                        return true;
                    }).run();
                }
            });

            return {
                dom: dom,
                update: function(updatedNode) {
                    if (updatedNode.type.name !== 'mathInline') {
                        return false;
                    }
                    if (updatedNode.attrs.latex !== node.attrs.latex) {
                        node = updatedNode;
                        dom.setAttribute('data-latex', node.attrs.latex);
                        render();
                    }
                    return true;
                },
            };
        };
    },

    addCommands: function() {
        var self = this;
        return {
            setMathInline: function(options) {
                return function(props) {
                    return props.commands.insertContent({
                        type: self.name,
                        attrs: {
                            latex: (options && options.latex) || 'x^2',
                        },
                    });
                };
            },
        };
    },
});

/**
 * Math Block Node - For display math like $$ ... $$
 */
var MathBlock = Node.create({
    name: 'mathBlock',
    group: 'block',
    atom: true,
    draggable: true,

    addAttributes: function() {
        return {
            latex: {
                default: '',
            },
        };
    },

    parseHTML: function() {
        return [
            {
                tag: 'div[data-math-block]',
                getAttrs: function(dom) {
                    return { latex: dom.getAttribute('data-latex') || dom.textContent };
                },
            },
            {
                tag: 'div.katex-display',
                getAttrs: function(dom) {
                    var annotation = dom.querySelector('annotation');
                    return { latex: annotation ? annotation.textContent : '' };
                },
            },
        ];
    },

    renderHTML: function(props) {
        return [
            'div',
            mergeAttributes({
                class: 'tiptap-math tiptap-math--block',
                'data-math-block': '',
                'data-latex': props.node.attrs.latex,
            }),
            props.node.attrs.latex,
        ];
    },

    addNodeView: function() {
        return function(props) {
            var node = props.node;
            var editor = props.editor;
            var getPos = props.getPos;

            var dom = document.createElement('div');
            dom.className = 'tiptap-math tiptap-math--block';
            dom.setAttribute('data-math-block', '');
            dom.setAttribute('data-latex', node.attrs.latex);

            var mathContainer = document.createElement('div');
            mathContainer.className = 'tiptap-math__display';
            dom.appendChild(mathContainer);

            // Edit button
            var editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'tiptap-math__edit-btn';
            editBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
            editBtn.title = 'Edit equation';
            dom.appendChild(editBtn);

            // Delete button
            var deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'tiptap-math__delete-btn';
            deleteBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
            deleteBtn.title = 'Delete equation';
            dom.appendChild(deleteBtn);

            // Render math
            function render() {
                loadKaTeX().then(function() {
                    mathContainer.innerHTML = renderMath(node.attrs.latex, true);
                }).catch(function() {
                    mathContainer.innerHTML = '<div class="tiptap-math__placeholder">$$' + escapeHtml(node.attrs.latex) + '$$</div>';
                });
            }

            render();

            // Edit button handler
            editBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showMathDialog(editor, node.attrs.latex, true, function(newLatex) {
                    if (typeof getPos === 'function') {
                        var pos = getPos();
                        editor.chain().focus().command(function(cmdProps) {
                            cmdProps.tr.setNodeMarkup(pos, undefined, { latex: newLatex });
                            return true;
                        }).run();
                    }
                });
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
                dom: dom,
                update: function(updatedNode) {
                    if (updatedNode.type.name !== 'mathBlock') {
                        return false;
                    }
                    if (updatedNode.attrs.latex !== node.attrs.latex) {
                        node = updatedNode;
                        dom.setAttribute('data-latex', node.attrs.latex);
                        render();
                    }
                    return true;
                },
                stopEvent: function(event) {
                    return event.target === editBtn || event.target === deleteBtn;
                },
            };
        };
    },

    addCommands: function() {
        var self = this;
        return {
            setMathBlock: function(options) {
                return function(props) {
                    return props.commands.insertContent({
                        type: self.name,
                        attrs: {
                            latex: (options && options.latex) || '\\sum_{i=1}^{n} x_i',
                        },
                    });
                };
            },
        };
    },

    addKeyboardShortcuts: function() {
        return {
            'Mod-Alt-e': function() {
                return this.editor.commands.setMathBlock();
            },
        };
    },
});

/**
 * Show Math dialog for inserting/editing equations
 */
function showMathDialog(editor, initialLatex, isBlock, onSave) {
    initialLatex = initialLatex || '';
    isBlock = isBlock === undefined ? true : isBlock;

    // Create dialog overlay
    var overlay = document.createElement('div');
    overlay.className = 'tiptap-math-dialog__overlay';

    var dialog = document.createElement('div');
    dialog.className = 'tiptap-math-dialog';

    dialog.innerHTML =
        '<div class="tiptap-math-dialog__header">' +
            '<h3>' + (onSave ? 'Edit' : 'Insert') + ' Math Equation</h3>' +
            '<button type="button" class="tiptap-math-dialog__close">&times;</button>' +
        '</div>' +
        '<div class="tiptap-math-dialog__body">' +
            '<div class="tiptap-math-dialog__type">' +
                '<label><input type="radio" name="mathType" value="block" ' + (isBlock ? 'checked' : '') + '> Block (display)</label>' +
                '<label><input type="radio" name="mathType" value="inline" ' + (!isBlock ? 'checked' : '') + '> Inline</label>' +
            '</div>' +
            '<textarea class="tiptap-math-dialog__code" rows="4" placeholder="Enter LaTeX...">' + escapeHtml(initialLatex) + '</textarea>' +
            '<div class="tiptap-math-dialog__examples">' +
                '<small>Examples: \\frac{a}{b}, \\sqrt{x}, x^2, \\sum_{i=1}^n, \\int_0^1</small>' +
            '</div>' +
            '<div class="tiptap-math-dialog__preview">' +
                '<label>Preview:</label>' +
                '<div class="tiptap-math-dialog__preview-area"></div>' +
            '</div>' +
        '</div>' +
        '<div class="tiptap-math-dialog__footer">' +
            '<button type="button" class="tiptap-math-dialog__cancel">Cancel</button>' +
            '<button type="button" class="tiptap-math-dialog__insert">' + (onSave ? 'Save' : 'Insert') + '</button>' +
        '</div>';

    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    // Get elements
    var codeArea = dialog.querySelector('.tiptap-math-dialog__code');
    var previewArea = dialog.querySelector('.tiptap-math-dialog__preview-area');
    var typeRadios = dialog.querySelectorAll('input[name="mathType"]');
    var closeBtn = dialog.querySelector('.tiptap-math-dialog__close');
    var cancelBtn = dialog.querySelector('.tiptap-math-dialog__cancel');
    var insertBtn = dialog.querySelector('.tiptap-math-dialog__insert');

    // Preview function
    var previewTimeout;
    function updatePreview() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(function() {
            var latex = codeArea.value.trim();
            var displayMode = dialog.querySelector('input[name="mathType"]:checked').value === 'block';

            if (!latex) {
                previewArea.innerHTML = '<span class="tiptap-math-dialog__preview-empty">Enter LaTeX to see preview</span>';
                return;
            }

            loadKaTeX().then(function() {
                previewArea.innerHTML = renderMath(latex, displayMode);
            }).catch(function() {
                previewArea.innerHTML = '<span class="tiptap-math-dialog__preview-error">Could not load KaTeX</span>';
            });
        }, 300);
    }

    // Type change handler
    typeRadios.forEach(function(radio) {
        radio.addEventListener('change', updatePreview);
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

    // Insert/Save handler
    insertBtn.addEventListener('click', function() {
        var latex = codeArea.value.trim();
        var useBlock = dialog.querySelector('input[name="mathType"]:checked').value === 'block';

        if (latex) {
            if (onSave) {
                onSave(latex);
            } else if (useBlock) {
                editor.chain().focus().setMathBlock({ latex: latex }).run();
            } else {
                editor.chain().focus().setMathInline({ latex: latex }).run();
            }
        }
        closeDialog();
    });

    // Initial preview
    updatePreview();

    // Focus code area
    setTimeout(function() {
        codeArea.focus();
        codeArea.select();
    }, 100);
}

/**
 * Create the Math extension bundle
 */
function createMathExtension() {
    return [MathInline, MathBlock];
}

module.exports = {
    createMathExtension: createMathExtension,
    MathInline: MathInline,
    MathBlock: MathBlock,
    showMathDialog: showMathDialog,
    loadKaTeX: loadKaTeX,
};
