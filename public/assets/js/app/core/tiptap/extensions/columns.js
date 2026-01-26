/**
 * Column Layouts Extension for Tiptap
 *
 * Enables creating multi-column layouts (2, 3, or 4 columns).
 * Custom implementation for responsive grid-based layouts.
 *
 * @module tiptap/extensions/columns
 */

const { Node, mergeAttributes } = require('@tiptap/core');

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
        return [
            'div',
            mergeAttributes({
                class: 'tiptap-columns tiptap-columns--' + cols,
                'data-column-layout': '',
                'data-columns': cols,
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
                        attrs: { columns: columns },
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
            deleteColumnLayout: function() {
                return function(props) {
                    var state = props.state;
                    var selection = state.selection;
                    var layoutNode = null;
                    var layoutPos = null;

                    state.doc.nodesBetween(selection.from, selection.to, function(node, pos) {
                        if (node.type.name === 'columnLayout') {
                            layoutNode = node;
                            layoutPos = pos;
                            return false;
                        }
                    });

                    if (layoutNode && layoutPos !== null) {
                        // Extract content from all columns and insert as regular paragraphs
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
                                cmdProps.tr.replaceWith(layoutPos, layoutPos + layoutNode.nodeSize, nodes);
                                return true;
                            })
                            .run();
                    }
                    return false;
                };
            },
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
