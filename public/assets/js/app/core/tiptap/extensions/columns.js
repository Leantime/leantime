/**
 * Column Layouts Extension for Tiptap
 *
 * Enables creating multi-column layouts (2, 3, or 4 columns)
 * with optional asymmetric layout variants (sidebar-left, sidebar-right, sidebar-both).
 * Custom implementation for responsive grid-based layouts.
 *
 * @module tiptap/extensions/columns
 */

const { Node, mergeAttributes } = require('@tiptap/core');

/**
 * Shared helper: delete a columnLayout node at a known position,
 * extracting its content back into regular paragraphs.
 */
function deleteColumnLayoutAtPos(props, layoutNode, layoutPos) {
    var content = [];
    layoutNode.forEach(function(column) {
        column.forEach(function(child) {
            content.push(child.toJSON());
        });
    });

    return props.chain()
        .command(function(cmdProps) {
            var nodes = content.map(function(c) {
                return props.editor.schema.nodeFromJSON(c);
            });
            if (nodes.length === 0) {
                nodes = [props.editor.schema.nodes.paragraph.create()];
            }
            cmdProps.tr.replaceWith(layoutPos, layoutPos + layoutNode.nodeSize, nodes);
            return true;
        })
        .run();
}

/**
 * Column Layout Node - Container for columns
 */
var ColumnLayout = Node.create({
    name: 'columnLayout',
    group: 'block',
    content: 'column+',
    defining: true,
    isolating: true,

    addAttributes: function() {
        return {
            columns: {
                default: 2,
                parseHTML: function(element) {
                    return parseInt(element.getAttribute('data-columns'), 10) || 2;
                },
                renderHTML: function(attributes) {
                    return { 'data-columns': attributes.columns };
                },
            },
            layout: {
                default: 'equal',
                parseHTML: function(element) {
                    return element.getAttribute('data-layout') || 'equal';
                },
                renderHTML: function(attributes) {
                    return { 'data-layout': attributes.layout };
                },
            },
        };
    },

    parseHTML: function() {
        return [
            { tag: 'div[data-column-layout]' },
            { tag: 'div.tiptap-columns' },
        ];
    },

    renderHTML: function(props) {
        var cols = props.node.attrs.columns || 2;
        var layout = props.node.attrs.layout || 'equal';
        var classes = 'tiptap-columns tiptap-columns--' + cols;
        if (layout !== 'equal') {
            classes += ' tiptap-columns--' + layout;
        }
        return [
            'div',
            mergeAttributes({
                class: classes,
                'data-column-layout': '',
                'data-columns': cols,
                'data-layout': layout,
            }, props.HTMLAttributes),
            0,
        ];
    },

    addCommands: function() {
        var self = this;
        return {
            setColumns: function(columns) {
                columns = columns || 2;
                return function(props) {
                    var content = [];
                    for (var i = 0; i < columns; i++) {
                        content.push({
                            type: 'column',
                            content: [{ type: 'paragraph' }],
                        });
                    }
                    return props.commands.insertContent({
                        type: self.name,
                        attrs: { columns: columns, layout: 'equal' },
                        content: content,
                    });
                };
            },
            setColumnLayout: function(columns, layout) {
                columns = columns || 2;
                layout = layout || 'equal';
                return function(props) {
                    var content = [];
                    for (var i = 0; i < columns; i++) {
                        content.push({
                            type: 'column',
                            content: [{ type: 'paragraph' }],
                        });
                    }
                    return props.commands.insertContent({
                        type: self.name,
                        attrs: { columns: columns, layout: layout },
                        content: content,
                    });
                };
            },
            updateColumnCount: function(columns) {
                return function(props) {
                    var state = props.state;
                    var selection = state.selection;
                    var layoutNode = null;
                    var layoutPos = null;

                    // Find the columnLayout node that contains the selection
                    state.doc.nodesBetween(selection.from, selection.to, function(node, pos) {
                        if (node.type.name === 'columnLayout') {
                            layoutNode = node;
                            layoutPos = pos;
                            return false;
                        }
                    });

                    if (layoutNode && layoutPos !== null) {
                        var currentColumns = layoutNode.childCount;
                        var newAttrs = { ...layoutNode.attrs, columns: columns };

                        if (columns > currentColumns) {
                            // Add more columns
                            var newContent = [];
                            layoutNode.forEach(function(child) {
                                newContent.push(child.toJSON());
                            });
                            for (var i = currentColumns; i < columns; i++) {
                                newContent.push({
                                    type: 'column',
                                    content: [{ type: 'paragraph' }],
                                });
                            }
                            return props.chain()
                                .command(function(cmdProps) {
                                    cmdProps.tr.replaceWith(
                                        layoutPos,
                                        layoutPos + layoutNode.nodeSize,
                                        props.editor.schema.nodeFromJSON({
                                            type: 'columnLayout',
                                            attrs: newAttrs,
                                            content: newContent,
                                        })
                                    );
                                    return true;
                                })
                                .run();
                        } else if (columns < currentColumns) {
                            // Remove columns (keep content from removed columns in last column)
                            var keptContent = [];
                            var mergedContent = [];
                            var idx = 0;
                            layoutNode.forEach(function(child) {
                                if (idx < columns - 1) {
                                    keptContent.push(child.toJSON());
                                } else if (idx === columns - 1) {
                                    // Last column - merge content from remaining columns
                                    var lastColumnContent = [];
                                    child.forEach(function(c) {
                                        lastColumnContent.push(c.toJSON());
                                    });
                                    mergedContent = lastColumnContent;
                                    keptContent.push({
                                        type: 'column',
                                        content: mergedContent,
                                    });
                                } else {
                                    // Merge content from removed columns
                                    child.forEach(function(c) {
                                        mergedContent.push(c.toJSON());
                                    });
                                    keptContent[columns - 1].content = mergedContent;
                                }
                                idx++;
                            });
                            return props.chain()
                                .command(function(cmdProps) {
                                    cmdProps.tr.replaceWith(
                                        layoutPos,
                                        layoutPos + layoutNode.nodeSize,
                                        props.editor.schema.nodeFromJSON({
                                            type: 'columnLayout',
                                            attrs: newAttrs,
                                            content: keptContent,
                                        })
                                    );
                                    return true;
                                })
                                .run();
                        } else {
                            // Same count, just update attrs
                            return props.chain()
                                .command(function(cmdProps) {
                                    cmdProps.tr.setNodeMarkup(layoutPos, undefined, newAttrs);
                                    return true;
                                })
                                .run();
                        }
                    }
                    return false;
                };
            },
            /**
             * Delete column layout by walking up from current selection.
             * Extracts content from all columns back into regular paragraphs.
             */
            deleteColumnLayout: function() {
                return function(props) {
                    var state = props.state;
                    var $pos = state.selection.$from;
                    var layoutNode = null;
                    var layoutPos = null;

                    // Walk up the resolved position to find columnLayout
                    for (var d = $pos.depth; d >= 0; d--) {
                        var node = $pos.node(d);
                        if (node.type.name === 'columnLayout') {
                            layoutNode = node;
                            layoutPos = $pos.before(d);
                            break;
                        }
                    }

                    if (layoutNode && layoutPos !== null) {
                        return deleteColumnLayoutAtPos(props, layoutNode, layoutPos);
                    }
                    return false;
                };
            },
            /**
             * Delete column layout at a known document position (used by NodeView button).
             */
            deleteColumnLayoutAt: function(pos) {
                return function(props) {
                    var node = props.state.doc.nodeAt(pos);
                    if (node && node.type.name === 'columnLayout') {
                        return deleteColumnLayoutAtPos(props, node, pos);
                    }
                    return false;
                };
            },
        };
    },

    addNodeView: function() {
        return function(viewProps) {
            var node = viewProps.node;
            var editor = viewProps.editor;
            var getPos = viewProps.getPos;
            var cols = node.attrs.columns || 2;
            var layout = node.attrs.layout || 'equal';

            // Wrapper with position:relative for toolbar positioning
            var dom = document.createElement('div');
            dom.className = 'tiptap-columns-wrapper';

            // Floating toolbar (hidden until hover/focus)
            var toolbar = document.createElement('div');
            toolbar.className = 'tiptap-columns-toolbar';
            toolbar.contentEditable = 'false';

            var removeBtn = document.createElement('button');
            removeBtn.className = 'tiptap-columns-toolbar__btn tiptap-columns-toolbar__remove';
            removeBtn.type = 'button';
            removeBtn.title = 'Remove columns';
            removeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
            removeBtn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var pos = getPos();
                if (pos != null) {
                    editor.commands.deleteColumnLayoutAt(pos);
                }
            });

            toolbar.appendChild(removeBtn);
            dom.appendChild(toolbar);

            // Content area — the actual CSS grid container
            var contentDOM = document.createElement('div');
            var classes = 'tiptap-columns tiptap-columns--' + cols;
            if (layout !== 'equal') {
                classes += ' tiptap-columns--' + layout;
            }
            contentDOM.className = classes;
            contentDOM.setAttribute('data-column-layout', '');
            contentDOM.setAttribute('data-columns', cols);
            contentDOM.setAttribute('data-layout', layout);
            dom.appendChild(contentDOM);

            return {
                dom: dom,
                contentDOM: contentDOM,
                update: function(updatedNode) {
                    if (updatedNode.type.name !== 'columnLayout') return false;
                    var newCols = updatedNode.attrs.columns || 2;
                    var newLayout = updatedNode.attrs.layout || 'equal';
                    var newClasses = 'tiptap-columns tiptap-columns--' + newCols;
                    if (newLayout !== 'equal') {
                        newClasses += ' tiptap-columns--' + newLayout;
                    }
                    contentDOM.className = newClasses;
                    contentDOM.setAttribute('data-columns', newCols);
                    contentDOM.setAttribute('data-layout', newLayout);
                    return true;
                },
            };
        };
    },

    addKeyboardShortcuts: function() {
        return {
            'Mod-Alt-2': function() {
                return this.editor.commands.setColumns(2);
            },
            'Mod-Alt-3': function() {
                return this.editor.commands.setColumns(3);
            },
            'Backspace': function() {
                var editor = this.editor;
                var state = editor.state;
                var selection = state.selection;

                // Only when cursor is collapsed (no selection range)
                if (!selection.empty) return false;

                // Walk up the node tree to find a columnLayout ancestor
                var $pos = selection.$from;
                for (var d = $pos.depth; d >= 0; d--) {
                    var node = $pos.node(d);
                    if (node.type.name === 'columnLayout') {
                        // Check if every column is empty (single empty paragraph)
                        var allEmpty = true;
                        node.forEach(function(column) {
                            if (column.childCount > 1 ||
                                (column.childCount === 1 && column.firstChild && column.firstChild.textContent !== '')) {
                                allEmpty = false;
                            }
                        });
                        if (allEmpty) {
                            return editor.commands.deleteColumnLayout();
                        }
                        return false;
                    }
                }
                return false;
            },
        };
    },
});

/**
 * Column Node - Individual column within a layout
 */
var Column = Node.create({
    name: 'column',
    group: 'column',
    content: 'block+',
    defining: true,
    isolating: true,

    parseHTML: function() {
        return [
            { tag: 'div[data-column]' },
            { tag: 'div.tiptap-column' },
        ];
    },

    renderHTML: function(props) {
        return [
            'div',
            mergeAttributes({
                class: 'tiptap-column',
                'data-column': '',
            }, props.HTMLAttributes),
            0,
        ];
    },

    addNodeView: function() {
        return function(props) {
            var dom = document.createElement('div');
            dom.className = 'tiptap-column';
            dom.setAttribute('data-column', '');

            var contentDOM = document.createElement('div');
            contentDOM.className = 'tiptap-column__content';
            dom.appendChild(contentDOM);

            return {
                dom: dom,
                contentDOM: contentDOM,
            };
        };
    },
});

/**
 * Create the Columns extension bundle
 */
function createColumnsExtension() {
    return [ColumnLayout, Column];
}

module.exports = {
    createColumnsExtension: createColumnsExtension,
    ColumnLayout: ColumnLayout,
    Column: Column,
};
