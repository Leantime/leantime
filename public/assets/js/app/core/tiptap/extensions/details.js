/**
 * Details/Collapsible Extension for Tiptap
 *
 * Enables creating collapsible sections using HTML5 details/summary elements.
 * Custom implementation compatible with Tiptap v2.
 *
 * @module tiptap/extensions/details
 */

const { Node, mergeAttributes } = require('@tiptap/core');

/**
 * Details Node - The container for collapsible content
 */
var Details = Node.create({
    name: 'details',
    group: 'block',
    content: 'detailsSummary detailsContent',
    defining: true,

    addAttributes: function() {
        return {
            open: {
                default: true,
                parseHTML: function(element) {
                    return element.hasAttribute('open');
                },
                renderHTML: function(attributes) {
                    if (attributes.open) {
                        return { open: 'open' };
                    }
                    return {};
                },
            },
        };
    },

    parseHTML: function() {
        return [
            { tag: 'details' },
        ];
    },

    renderHTML: function(props) {
        return [
            'details',
            mergeAttributes({ class: 'tiptap-details' }, props.HTMLAttributes),
            0,
        ];
    },

    addNodeView: function() {
        return function(props) {
            var node = props.node;

            var dom = document.createElement('details');
            dom.className = 'tiptap-details';

            // Set initial open state
            if (node.attrs.open) {
                dom.setAttribute('open', 'open');
            }

            var contentDOM = dom;

            return {
                dom: dom,
                contentDOM: contentDOM,
                update: function(updatedNode) {
                    if (updatedNode.type.name !== 'details') {
                        return false;
                    }

                    // Update open state
                    if (updatedNode.attrs.open) {
                        dom.setAttribute('open', 'open');
                    } else {
                        dom.removeAttribute('open');
                    }

                    return true;
                },
            };
        };
    },

    addCommands: function() {
        var self = this;
        return {
            setDetails: function() {
                return function(props) {
                    return props.commands.insertContent({
                        type: self.name,
                        attrs: { open: true },
                        content: [
                            {
                                type: 'detailsSummary',
                                content: [
                                    {
                                        type: 'text',
                                        text: 'Click to expand',
                                    },
                                ],
                            },
                            {
                                type: 'detailsContent',
                                content: [
                                    {
                                        type: 'paragraph',
                                    },
                                ],
                            },
                        ],
                    });
                };
            },
            toggleDetails: function() {
                return function(props) {
                    var state = props.state;
                    var selection = state.selection;
                    var detailsNode = null;
                    var detailsPos = null;

                    // Find the details node that contains the selection
                    state.doc.nodesBetween(selection.from, selection.to, function(node, pos) {
                        if (node.type.name === 'details') {
                            detailsNode = node;
                            detailsPos = pos;
                            return false;
                        }
                    });

                    if (detailsNode && detailsPos !== null) {
                        return props.chain().command(function(cmdProps) {
                            cmdProps.tr.setNodeMarkup(detailsPos, undefined, {
                                ...detailsNode.attrs,
                                open: !detailsNode.attrs.open,
                            });
                            return true;
                        }).run();
                    }

                    return false;
                };
            },
            unsetDetails: function() {
                return function(props) {
                    return props.commands.lift('details');
                };
            },
        };
    },

    addKeyboardShortcuts: function() {
        return {
            'Mod-Alt-d': function() {
                return this.editor.commands.setDetails();
            },
            // Allow Mod-Enter to exit details and create paragraph after
            'Mod-Enter': function() {
                var editor = this.editor;
                var state = editor.state;
                var selection = state.selection;

                // Check if we're inside a details node
                var detailsNode = null;
                var detailsPos = null;

                state.doc.nodesBetween(selection.from, selection.to, function(node, pos) {
                    if (node.type.name === 'details') {
                        detailsNode = node;
                        detailsPos = pos;
                    }
                });

                if (detailsNode && detailsPos !== null) {
                    // Insert paragraph after details
                    var endPos = detailsPos + detailsNode.nodeSize;
                    return editor.chain()
                        .insertContentAt(endPos, { type: 'paragraph' })
                        .focus(endPos + 1)
                        .run();
                }

                return false;
            },
        };
    },
});

/**
 * Details Summary Node - The clickable header
 */
var DetailsSummary = Node.create({
    name: 'detailsSummary',
    group: 'detailsSummary',
    content: 'inline*',
    defining: true,

    parseHTML: function() {
        return [
            { tag: 'summary' },
        ];
    },

    renderHTML: function(props) {
        return [
            'summary',
            mergeAttributes({ class: 'tiptap-details__summary' }, props.HTMLAttributes),
            0,
        ];
    },

    addNodeView: function() {
        return function(props) {
            var editor = props.editor;
            var getPos = props.getPos;

            var dom = document.createElement('summary');
            dom.className = 'tiptap-details__summary';

            // Create arrow icon that triggers toggle
            var arrow = document.createElement('span');
            arrow.className = 'tiptap-details__arrow';
            dom.appendChild(arrow);

            var contentDOM = document.createElement('span');
            contentDOM.className = 'tiptap-details__summary-content';
            dom.appendChild(contentDOM);

            // Create delete button
            var deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'tiptap-details__delete-btn';
            deleteBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
            deleteBtn.title = 'Delete collapsible section';
            dom.appendChild(deleteBtn);

            // Handle click on the arrow to toggle
            arrow.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Find the details node and toggle it via the editor
                if (typeof getPos === 'function') {
                    var pos = getPos();
                    // Find parent details node
                    var resolved = editor.state.doc.resolve(pos);
                    for (var depth = resolved.depth; depth >= 0; depth--) {
                        var node = resolved.node(depth);
                        if (node.type.name === 'details') {
                            var detailsPos = resolved.before(depth);
                            editor.chain().focus().command(function(cmdProps) {
                                cmdProps.tr.setNodeMarkup(detailsPos, undefined, {
                                    ...node.attrs,
                                    open: !node.attrs.open,
                                });
                                return true;
                            }).run();
                            break;
                        }
                    }
                }
            });

            // Handle delete button click
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (typeof getPos === 'function') {
                    var pos = getPos();
                    // Find parent details node and delete it
                    var resolved = editor.state.doc.resolve(pos);
                    for (var depth = resolved.depth; depth >= 0; depth--) {
                        var node = resolved.node(depth);
                        if (node.type.name === 'details') {
                            var detailsPos = resolved.before(depth);
                            editor.chain().focus().command(function(cmdProps) {
                                cmdProps.tr.delete(detailsPos, detailsPos + node.nodeSize);
                                return true;
                            }).run();
                            break;
                        }
                    }
                }
            });

            // Also handle clicking the summary background (not the text)
            dom.addEventListener('click', function(e) {
                // If click was on the dom itself (not arrow, not contentDOM text, not delete button)
                if (e.target === dom) {
                    e.preventDefault();
                    arrow.click(); // Trigger the arrow click
                }
            });

            return {
                dom: dom,
                contentDOM: contentDOM,
                stopEvent: function(event) {
                    return event.target === deleteBtn || deleteBtn.contains(event.target);
                },
            };
        };
    },
});

/**
 * Details Content Node - The collapsible content area
 */
var DetailsContent = Node.create({
    name: 'detailsContent',
    group: 'detailsContent',
    content: 'block+',

    parseHTML: function() {
        return [
            { tag: 'div.tiptap-details__content' },
            // Fallback for content after summary that isn't wrapped
            {
                tag: 'details > *:not(summary)',
                getAttrs: function(dom) {
                    // Only match direct children of details that aren't summary
                    if (dom.parentElement && dom.parentElement.tagName === 'DETAILS') {
                        return {};
                    }
                    return false;
                },
            },
        ];
    },

    renderHTML: function(props) {
        return [
            'div',
            mergeAttributes({ class: 'tiptap-details__content' }, props.HTMLAttributes),
            0,
        ];
    },
});

/**
 * Create the Details extension bundle
 * Returns an array of all three nodes needed
 */
function createDetailsExtension() {
    return [Details, DetailsSummary, DetailsContent];
}

module.exports = {
    createDetailsExtension: createDetailsExtension,
    Details: Details,
    DetailsSummary: DetailsSummary,
    DetailsContent: DetailsContent,
};
