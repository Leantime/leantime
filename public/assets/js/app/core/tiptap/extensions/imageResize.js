/**
 * Resizable Image Extension for Tiptap
 *
 * Extends @tiptap/extension-image with drag-to-resize handles.
 * Adds a `width` attribute to image nodes and wraps them in a
 * resize container with a corner drag handle when selected.
 *
 * This is a custom replacement for the broken `tiptap-extension-resize-image`
 * npm package (its package.json declares "type":"module" but ships CJS,
 * causing webpack to fail with "exports is not defined").
 *
 * @module tiptap/extensions/imageResize
 */

/**
 * Creates a resizable image extension by extending the base Image extension.
 *
 * @param {Object} Image - The @tiptap/extension-image default export
 * @returns {Object} Extended TipTap Image node with resize support
 */
export function createResizableImage(Image) {

    return Image.extend({
        name: 'image',

        addAttributes: function() {
            return Object.assign({}, this.parent ? this.parent() : {}, {
                width: {
                    default: null,
                    parseHTML: function(element) {
                        // Read from style or attribute
                        return element.getAttribute('width') ||
                               element.style.width ||
                               null;
                    },
                    renderHTML: function(attributes) {
                        if (!attributes.width) {
                            return {};
                        }
                        return {
                            width: attributes.width,
                            style: 'width: ' + attributes.width + (String(attributes.width).match(/\d$/) ? 'px' : ''),
                        };
                    },
                },
            });
        },

        addNodeView: function() {
            return function(props) {
                var node = props.node;
                var getPos = props.getPos;
                var editor = props.editor;

                // Outer container
                var container = document.createElement('div');
                container.className = 'image-resizer';
                container.style.display = 'inline-block';
                container.style.position = 'relative';
                container.style.lineHeight = '0';
                container.style.maxWidth = '100%';
                if (node.attrs.width) {
                    var w = String(node.attrs.width);
                    container.style.width = w + (w.match(/\d$/) ? 'px' : '');
                }

                // Image element
                var img = document.createElement('img');
                img.src = node.attrs.src || '';
                img.alt = node.attrs.alt || '';
                if (node.attrs.title) img.title = node.attrs.title;
                img.style.width = '100%';
                img.style.display = 'block';
                img.draggable = false;
                container.appendChild(img);

                // Resize handle (bottom-right corner)
                var handle = document.createElement('div');
                handle.className = 'resize-trigger';
                handle.style.cssText = 'position:absolute;right:-4px;bottom:-4px;width:10px;height:10px;' +
                    'background:var(--primary-color,#5a67d8);border:2px solid #fff;border-radius:50%;' +
                    'cursor:se-resize;z-index:10;display:none;';
                container.appendChild(handle);

                // Show handle only when selected
                var selected = false;
                function updateSelection(isSelected) {
                    selected = isSelected;
                    handle.style.display = isSelected ? 'block' : 'none';
                    container.style.outline = isSelected ? '2px solid var(--primary-color,#5a67d8)' : 'none';
                    container.style.outlineOffset = isSelected ? '2px' : '0';
                }

                // Drag resize logic
                handle.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var startX = e.clientX;
                    var startWidth = container.offsetWidth;

                    function onMouseMove(e) {
                        var newWidth = Math.max(50, startWidth + (e.clientX - startX));
                        container.style.width = newWidth + 'px';
                        img.style.width = '100%';
                    }

                    function onMouseUp() {
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);

                        // Commit the new width to the document model
                        var pos = getPos();
                        if (typeof pos === 'number') {
                            var newWidth = container.offsetWidth;
                            editor.chain()
                                .command(function(cmdProps) {
                                    cmdProps.tr.setNodeMarkup(pos, undefined, Object.assign(
                                        {}, node.attrs, { width: newWidth }
                                    ));
                                    return true;
                                })
                                .run();
                        }
                    }

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });

                return {
                    dom: container,
                    update: function(updatedNode) {
                        if (updatedNode.type.name !== 'image') return false;
                        node = updatedNode;

                        img.src = updatedNode.attrs.src || '';
                        img.alt = updatedNode.attrs.alt || '';
                        if (updatedNode.attrs.title) img.title = updatedNode.attrs.title;
                        if (updatedNode.attrs.width) {
                            var w = String(updatedNode.attrs.width);
                            container.style.width = w + (w.match(/\d$/) ? 'px' : '');
                        }
                        return true;
                    },
                    selectNode: function() { updateSelection(true); },
                    deselectNode: function() { updateSelection(false); },
                    destroy: function() {
                        // Cleanup handled by GC
                    },
                };
            };
        },
    });
}

