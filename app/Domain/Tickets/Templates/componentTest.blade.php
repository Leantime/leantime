<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swimlane Component Test</title>
    @vite(['resources/css/main.css'])
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .test-section h2 { margin-top: 0; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .component-demo { margin: 15px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        .component-label { font-weight: bold; color: #666; margin-bottom: 8px; }
    </style>
</head>
<body>
    <h1>Swimlane Row Header Components Test</h1>
    <p>Testing all new Blade components before integration</p>

    <!-- Icon Components Test -->
    <div class="test-section">
        <h2>1. Icon Components</h2>

        <div class="component-demo">
            <div class="component-label">ThermometerIcon (Priority)</div>
            <div style="display: flex; gap: 20px; align-items: center;">
                <div>
                    <small>Critical (1):</small>
                    <x-global::kanban.thermometer-icon :priority="1" />
                </div>
                <div>
                    <small>High (2):</small>
                    <x-global::kanban.thermometer-icon :priority="2" />
                </div>
                <div>
                    <small>Medium (3):</small>
                    <x-global::kanban.thermometer-icon :priority="3" />
                </div>
                <div>
                    <small>Low (4):</small>
                    <x-global::kanban.thermometer-icon :priority="4" />
                </div>
                <div>
                    <small>Lowest (5):</small>
                    <x-global::kanban.thermometer-icon :priority="5" />
                </div>
            </div>
            <div style="margin-top: 10px;">
                <small>With labels:</small><br>
                <x-global::kanban.thermometer-icon :priority="1" :showLabel="true" />
            </div>
        </div>

        <div class="component-demo">
            <div class="component-label">TShirtIcon (Effort)</div>
            <div style="display: flex; gap: 20px; align-items: center;">
                <x-global::kanban.tshirt-icon :effort="1" :showLabel="true" />
                <x-global::kanban.tshirt-icon :effort="2" :showLabel="true" />
                <x-global::kanban.tshirt-icon :effort="3" :showLabel="true" />
                <x-global::kanban.tshirt-icon :effort="5" :showLabel="true" />
                <x-global::kanban.tshirt-icon :effort="8" :showLabel="true" />
                <x-global::kanban.tshirt-icon :effort="13" :showLabel="true" />
            </div>
        </div>

        <div class="component-demo">
            <div class="component-label">UserAvatar (with consistent color generation)</div>
            <div style="display: flex; gap: 20px; align-items: center;">
                <div>
                    <small>Marcus Wells (SM):</small><br>
                    <x-global::kanban.user-avatar username="Marcus Wells" size="sm" />
                </div>
                <div>
                    <small>Sarah Connor (MD):</small><br>
                    <x-global::kanban.user-avatar username="Sarah Connor" size="md" />
                </div>
                <div>
                    <small>Unassigned (MD):</small><br>
                    <x-global::kanban.user-avatar username="Unassigned" size="md" />
                </div>
                <div>
                    <small>Bob Johnson (LG):</small><br>
                    <x-global::kanban.user-avatar username="Bob Johnson" size="lg" />
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                Note: Each username gets a consistent color. Same user = same color every time.
            </div>
        </div>

        <div class="component-demo">
            <div class="component-label">TimeIndicator</div>
            <div style="display: flex; gap: 20px; align-items: center;">
                <div>
                    <small>Due Soon:</small>
                    <x-global::kanban.time-indicator type="dueSoon" />
                </div>
                <div>
                    <small>Overdue:</small>
                    <x-global::kanban.time-indicator type="overdue" />
                </div>
                <div>
                    <small>Stale:</small>
                    <x-global::kanban.time-indicator type="stale" />
                </div>
            </div>
        </div>

        <div class="component-demo">
            <div class="component-label">Supporting Icons</div>
            <div style="display: flex; gap: 20px; align-items: center;">
                <div>
                    <small>Milestone 🎯:</small>
                    <x-global::kanban.milestone-icon label="Sprint 1" />
                </div>
                <div>
                    <small>Bug 🐛:</small>
                    <x-global::kanban.type-icon type="bug" />
                </div>
                <div>
                    <small>Feature ✨:</small>
                    <x-global::kanban.type-icon type="feature" />
                </div>
                <div>
                    <small>Sprint 🏃:</small>
                    <x-global::kanban.sprint-icon label="Sprint 2" />
                </div>
            </div>
        </div>
    </div>

    <!-- MicroProgressBar Test -->
    <div class="test-section">
        <h2>2. MicroProgressBar Component</h2>

        <div class="component-demo">
            <div class="component-label">Status Breakdown (50% New, 30% In Progress, 20% Done)</div>
            <x-global::kanban.micro-progress-bar
                :statusCounts="['3' => 5, '4' => 3, '5' => 2]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :totalCount="10"
            />
        </div>

        <div class="component-demo">
            <div class="component-label">Different Distribution (70% New, 20% In Progress, 10% Done)</div>
            <x-global::kanban.micro-progress-bar
                :statusCounts="['3' => 14, '4' => 4, '5' => 2]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :totalCount="20"
            />
        </div>

        <div class="component-demo">
            <div class="component-label">All Statuses</div>
            <x-global::kanban.micro-progress-bar
                :statusCounts="['1' => 2, '3' => 5, '4' => 3, '2' => 1, '5' => 4]"
                :statusColumns="['1' => 'Blocked', '3' => 'New', '4' => 'In Progress', '2' => 'Waiting', '5' => 'Done']"
                :totalCount="15"
            />
        </div>
    </div>

    <!-- Count Badge Variations -->
    <div class="test-section">
        <h2>3. CountBadge Color Variations</h2>

        <div class="component-demo">
            <div class="component-label">Small Count (1-9) - Light Olive</div>
            <x-global::kanban.swimlane-row-header
                groupBy="priority"
                :groupId="3"
                label="Medium"
                :totalCount="5"
                :statusCounts="['3' => 3, '4' => 1, '5' => 1]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
        </div>

        <div class="component-demo">
            <div class="component-label">Medium Count (10-99) - Medium Olive</div>
            <x-global::kanban.swimlane-row-header
                groupBy="priority"
                :groupId="2"
                label="High"
                :totalCount="12"
                :statusCounts="['3' => 5, '4' => 4, '5' => 3]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
        </div>

        <div class="component-demo">
            <div class="component-label">Large Count (100+) - Dark Olive</div>
            <x-global::kanban.swimlane-row-header
                groupBy="priority"
                :groupId="1"
                label="Critical"
                :totalCount="128"
                :statusCounts="['3' => 50, '4' => 48, '5' => 30]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
        </div>
    </div>

    <!-- SwimLaneRowHeader Test -->
    <div class="test-section">
        <h2>4. Complete SwimLaneRowHeader Components (Matching Design)</h2>

        <h3 style="color: #6B7A4D; margin-top: 20px;">EXPANDED vs COLLAPSED States</h3>
        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 30px;">
            <div>
                <small style="color: #666;">Expanded (shows progress bar)</small>
                <x-global::kanban.swimlane-row-header
                    groupBy="priority"
                    :groupId="1"
                    label="Critical"
                    :totalCount="4"
                    :statusCounts="['3' => 2, '4' => 1, '5' => 1]"
                    :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                    :expanded="true"
                    timeAlert="overdue"
                />
            </div>
            <div>
                <small style="color: #666;">Collapsed (minimal)</small>
                <x-global::kanban.swimlane-row-header
                    groupBy="priority"
                    :groupId="2"
                    label="High"
                    :totalCount="6"
                    :statusCounts="['3' => 3, '4' => 2, '5' => 1]"
                    :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                    :expanded="false"
                    timeAlert="dueSoon"
                />
            </div>
            <div>
                <small style="color: #666;">Collapsed (USER groupby)</small>
                <x-global::kanban.swimlane-row-header
                    groupBy="editorId"
                    groupId="123"
                    label="Sarah Chen"
                    :totalCount="5"
                    :statusCounts="['3' => 2, '4' => 2, '5' => 1]"
                    :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                    :expanded="false"
                    timeAlert="overdue"
                />
            </div>
            <div>
                <small style="color: #666;">Collapsed (EFFORT groupby)</small>
                <x-global::kanban.swimlane-row-header
                    groupBy="storypoints"
                    :groupId="5"
                    label="L"
                    :totalCount="3"
                    :statusCounts="['3' => 1, '4' => 1, '5' => 1]"
                    :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                    :expanded="false"
                    timeAlert="stale"
                />
            </div>
        </div>

        <h3 style="color: #6B7A4D; margin-top: 20px;">GROUP BY: EFFORT</h3>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <x-global::kanban.swimlane-row-header
                groupBy="storypoints"
                :groupId="1"
                label="XS"
                :totalCount="5"
                :statusCounts="['3' => 2, '4' => 2, '5' => 1]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="storypoints"
                :groupId="2"
                label="S"
                :totalCount="18"
                :statusCounts="['3' => 6, '4' => 6, '5' => 6]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
                timeAlert="dueSoon"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="storypoints"
                :groupId="3"
                label="M"
                :totalCount="6"
                :statusCounts="['3' => 2, '4' => 2, '5' => 2]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="storypoints"
                :groupId="5"
                label="L"
                :totalCount="3"
                :statusCounts="['3' => 0, '4' => 1, '5' => 2]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
                timeAlert="overdue"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="storypoints"
                :groupId="8"
                label="XL"
                :totalCount="2"
                :statusCounts="['3' => 1, '4' => 1, '5' => 0]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
        </div>

        <h3 style="color: #6B7A4D; margin-top: 20px;">GROUP BY: MILESTONE</h3>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <x-global::kanban.swimlane-row-header
                groupBy="milestoneid"
                groupId="1"
                label="Q1 Launch"
                :totalCount="12"
                :statusCounts="['3' => 4, '4' => 5, '5' => 3]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
                timeAlert="dueSoon"
                moreInfo="Start: Jan 1, 2024 • End: Mar 31, 2024 • Status: Active"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="milestoneid"
                groupId="2"
                label="Beta Release"
                :totalCount="8"
                :statusCounts="['3' => 2, '4' => 2, '5' => 4]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
                moreInfo="Start: Feb 1, 2024 • End: Feb 28, 2024 • Status: Completed"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="milestoneid"
                groupId="3"
                label="User Research"
                :totalCount="5"
                :statusCounts="['3' => 3, '4' => 2, '5' => 0]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
                moreInfo="Start: Mar 1, 2024 • End: Apr 15, 2024 • Status: In Progress"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="milestoneid"
                groupId="0"
                label="No Milestone"
                :totalCount="7"
                :statusCounts="['3' => 4, '4' => 2, '5' => 1]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
                timeAlert="stale"
            />
        </div>

        <h3 style="color: #6B7A4D; margin-top: 20px;">GROUP BY: SPRINT</h3>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <x-global::kanban.swimlane-row-header
                groupBy="sprint"
                groupId="23"
                label="Sprint 23"
                :totalCount="15"
                :statusCounts="['3' => 5, '4' => 7, '5' => 3]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
                timeAlert="dueSoon"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="sprint"
                groupId="24"
                label="Sprint 24"
                :totalCount="8"
                :statusCounts="['3' => 3, '4' => 4, '5' => 1]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
            <x-global::kanban.swimlane-row-header
                groupBy="sprint"
                groupId="0"
                label="Backlog"
                :totalCount="24"
                :statusCounts="['3' => 18, '4' => 4, '5' => 2]"
                :statusColumns="['3' => 'New', '4' => 'In Progress', '5' => 'Done']"
                :expanded="true"
            />
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #FFF9E6; border-left: 4px solid #F5A623; border-radius: 4px;">
            <strong>💡 Design Notes:</strong>
            <ul style="margin: 8px 0; padding-left: 20px;">
                <li><strong>Two states only:</strong> Expanded (shows progress bar) and Collapsed (hides progress bar)</li>
                <li><strong>Label text always visible</strong> in both states - only progress bar toggles</li>
                <li><strong>Count badge styling differs by state:</strong>
                    <ul style="margin: 4px 0; padding-left: 20px;">
                        <li>Expanded: Olive green background badge</li>
                        <li>Collapsed: Plain number (no background)</li>
                    </ul>
                </li>
                <li>Chevron rotates: ▼ (expanded) → ► (collapsed)</li>
                <li>Better spacing and alignment in both states</li>
                <li>Time indicators (⏳⏰💤) inline with label row</li>
                <li>Milestone dates show on hover only (not displayed on card)</li>
                <li>Icons match design: 🎯 for milestones, 🏃 for sprints, 👕 for effort</li>
                <li>Smooth transition animation between states (0.2s ease)</li>
            </ul>
        </div>
    </div>

    <div class="test-section">
        <h2>5. Design Tokens (CSS Variables)</h2>
        <div class="component-demo">
            <div class="component-label">Priority Colors</div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <div style="width: 120px; padding: 10px; background: var(--priority-critical); color: white; border-radius: 4px;">Critical</div>
                <div style="width: 120px; padding: 10px; background: var(--priority-high); color: white; border-radius: 4px;">High</div>
                <div style="width: 120px; padding: 10px; background: var(--priority-medium); color: white; border-radius: 4px;">Medium</div>
                <div style="width: 120px; padding: 10px; background: var(--priority-low); color: white; border-radius: 4px;">Low</div>
                <div style="width: 120px; padding: 10px; background: var(--priority-lowest); color: white; border-radius: 4px;">Lowest</div>
            </div>
        </div>
    </div>

    <script>
        console.log('Component test page loaded');

        // Implement toggle functionality for test page
        window.leantime = window.leantime || {};
        window.leantime.kanbanController = window.leantime.kanbanController || {};

        window.leantime.kanbanController.toggleSwimlane = function(id) {
            console.log('Toggle swimlane:', id);

            const header = document.querySelector('[data-swimlane-id="' + id + '"]');
            if (!header) return;

            const isExpanded = header.getAttribute('data-expanded') === 'true';
            const newExpanded = !isExpanded;

            // Update data attribute
            header.setAttribute('data-expanded', newExpanded.toString());

            // Find the component and re-render it (in real app, this would be handled by HTMX/server)
            // For test page, we'll use a simple approach: reload the page or toggle classes
            // Since we can't easily re-render Blade, let's just show a message
            alert('Toggle clicked! In the real Kanban view, this will expand/collapse the swimlane.\n\nCurrent state: ' + (isExpanded ? 'Expanded' : 'Collapsed') + '\nNew state: ' + (newExpanded ? 'Expanded' : 'Collapsed'));

            // In Phase 7, we'll implement actual server-side toggle with HTMX or page reload
        };
    </script>
</body>
</html>
