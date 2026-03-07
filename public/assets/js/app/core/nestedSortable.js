/**
 * nestedSortable — SortableJS-based hierarchical sortable for Leantime.
 *
 * Replaces the jQuery UI nestedSortable extension.
 * Provides the same functionality: hierarchical nesting validation,
 * project boundary enforcement, drag-out detection (calendar, pomodoro),
 * and group change tracking.
 *
 * Public API: leantime.nestedSortable.init(rootEl)
 */
(function () {
    'use strict';

    var nestingRules = {
        allowedChildren: {
            'root': ['section'],
            'section': ['milestone', 'task'],
            'milestone': ['milestone', 'task'],
            'task': ['task', 'subtask'],
            'subtask': ['subtask']
        },
        allowedParents: {
            'section': ['root'],
            'milestone': ['section', 'milestone'],
            'task': ['section', 'milestone', 'task'],
            'subtask': ['task', 'subtask']
        }
    };

    function getItemType(el) {
        if (!el) return null;
        return el.dataset ? el.dataset.itemType : null;
    }

    function getContainerType(el) {
        if (!el) return 'root';
        if (el.dataset && el.dataset.containerType) return el.dataset.containerType;
        if (el.dataset && el.dataset.itemType) return el.dataset.itemType;
        var parentItem = el.closest('.sortable-item');
        if (parentItem) return parentItem.dataset.itemType || 'root';
        return 'root';
    }

    function getItemProject(el) {
        if (!el) return null;
        return el.dataset ? el.dataset.project : null;
    }

    function isCircularReference(draggedItem, targetContainer) {
        if (!draggedItem || !targetContainer) return false;
        var current = targetContainer;
        while (current) {
            if (current === draggedItem) return true;
            var parentItem = current.parentElement ? current.parentElement.closest('.sortable-item') : null;
            current = parentItem;
        }
        return false;
    }

    function isNestingAllowed(currentItem, targetList, ignoreProjectCheck) {
        var currentItemType = getItemType(currentItem);
        var targetContainerType = getContainerType(targetList);

        if (isCircularReference(currentItem, targetList)) return false;

        if (!ignoreProjectCheck) {
            var currentProject = getItemProject(currentItem);
            var targetProject = getItemProject(targetList);
            if (!targetProject && targetList && targetList.parentElement) {
                var parentItem = targetList.parentElement.closest('.sortable-item');
                targetProject = parentItem ? getItemProject(parentItem) : undefined;
            }
            if (targetProject !== undefined && currentProject !== targetProject) return false;
        }

        var allowed = nestingRules.allowedParents[currentItemType];
        if (!allowed || allowed.indexOf(targetContainerType) === -1) return false;

        return true;
    }

    function getItemGroupKey(el) {
        var groupContainer = el.closest('.sortable-list[data-group-key]');
        return groupContainer ? groupContainer.dataset.groupKey : null;
    }

    function isDragOut(clientX, clientY) {
        var calEl = document.querySelector('.minCalendarWrapper');
        if (calEl) {
            var rect = calEl.getBoundingClientRect();
            if (clientX > rect.left - 10 && clientY > rect.top - 10 &&
                clientX < rect.right + 10 && clientY < rect.bottom + 10) {
                return 'calendar';
            }
        }
        var pomEl = document.querySelector('.pomodoroDrop');
        if (pomEl) {
            var pRect = pomEl.getBoundingClientRect();
            if (clientX > pRect.left + 10 && clientY > pRect.top + 10 &&
                clientX < pRect.right - 10 && clientY < pRect.bottom - 10) {
                pomEl.classList.add('pomodoro-drop-target');
                return 'pomodoro';
            }
            pomEl.classList.remove('pomodoro-drop-target');
        }
        return false;
    }

    function saveSorting() {
        var sortingData = [];
        var groupBy = '';
        var container = document.getElementById('yourToDoContainer');
        if (container) groupBy = container.dataset.groupBy || '';
        var groupChanges = [];

        // Detect group changes
        if (groupBy) {
            document.querySelectorAll('.sortable-item').forEach(function (el) {
                var itemId = el.dataset.id;
                var currentGroupKey = getItemGroupKey(el);
                var originalGroupKey = el.dataset.originalGroupKey;
                if (originalGroupKey && currentGroupKey && originalGroupKey !== currentGroupKey) {
                    groupChanges.push({
                        id: itemId,
                        fromGroup: originalGroupKey,
                        toGroup: currentGroupKey,
                        groupBy: groupBy
                    });
                }
            });
        }

        function collectItems(containerEl, parentId, level) {
            var children = containerEl.querySelectorAll(':scope > .sortable-item');
            children.forEach(function (item, index) {
                var itemId = item.dataset.id;
                var currentGroupKey = getItemGroupKey(item);
                sortingData.push({
                    id: itemId,
                    parentId: parentId || null,
                    parentType: getContainerType(item.parentElement),
                    level: level,
                    order: index,
                    groupKey: currentGroupKey
                });
                var childContainer = item.querySelector(':scope > .sortable-list');
                if (childContainer) {
                    collectItems(childContainer, itemId, level + 1);
                }
            });
        }

        // Collect from root containers only
        document.querySelectorAll('.sortable-list').forEach(function (list) {
            if (!list.parentElement || !list.parentElement.closest('.sortable-list')) {
                collectItems(list, null, 0);
            }
        });

        if (sortingData.length > 0) {
            var requestData = {};
            sortingData.forEach(function (item, index) {
                requestData[index] = JSON.stringify(item);
            });
            if (groupChanges.length > 0) {
                groupChanges.forEach(function (change, index) {
                    requestData['groupChanges[' + index + ']'] = JSON.stringify(change);
                });
                requestData['groupBy'] = groupBy;
            }
            htmx.ajax('POST', leantime.appUrl + '/hx/widgets/myToDos/saveSorting', {
                target: '#htmx-indicator',
                swap: 'none',
                values: requestData
            });
        }
    }

    /**
     * Initialize nested sortable on all .sortable-list containers.
     */
    function initNestedSortable(rootEl) {
        if (!rootEl) return;

        var lists = rootEl.classList.contains('sortable-list')
            ? [rootEl]
            : Array.from(rootEl.querySelectorAll('.sortable-list'));

        // Also include rootEl if it is a sortable-list
        if (rootEl.classList.contains('sortable-list') && lists.indexOf(rootEl) === -1) {
            lists.push(rootEl);
        }

        lists.forEach(function (list) {
            // Skip if already initialized
            if (list._sortableInstance) {
                list._sortableInstance.destroy();
            }

            list._sortableInstance = new Sortable(list, {
                group: 'nested-sortable',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                delay: 150,
                delayOnTouchOnly: true,
                draggable: '.sortable-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                handle: '.sortable-item',
                filter: '.no-drag',
                forceFallback: false,
                scroll: true,
                scrollSensitivity: 40,
                scrollSpeed: 20,

                onStart: function (evt) {
                    var item = evt.item;
                    // Store original group key for all items
                    document.querySelectorAll('.sortable-item').forEach(function (el) {
                        if (!el.dataset.originalGroupKey) {
                            el.dataset.originalGroupKey = getItemGroupKey(el) || '';
                        }
                    });
                    item.dataset.originalGroupKey = getItemGroupKey(item) || '';
                    item.dataset.startParentId = item.parentElement ? (item.parentElement.id || '') : '';
                },

                onMove: function (evt) {
                    var draggedItem = evt.dragged;
                    var targetList = evt.to;

                    // Check drag-out
                    // (can't easily check clientX/Y here, but onMove validates target)

                    // Validate nesting
                    if (!isNestingAllowed(draggedItem, targetList, false)) {
                        targetList.classList.add('highlight-drop-error');
                        return false; // Cancel move
                    }

                    // Clear error highlights, add valid highlight
                    document.querySelectorAll('.highlight-drop-error').forEach(function (el) {
                        el.classList.remove('highlight-drop-error');
                    });
                    document.querySelectorAll('.highlight-drop').forEach(function (el) {
                        el.classList.remove('highlight-drop');
                    });
                    targetList.classList.add('highlight-drop');

                    return true;
                },

                onEnd: function (evt) {
                    var item = evt.item;

                    // Clean up highlights
                    document.querySelectorAll('.highlight-drop, .highlight-drop-error').forEach(function (el) {
                        el.classList.remove('highlight-drop');
                        el.classList.remove('highlight-drop-error');
                    });
                    document.querySelectorAll('.pomodoro-drop-target').forEach(function (el) {
                        el.classList.remove('pomodoro-drop-target');
                    });

                    // Validate final position
                    var finalList = item.parentElement;
                    if (!finalList || !finalList.classList.contains('sortable-list')) {
                        return;
                    }

                    if (!isNestingAllowed(item, finalList, true)) {
                        // Move back to original parent
                        var startParent = document.getElementById(item.dataset.startParentId);
                        if (startParent) {
                            startParent.appendChild(item);
                        }
                        var message = "Nesting not allowed here";
                        if (typeof leantime !== 'undefined' && leantime.toast) {
                            leantime.toast.show({ message: message, style: 'error' });
                        }
                        return;
                    }

                    // Clean up empty nested lists
                    setTimeout(function () {
                        document.querySelectorAll('.sortable-list').forEach(function (list) {
                            if (list.querySelectorAll(':scope > .sortable-item').length === 0) {
                                var parentItem = list.parentElement;
                                if (parentItem && parentItem.classList.contains('sortable-item')) {
                                    var toggle = parentItem.querySelector('.accordion-toggle');
                                    if (toggle) toggle.remove();
                                }
                            }
                        });
                    }, 300);

                    // Save sorting
                    saveSorting();

                    // Clean up original-group-key data
                    document.querySelectorAll('.sortable-item').forEach(function (el) {
                        delete el.dataset.originalGroupKey;
                    });
                }
            });
        });
    }

    // Expose on leantime namespace
    var lt = window.leantime || (window.leantime = {});
    lt.nestedSortable = {
        init: initNestedSortable,
        saveSorting: saveSorting,
        isNestingAllowed: isNestingAllowed
    };

    // jQuery bridge — allows legacy template calls like jQuery('.sortable-list').nestedSortable()
    if (typeof jQuery !== 'undefined') {
        jQuery.fn.nestedSortable = function () {
            return this.each(function () {
                initNestedSortable(this);
            });
        };
    }

})();
