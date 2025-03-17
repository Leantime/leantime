(function($) {

    $.fn.nestedSortable = function(options) {

        const nestingRules = {
            // Define what can be nested under each type
            allowedChildren: {
                'root': ['section'],
                'section': ['milestone', 'task'],
                'milestone': ['milestone', 'task'],
                'task': ['task', 'subtask'],
                'subtask': ['subtask']
            },

            // Define where each type can be nested
            allowedParents: {
                'section': ['root'],
                'milestone': ['section', 'milestone'],
                'task': ['section', 'milestone', 'task'],
                'subtask': ['task', 'subtask']
            }
        };

        let dragInProgress = false;

        const dragState = {
            lastMouseX: 0,
            initialMouseX: 0,
            lastMouseY: 0,
            initialMouseY: 0,
            horizontalThreshold: 30, // Pixels to move horizontally before changing level
            currentLevel: 0,
            startLevel: 0,
            targetContainer: null,
            currentIndent: 0,
            intent: null,
            maxIndent: 3, // Maximum indent level
            horizontalDirection: 0, // -1 for left, 1 for right
            moveToRoot: false, // Flag to indicate if item should move to root
            bottomThreshold: 25, // Pixels from bottom of container to trigger root move
            rootContainer: null, // Reference to the root container
            calendarDrag: false, // Flag to indicate if dragging to calendar
            pomodoroTargeted: false, // Flag to indicate if dragging to pomodoro timer
            bottomIndicatorVisible: false, // Flag to track if bottom indicator is visible
            animationFrame: null,
            itemType: null, // Type of the item being dragged
            startParent: null, // Original parent of the dragged item
            validDropTarget: true, // Flag to track if current drop target is valid
            hasErrors: false,
            externalDropInProgress: false,
            isNewLevel: false,
            lastDragMovementTime: 0, // Track last time drag movement was processed
            debounceDelay: 50, // Delay in milliseconds for debouncing
        };

        function debounce(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }



        // Function to check if nesting is allowed
        function isNestingAllowed(currentItem, targetItem, ignoreProjectCheck = false) {

            const targetContainerType = getContainerType(targetItem);
            const currentItemType = getItemType(currentItem);

            // Prevent circular references
            if (isCircularReference(currentItem, targetItem)) {
                dragState.nestingErrorType = 'circular';
                return false;
            }

            if(ignoreProjectCheck === false) {

                const currentProject = getItemProject(currentItem);
                let targetProject = getItemProject(targetItem);

                if(typeof targetProject === 'undefined') {
                    targetProject = jQuery(targetItem).parent().data("project");
                }

                if(!isProjectAllowed(currentProject, targetProject)){
                    dragState.nestingErrorType = 'project';
                    return false;
                }
            }

            //const targetProject = dragState.targetContainer.data("project");

            if(!isHierarchyAllowed(currentItemType, targetContainerType)){
                dragState.nestingErrorType = 'types';
                return false;
            }

            return true;

        }

        function isHierarchyAllowed(itemType, containerType) {
            return nestingRules.allowedParents[itemType]?.includes(containerType) === true;
        }

        function isProjectAllowed(fromProjectId, toProjectId) {
            //If the targets container project id is not defined we are most likely in thge root
            if(typeof toProjectId === 'undefined') {
                return true;
            }

            return fromProjectId === toProjectId;
        }

        // New function to check for circular references
        function isCircularReference(draggedItem, targetContainer) {
            if (!draggedItem || !targetContainer) {
                return false;
            }

            // Check if target is a descendant of dragged item
            let current = $(targetContainer);
            while (current.length) {
                if (current.is(draggedItem)) {
                    return true;
                }
                current = current.parent().closest('.sortable-item');
            }

            return false;
        }

        // Function to get container type
        function getContainerType(container) {

            //container can be list or item

            // Check if the container itself has a container-type data attribute
            const containerType = jQuery(container).data('containerType');
            if (containerType) {
                return containerType;
            }

            //Check if the container is an item and has item type
            const itemType = jQuery(container).data('itemType');
            if (itemType) {
                return itemType;
            }

            // Check the parent of the thing
            const parentItem = jQuery(container).closest('.sortable-item');
            if (parentItem.length > 0) {
                return parentItem.data('itemType');
            }

            // Fallback to root
            return 'root';
        }

        // Function to get item type
        function getItemType(item) {

            if (!item || item.length === 0) {
                //console.warn("Attempted to get type of non-existent item");
                return null;
            }

            return jQuery(item).data('itemType');
        }

        function getItemProject(item) {

            if (!item || item.length === 0) {
                //console.warn("Attempted to get project of non-existent item");
                return null;
            }

            return jQuery(item).data('project');
        }

        function findPreviousElement(element) {
            // Check if there's a previous sibling
            var prevSibling = element.prev(".sortable-item");

            if (prevSibling.length) {

                // If the previous sibling has nested items, get the deepest last one
                var deepestNested = findDeepestNestedItem(prevSibling);

                return deepestNested || prevSibling;
            }

            // If no previous sibling, go up to parent and try again
            var parentList = element.parent(".sortable-list");
            var parentItem = parentList.parent(".sortable-item");

            if (parentItem.length) {
                return parentItem;
            }

            return $(); // Empty jQuery object if nothing found
        }

        // Find the deepest nested item within an element
        function findDeepestNestedItem(element) {
            var nestedList = element.find("> .sortable-list");
            if (nestedList.length) {
                var lastNestedItem = nestedList.children(".sortable-item").last();
                if (lastNestedItem.length) {
                    var deeperNested = findDeepestNestedItem(lastNestedItem);
                    return deeperNested.length ? deeperNested : lastNestedItem;
                }
            }
            return element;
        }

        // Reset all drag state variables
        function resetDragState() {
            Object.assign(dragState, {
                lastMouseX: 0,
                initialMouseX: 0,
                lastMouseY: 0,
                initialMouseY: 0,
                horizontalDirection: 0,
                currentIndent: 0,
                targetContainer: null,
                startLevel: 0,
                currentLevel: 0,
                lastIndentChange: 0,
                intent: null,
                item: null,
                itemType: null,
                itemProject: null,
                itemAbove: null,
                startParent: null,
                moveToRoot: false,
                calendarDrag: false,
                pomodoroTargeted: false,
                item: null,
                bottomIndicatorVisible: false,
                hasErrors: false,
                externalDropInProgress: false,
                isNewLevel: false,
                nestingErrorType: ''
            });

            jQuery('.highlight-drop').removeClass('highlight-drop');
            jQuery('.highlight-drop-error').removeClass('highlight-drop-error');
            jQuery('.pomodoroDrop').removeClass('pomodoro-drop-target');
        }

        //Initialize drag state with current ui and event object
        function initDragState(event, ui) {
            dragState.lastMouseX = event.pageX;
            dragState.initialMouseX = event.pageX;
            dragState.lastMouseY = event.pageY;
            dragState.initialMouseY = event.pageY;
            dragState.horizontalDirection = 0;
            dragState.intent = null;

            // ?? figure out why both
            dragState.currentIndent = ui.item.parents('.sortable-list').length - 1;
            dragState.startLevel = ui.item.parents('.sortable-list').length;
            dragState.currentLevel = dragState.startLevel;

            //Time elapsed since last event change
            dragState.lastIndentChange = 0;

            //Current Element
            dragState.item  = ui.item;
            dragState.itemType = getItemType(ui.item);
            dragState.itemProject = getItemProject(ui.item);
            dragState.itemAbove = findPreviousElement(ui.item);

            //Target Container
            dragState.targetContainer = event.target;

            dragState.rootContainer = jQuery('.sortable-list').first();
            dragState.startParent = ui.item.parent();

            dragState.moveToRoot = false;

            dragState.calendarDrag = false;
            dragState.pomodoroTargeted = false;
            dragState.externalDropInProgress = false;

            dragState.bottomIndicatorVisible = false;

            dragState.nestingErrorType = "";
        }

        function isDragOut(currentMouseX, currentMouseY) {

            // Check if we're dragging towards the calendar
            const calendarEl = jQuery('.minCalendarWrapper');

            if (calendarEl.length) {
                const calendarRect = calendarEl[0].getBoundingClientRect();
                // If we're moving towards the calendar, flag it
                if (currentMouseX > calendarRect.left - 10 && currentMouseY > calendarRect.top - 10 &&
                    currentMouseX < calendarRect.right + 10 && currentMouseY < calendarRect.bottom + 10) {
                    dragState.calendarDrag = true;

                    return true; // Exit early to let calendar handle the drag
                }
            }

            // Check if we're dragging towards the pomodoro timer
            const pomodoroEl = jQuery('.pomodoroDrop');





            if (pomodoroEl.length) {
                const pomodoroRect = pomodoroEl[0].getBoundingClientRect();


                // If we're moving towards the pomodoro timer, flag it - use more generous boundaries
                if (currentMouseX > pomodoroRect.left +10 && currentMouseY > pomodoroRect.top + 10 &&
                    currentMouseX < pomodoroRect.right - 10 && currentMouseY < pomodoroRect.bottom -10) {
                    dragState.pomodoroTargeted = true;
                    dragState.externalDropInProgress = true;

                    jQuery(pomodoroEl).addClass('pomodoro-drop-target');

                    return true; // Exit early to let pomodoro handle the drag
                }
                jQuery(pomodoroEl).removeClass('pomodoro-drop-target');
            }

            return false;

        }



        // Constants for angle thresholds
        const INTENT_THRESHOLDS = {
            VERTICAL: 10,         // Degrees from pure vertical (0°/180°)
            DIAGONAL: 70,         // Degrees from diagonal (45°/135°/225°/315°)
            HORIZONTAL: 10,       // Degrees from pure horizontal (90°/270°)
            DEAD_ZONE: 0,         // Dead zone around boundaries to prevent accidental triggering
            MIN_DISTANCE: 5,     // Minimum distance (px) before intent is detected
            HORIZONTAL_THRESHOLD: 5  // Horizontal distance (px) before diagonal/horizontal is detected
        };

// Intent types
        const INTENT = {
            NEST: 'nest',
            REORDER_UP: 'reorder-up',
            REORDER_DOWN: 'reorder-down',
            NEST_UNDER_PREV: 'nest-under-previous',
            NEST_UNDER_NEXT: 'nest-under-next',
            UNNEST: 'unnest',
            UNNEST_AND_DOWN: 'unnest-and-down',
            UNNEST_FROM_PREV: 'unnest-from-previous',
            EXPAND_COLLAPSE: 'expand-collapse',
            NOT_MOVED: 'not-moved',
            NONE: 'none'
        };

        /**
         * Determines user intent based on mouse movement
         * @param {number} startX - Starting X coordinate
         * @param {number} startY - Starting Y coordinate
         * @param {number} currentX - Current X coordinate
         * @param {number} currentY - Current Y coordinate
         * @returns {Object} Intent object with type and additional metadata
         */
        function determineUserIntent(startX, startY, currentX, currentY) {
            // Calculate distance and basic vectors
            const dx = currentX - startX;
            const dy = currentY - startY;
            const distance = Math.sqrt(dx * dx + dy * dy);

            // If we haven't moved enough, no intent is detected yet
            if (distance < INTENT_THRESHOLDS.MIN_DISTANCE) {

                return { type: INTENT.NOT_MOVED, angle: 0, distance };
            }

            // Calculate angle in degrees (0° is up, 90° is right, etc.)
            // Math.atan2 returns radians from -π to π, with 0 at "right"
            // We convert to degrees and adjust so 0° is "up"
            let angle = Math.atan2(dx, -dy) * (180 / Math.PI);
            if (angle < 0) angle += 360; // Convert to 0-360° range

            // Calculate horizontal distance (absolute)
            const horizontalDistance = Math.abs(dx);

            // Create result object with metadata
            const result = {
                type: INTENT.NONE,
                angle,
                distance,
                horizontalDistance,
                verticalDistance: Math.abs(dy),
                dx,
                dy
            };

            // If horizontal distance is below threshold, only allow vertical intents
            if (horizontalDistance < INTENT_THRESHOLDS.HORIZONTAL_THRESHOLD) {
                //console.log("not enough horizontal movement ");
                if (dy < 0) {
                    return { ...result, type: INTENT.REORDER_UP };
                } else {
                    return { ...result, type: INTENT.REORDER_DOWN };
                }
            }

            // Check for pure vertical movement (reordering)
            if (isWithinRange(angle, 0, INTENT_THRESHOLDS.VERTICAL) ||
                isWithinRange(angle, 180, INTENT_THRESHOLDS.VERTICAL)) {
                if (dy < 0) {
                    return { ...result, type: INTENT.REORDER_UP };
                } else {
                    return { ...result, type: INTENT.REORDER_DOWN };
                }
            }

            // Check for diagonal movement (nesting/unnesting)
            if (isWithinRange(angle, 45, INTENT_THRESHOLDS.DIAGONAL)) {
                return { ...result, type: INTENT.NEST };
            }

            if (isWithinRange(angle, 90, INTENT_THRESHOLDS.HORIZONTAL)) {
                return { ...result, type: INTENT.NEST };
            }

            if (isWithinRange(angle, 135, INTENT_THRESHOLDS.DIAGONAL)) {
                return { ...result, type: INTENT.NEST };
            }

            if (isWithinRange(angle, 225, INTENT_THRESHOLDS.DIAGONAL)) {
                return { ...result, type: INTENT.UNNEST };
            }

            if (isWithinRange(angle, 270, INTENT_THRESHOLDS.HORIZONTAL)) {
                return { ...result, type: INTENT.UNNEST };
            }

            if (isWithinRange(angle, 315, INTENT_THRESHOLDS.DIAGONAL)) {
                return { ...result, type: INTENT.UNNEST };
            }

            // If we get here, we're in an undefined area
            return result;
        }

        /**
         * Checks if an angle is within a range of another angle, accounting for dead zone
         * @param {number} angle - The angle to check
         * @param {number} target - The target angle
         * @param {number} range - The range around the target angle
         * @returns {boolean} True if angle is within range
         */
        function isWithinRange(angle, target, range) {
            const effectiveRange = range - INTENT_THRESHOLDS.DEAD_ZONE;

            //console.log("is", Math.abs(((angle - target + 180) % 360) - 180), " less than ", effectiveRange, "of", target, ": ", Math.abs(((angle - target + 180) % 360) - 180) <= effectiveRange);
            return Math.abs(((angle - target + 180) % 360) - 180) <= effectiveRange;
        }

        const debouncedDragMovement = debounce(function(event, ui) {
            const now = Date.now();
            // Only process if enough time has passed since last update
            if (now - dragState.lastDragMovementTime > dragState.debounceDelay) {
                dragState.lastDragMovementTime = now;
            }
        }, 50); // 50ms debounce time - adjust as needed

        function handleDragMovement(event, ui) {
                // Use the debounced version instead of direct execution


                // const intent = determineUserIntent(
                //     dragState.initialMouseX,
                //     dragState.initialMouseY,
                //     event.pageX,
                //     event.pageY
                // );

                //console.log("intent", intent.type);

                if (isDragOut(event.clientX, event.clientY) === true) {
                    return;
                }

                // updateVisualFeedback(intent, ui);

                //Taqrget list is
                let targetItem = ui.placeholder.parent(".sortable-list");

                // if (intent.type === INTENT.NEST) {
                //     targetItem = findPreviousElement(ui.placeholder);
                //     dragState.intent = intent.type;
                // } else {
                //     dragState.intent = null;
                // }

                let targetList = getTargetContainerList(targetItem);

                let targetCandidateType = getItemType(targetList);
                let targetCandidateProject = getItemProject(targetList);

                if (isNestingAllowed(ui.item, targetList) == false) {

                    jQuery('.highlight-drop').removeClass('highlight-drop');
                    jQuery('.highlight-drop-error').removeClass('highlight-drop-error');
                    jQuery(targetList).addClass('highlight-drop-error');
                    jQuery(targetList).addClass('highlight-drop-error');

                    //console.log("no nesting allowed");
                    dragState.targetContainer = null;
                    return
                }

                dragState.targetContainer = targetList;
                ui.helper.data('droppingTarget', dragState.targetContainer);

                jQuery('.highlight-drop').removeClass('highlight-drop');
                jQuery('.highlight-drop-error').removeClass('highlight-drop-error');
                jQuery(dragState.targetContainer).addClass('highlight-drop');
        }

        function createNestedContainerIfNeeded(targetItem) {
            if (!targetItem.length) return null;

            let nestedList = targetItem.find('> .sortable-list');
            if (nestedList.length === 0) {
                const itemType = getItemType(targetItem);
                targetItem.append('<div class="sortable-list"></div>');
                nestedList = targetItem.find('> .sortable-list');
                nestedList.data('containerType', itemType);
            }

            return nestedList;
        }

        function getTargetContainerList(targetItem) {
            let nestedList = {};
            let targetItemType = getItemType(targetItem);

            if(jQuery(targetItem).hasClass("sortable-list")) {
                nestedList = jQuery(targetItem);
                //dragState.isNewLevel = false;
            }else{
                nestedList = jQuery(targetItem).find('> .sortable-list').first();
                //
                //
                // if (nestedList.length === 0) {
                //     targetItem.append('<div class="sortable-list"></div>');
                //     nestedList = jQuery(targetItem).find('> .sortable-list').first();
                //     nestedList.data('containerType', targetItemType);
                //
                // }
                // dragState.isNewLevel = true;

            }

            if (!nestedList.data('containerType')) {
                nestedList.data('containerType', targetItemType);
            }

            return nestedList[0];
        }

        function updateVisualFeedback(intent, ui) {
            jQuery('.highlight-drop').removeClass('highlight-drop');

            if (intent.type === INTENT.NEST) {
                const prevItem = findPreviousElement(ui.placeholder);
                if (prevItem.length) {
                    prevItem.addClass('highlight-drop');
                }
            } else if (intent.type === INTENT.UNNEST) {
                ui.placeholder.closest('.sortable-list').parent().addClass('highlight-drop');
            }
        }



        function saveSorting() {
            const sortingData = [];

            // Recursively collect all items with their hierarchy
            function collectItems(container, parentId = null, level = 0) {
                container.children('.sortable-item').each(function (index) {
                    const $item = jQuery(this);
                    const itemId = $item.data('id');

                    // Add this item to the sorting data
                    sortingData.push({
                        id: itemId,
                        parentId: parentId,
                        parentType: getContainerType($item.parent()),
                        level: level,
                        order: index
                    });

                    // Process children if any
                    const $childContainer = $item.children('.sortable-list');
                    if ($childContainer.length) {
                        collectItems($childContainer, itemId, level + 1);
                    }
                });
            }

            // Start collecting from the root container
            jQuery('.sortable-list').first().each(function () {
                collectItems(jQuery(this));
            });

            // If we're in the middle of a calendar drag, don't save sorting
            if (dragState.calendarDrag) {
                return;
            }

            // Send the sorting data to the server
            if (sortingData.length > 0) {
                //console..log("Saving sorting data:", sortingData);
                htmx.ajax('POST', leantime.appUrl+'/hx/widgets/myToDos/saveSorting', {
                    target: '#htmx-indicator',
                    swap: 'none',
                    values: sortingData
                });
            }
        }

        sortableInstance = this.sortable({
            zIndex: 99999,
            appendTo: ".maincontent",
            items: '.sortable-item',
            connectWith: '.sortable-list',
            tolerance: "pointer",
            placeholder: "sortable-placeholder",
            forcePlaceholderSize: true,
            dropOnEmpty: true,
            revert: false,
            delay: 150, // Increased delay to allow calendar drag to initialize first
            distance: 10, // Minimum distance before drag starts
            scrollSensitivity: 40,
            scrollSpeed: 20,
            scroll: false,
            helper: "clone",
            appendTo: "body", // This ensures the helper


            start: function (event, ui) {
                // Store initial state
                ui.item.data('startPos', ui.item.index());
                const startParent = ui.item.parent();
                ui.item.data('startParent', startParent);

                // Store the item type
                //console..log("Drag started with item:", ui.item);
                //console..log("Item parent:", startParent);
                dragState.itemType = getItemType(ui.item);
                initDragState(event, ui);
                dragInProgress = true;
            },
            sort: function (event, ui) {

                handleDragMovement(event, ui);

                if (dragState.calendarDrag || dragState.pomodoroTargeted) {
                    // If we're dragging to the calendar or pomodoro timer, cancel sorting and reset drag state
                    jQuery(this).sortable('cancel');
                    resetDragState();
                    return false;
                }

            },
            stop: function (event, ui) {

                //Clean up
                // If we were targeting the pomodoro, don't save sorting
                if (dragState.pomodoroTargeted) {
                    resetDragState();
                    return;
                }

                if (dragState.hasErrors === false) {
                    // Save the new order
                    saveSorting();
                }

                resetDragState();
                dragInProgress = false;

                setTimeout(function () {
                    jQuery('.sortable-list').each(function () {
                        if (jQuery(this).children('.sortable-item').length === 0 &&
                            !jQuery(this).is('#yourToDoContainer > .sortable-list')) {
                            // Don't remove the root list
                            if (jQuery(this).parent().hasClass('sortable-item')) {

                                var ticketId = jQuery(this).parent().data("id");
                                jQuery(this).parent().find(".accordion-toggle").remove();
                                //jQuery(this).remove();
                            }
                        }
                    });
                }, 300);
            },
            beforeStop: function (event, ui) {
                dragInProgress = false;

                if (dragState.calendarDrag) {
                    return;
                }

                // Get the current container the item is being dropped into
                const currentContainer = ui.item.parent();

                if (!currentContainer.hasClass('sortable-list')) {
                    dragState.hasErrors = true;
                    //console.warn("Current container is not a sortable-list:", currentContainer);
                    return;
                }

                const containerType = getContainerType(currentContainer);
                let itemType = dragState.itemType;


                if (!isNestingAllowed(ui.item, dragState.targetContainer, true)) {
                    // Cancel the move if not allowed
                    //console..warn("Root nesting not allowed");
                    if(ui.item !== ui.item.data('startParent')) {
                        ui.item.appendTo(ui.item.data('startParent'));
                    }
                    dragState.hasErrors = true;

                    let message = "";
                    switch(dragState.nestingErrorType) {
                        case 'project':
                            message = "Can't nest elements from 2 different projects";
                            break;
                        case 'types':
                            message = "Can't nest these elements underneach each other";
                            break;
                        case 'circular':
                            message = "Can't nest element undneath itself";
                            break;
                        default:
                            message = "Nesting not allowed here";
                    }

                    jQuery.growl({message: message, style: "error"});

                    return;
                }

            },
            change: function (event, ui) {
                //console..log("doing the change");

                // Update placeholder class based on current indent level
                ui.placeholder.removeClass('indent-0 indent-1 indent-2 indent-3');
                ui.placeholder.addClass('indent-' + dragState.currentIndent);
            }
        });

    }
}( jQuery ));

