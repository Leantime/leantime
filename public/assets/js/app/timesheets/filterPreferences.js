(function invokeFilterPreferences() {
    'use strict';

    const PROFILE_ENDPOINT = '/timesheets/saveFilterPreferences';

    let currentPreferences = {};
    let dataTableInstance = null;
    let selectedRangeName = null;
    let activeProfileName = localStorage.getItem('activeProfileName') || null;
    let isEditMode = false;
    let isApplyingFilters = false;


    function init(dataTable) {
        dataTableInstance = dataTable;

        let attempts = 0;
        const maxAttempts = 20;

        const checkAndInit = function () {

            const dtButtons = jQuery('#tableButtons .dt-buttons');
            if (dtButtons.length > 0) {
                initUI();
                initDateRangeTracking();
                updateActiveProfileDisplay();
                checkAndRefreshActiveProfile();
            }

        };

        setTimeout(checkAndInit, 100);
    }

    function checkAndRefreshActiveProfile() {
        const activeProfile = localStorage.getItem('activeProfileName');
        const dateRange = localStorage.getItem('activeProfileDateRange');
        const lastApplied = localStorage.getItem('activeProfileLastApplied');

        if (!activeProfile || !dateRange || !lastApplied || dateRange === 'Custom') {
            return;
        }

        const lastAppliedDate = new Date(lastApplied);
        const now = new Date();

        if (shouldRefreshRange(dateRange, lastAppliedDate, now)) {

            loadAllPreferences().then(function () {
                const pref = currentPreferences[activeProfile];
                if (pref && pref.filters) {
                    applyFilters(pref.filters).then(function () {
                        localStorage.setItem('activeProfileLastApplied', now.toISOString());
                        jQuery('#form').submit();
                    });
                }
            });
        }
    }

    function shouldRefreshRange(rangeType, lastApplied, now) {
        const lastAppliedDay = new Date(lastApplied.getFullYear(), lastApplied.getMonth(), lastApplied.getDate());
        const nowDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        switch (rangeType) {
            case 'Today':
            case 'Yesterday':
                return lastAppliedDay.getTime() !== nowDay.getTime();

            case 'This Week':
                const lastWeekStart = getStartOfWeek(lastApplied);
                const nowWeekStart = getStartOfWeek(now);
                return lastWeekStart.getTime() !== nowWeekStart.getTime();

            case 'This Month':
                return lastApplied.getMonth() !== now.getMonth() ||
                    lastApplied.getFullYear() !== now.getFullYear();

            case 'Last Month':
                return lastApplied.getMonth() !== now.getMonth() ||
                    lastApplied.getFullYear() !== now.getFullYear();

            default:
                return false;
        }
    }

    function getStartOfWeek(date) {
        const d = new Date(date);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        return new Date(d.setDate(diff));
    }

    function initDateRangeTracking() {
        const dateInput = jQuery('input[name="dateFrom"]');

        if (dateInput.length && dateInput.data('daterangepicker')) {
            const picker = dateInput.data('daterangepicker');
            jQuery(document).on('click', '.daterangepicker .ranges li', function () {
                const label = jQuery(this).text().trim();
                selectedRangeName = label;
                picker._storedChosenLabel = label;
            });

            dateInput.on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel && picker.chosenLabel !== 'Custom Range') {
                    selectedRangeName = picker.chosenLabel;
                    picker._storedChosenLabel = picker.chosenLabel;
                }
            });
        } else {
            setTimeout(initDateRangeTracking, 300);
        }
    }

    async function loadPreference(name) {
        // Don't load if we're in edit mode
        if (isEditMode) {
            console.log('[Profiles] Load blocked - edit mode active');
            return false;
        }
        
        try {
            const response = await fetch(leantime.appUrl + PROFILE_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'load',
                    name: name
                })
            });

            if (response.ok) {
                const data = await response.json();

                if (data.status === 'success' && data.preference) {
                    // Apply filters and wait for completion
                    await applyFilters(data.preference.filters);
                    
                    // Wait a bit more to ensure all async operations complete
                    await new Promise(resolve => setTimeout(resolve, 400));
                    
                    // Now it's safe to update state and submit
                    activeProfileName = name;
                    localStorage.setItem('activeProfileName', name);
                    localStorage.setItem('activeProfileDateRange', data.preference.filters.dateRange || 'Custom');
                    localStorage.setItem('activeProfileLastApplied', new Date().toISOString());
                    updateActiveProfileDisplay();
                    
                    console.log('[Profiles] Submitting form after profile load');
                    jQuery('#form').submit();
                    return true;
                } else {
                    console.error('[Profiles] Load failed:', data.message);
                }
            } else {
                console.error('[Profiles] Backend returned error:', response.status, response.statusText);
            }
        } catch (error) {
            console.error('[Profiles] Failed to load profile:', error);
        }

        alert('Failed to load profile');
        return false;
    }

    function updateActiveProfileDisplay() {
        const button = jQuery('#filterPreferencesBtn');
        if (activeProfileName) {
            button.html(`<span><i class="fa fa-bookmark"></i> Profile: ${activeProfileName}</span>`);
        } else {
            button.html(`<span><i class="fa fa-bookmark"></i> None selected</span>`);
            button.css('background-color', '');
        }
    }

    function clearActiveProfile() {
        activeProfileName = null;
        localStorage.removeItem('activeProfileName');
        localStorage.removeItem('activeProfileDateRange');
        localStorage.removeItem('activeProfileLastApplied');
        updateActiveProfileDisplay();
    }

    function onFilterChange() {
        // Don't clear active profile if we're applying filters or in edit mode
        if (activeProfileName && !isEditMode && !isApplyingFilters) {
            clearActiveProfile();
        }
    }

    function attachFilterChangeListeners() {
        jQuery('select[name="clientId"], select[name="userId"], select[name="kind"]').on('change', onFilterChange);
        jQuery('input[name="invEmpl"], input[name="invComp"], input[name="paid"]').on('change', onFilterChange);
        jQuery('input[name="project[]"]').on('change', onFilterChange);
        jQuery('input[name="dateFrom"]').on('apply.daterangepicker', onFilterChange);
    }
    
    function setupFormSubmitInterceptor() {
        // Remove any existing handler to avoid duplicates
        jQuery('#form').off('submit.editMode');
        
        // Intercept form submission to check edit mode and filter application
        jQuery('#form').on('submit.editMode', function(e) {
            if (isEditMode) {
                e.preventDefault();
                e.stopImmediatePropagation();
                console.log('[Profiles] Form submit blocked - edit mode active');
                return false;
            }
            if (isApplyingFilters) {
                e.preventDefault();
                e.stopImmediatePropagation();
                console.log('[Profiles] Form submit blocked - applying filters');
                return false;
            }
        });
    }

    async function savePreference(name) {

        if (!name || name.trim() === '') {
            alert('Please enter a name for this profile');
            return false;
        }

        const filters = getCurrentFilters();

        try {
            const response = await fetch(leantime.appUrl + PROFILE_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'save',
                    name: name,
                    filters: filters
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    await loadAllPreferences();
                    return true;
                } else {
                    console.error('[Profiles] Save failed:', data.message);
                }
            } else {
                console.error('[Profiles] Backend returned error:', response.status, response.statusText);
            }
        } catch (error) {
            console.error('[Profiles] Failed to save profile:', error);
        }

        alert('Failed to save profile');
        return false;
    }
    function applyDateRange(rangeName) {

        const dateInput = jQuery('input[name="dateFrom"]');
        if (!dateInput.length || !dateInput.data('daterangepicker')) {
            return false;
        }

        const picker = dateInput.data('daterangepicker');

        let startDate, endDate;

        switch (rangeName) {
            case 'Today':
                startDate = moment().startOf('day');
                endDate = moment().endOf('day');
                break;
            case 'This Week':
                startDate = moment().startOf('isoWeek');
                endDate = moment().endOf('isoWeek');
                break;
            case 'This Month':
                startDate = moment().startOf('month');
                endDate = moment().endOf('month');
                break;
            default:
                return false;
        }

        picker.setStartDate(startDate);
        picker.setEndDate(endDate);

        picker.chosenLabel = rangeName;
        picker._storedChosenLabel = rangeName;

        jQuery('input[name="dateFrom"]').val(startDate.format('YYYY-MM-DD'));
        jQuery('input[name="dateTo"]').val(endDate.format('YYYY-MM-DD'));

        selectedRangeName = rangeName;

        return true;
    }

    async function applyFilters(filters) {
        if (!filters) {
            return;
        }

        // Set flag to prevent change listeners from firing
        isApplyingFilters = true;
        console.log('[Profiles] Started applying filters');
        
        try {
            // Apply date range
            if (filters.dateRange && filters.dateRange !== 'Custom') {
                const applied = applyDateRange(filters.dateRange);

                if (!applied) {
                    if (filters.dateFrom) {
                        jQuery('input[name="dateFrom"]').val(filters.dateFrom);
                    }
                    if (filters.dateTo) {
                        jQuery('input[name="dateTo"]').val(filters.dateTo);
                    }
                }
            } else {
                if (filters.dateFrom) {
                    jQuery('input[name="dateFrom"]').val(filters.dateFrom);
                }
                if (filters.dateTo) {
                    jQuery('input[name="dateTo"]').val(filters.dateTo);
                }
            }

            // Apply other filters
            if (filters.clientId) {
                jQuery('select[name="clientId"]').val(filters.clientId);
            }
            if (filters.userId) {
                jQuery('select[name="userId"]').val(filters.userId);
            }
            if (filters.kind) {
                jQuery('select[name="kind"]').val(filters.kind);
            }
            
            // Apply checkboxes
            jQuery('input[name="invEmpl"]').prop('checked', filters.invEmpl === '1');
            jQuery('input[name="invComp"]').prop('checked', filters.invComp === '1');
            jQuery('input[name="paid"]').prop('checked', filters.paid === '1');

            // Apply project filters
            if (filters.projects) {
                // First uncheck all projects
                jQuery('input[name="project[]"]').prop('checked', false);
                
                if (filters.projects.includes('-1')) {
                    // Check "All Projects"
                    jQuery('#projectCheckboxAll').prop('checked', true);
                } else {
                    // Check specific projects
                    filters.projects.forEach(function(projectId) {
                        jQuery(`input[name="project[]"][value="${projectId}"]`).prop('checked', true);
                    });
                }
            }
        } finally {
            // Always reset the flag after a delay to ensure all async changes complete
            setTimeout(function() {
                isApplyingFilters = false;
                console.log('[Profiles] Finished applying filters');
            }, 300);
        }
    }

    async function loadAllPreferences() {
        try {
            const response = await fetch(leantime.appUrl + PROFILE_ENDPOINT, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                currentPreferences = data.preferences || {};
                return currentPreferences;
            } else {
                console.error('[Profiles] Backend returned error:', response.status, response.statusText);
            }
        } catch (error) {
            console.error('[Profiles] Failed to load preferences:', error);
        }
        return {};
    }

    function getCurrentFilters() {
        const dateFrom = jQuery('input[name="dateFrom"]').val() || '';
        const dateTo = jQuery('input[name="dateTo"]').val() || '';

        let dateRange = 'Custom';
        const dateInput = jQuery('input[name="dateFrom"]');

        if (dateInput.length && dateInput.data('daterangepicker')) {
            const picker = dateInput.data('daterangepicker');
            if (picker._storedChosenLabel && picker._storedChosenLabel !== 'Custom Range') {
                dateRange = picker._storedChosenLabel;
            } else if (picker.chosenLabel && picker.chosenLabel !== 'Custom Range') {
                dateRange = picker.chosenLabel;
            } else if (selectedRangeName && selectedRangeName !== 'Custom Range') {
                dateRange = selectedRangeName;
            } else {
                dateRange = detectRangeFromDates(dateFrom, dateTo);
            }
        }

        const projectFilters = {
            projects: [],
            clientId: jQuery('select[name="clientId"]').val() || '-1',
            userId: jQuery('select[name="userId"]').val() || 'all',
            kind: jQuery('select[name="kind"]').val() || 'all',
            invEmpl: jQuery('input[name="invEmpl"]').is(':checked') ? '1' : '0',
            invComp: jQuery('input[name="invComp"]').is(':checked') ? '1' : '0',
            paid: jQuery('input[name="paid"]').is(':checked') ? '1' : '0',
            dateRange: dateRange,
            dateFrom: dateFrom,
            dateTo: dateTo
        };

        const selectedProjects = [];

        jQuery('input[name="project[]"]:checked').each(function () {
            const val = jQuery(this).val();

            if (val !== '-1') {
                selectedProjects.push(val);
            }
        });

        const allProjectsChecked = jQuery('#projectCheckboxAll').is(':checked');

        if (allProjectsChecked || selectedProjects.length === 0) {
            projectFilters.projects = ['-1'];
        } else {
            projectFilters.projects = selectedProjects;
        }

        if (dataTableInstance && typeof dataTableInstance.columns === 'function') {
            const columnState = {};


            dataTableInstance.columns().every(function (index) {
                const column = this;
                const columnName = jQuery(column.header()).data('column-name');
                if (columnName) {
                    columnState[columnName] = column.visible();
                }
            });
            projectFilters.columnState = columnState;
        }

        return projectFilters;
    }
    function detectRangeFromDates(dateFrom, dateTo) {
        if (!dateFrom || !dateTo) return 'Custom';

        const from = moment(dateFrom, 'MM/DD/YYYY');
        const to = moment(dateTo, 'MM/DD/YYYY');
        const today = moment().startOf('day');

        if (from.isSame(today, 'day') && to.isSame(today, 'day')) {
            return 'Today';
        }

        const thisWeekStart = moment().startOf('isoWeek');
        const thisWeekEnd = moment().endOf('isoWeek');
        if (from.isSame(thisWeekStart, 'day') && to.isSame(thisWeekEnd, 'day')) {
            return 'This Week';
        }

        const thisMonthStart = moment().startOf('month');
        const thisMonthEnd = moment().endOf('month');
        if (from.isSame(thisMonthStart, 'day') && to.isSame(thisMonthEnd, 'day')) {
            return 'This Month';
        }

        return 'Custom';
    }

    async function deletePreference(name) {

        if (!confirm('Are you sure you want to delete this profile?')) {
            return false;
        }

        try {
            const response = await fetch(leantime.appUrl + PROFILE_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'delete',
                    name: name
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    await loadAllPreferences();
                    return true;
                } else {
                    console.error('[Profiles] Delete failed:', data.message);
                }
            } else {
                console.error('[Profiles] Backend returned error:', response.status, response.statusText);
            }
        } catch (error) {
            console.error('[Profiles] Failed to delete profile:', error);
        }

        alert('Failed to delete profile');
        return false;
    }


    function buildPreferencesDropdown() {
        const keys = Object.keys(currentPreferences);

        if (keys.length === 0) {
            return '<div style="padding: 12px; text-align: center; color: #666;"><i class="fa fa-info-circle"></i> No saved profiles</div>';
        }

        let html = '';
        keys.forEach(function (key) {
            const pref = currentPreferences[key];
            const hasProject = pref.slackProjectId && pref.slackProjectId !== '';
            const checked = pref.autoExport && hasProject ? 'checked' : '';
            const disabled = !hasProject ? 'disabled' : '';
            const slackStyle = !hasProject ? 'opacity: 0.5;' : '';
            const isActive = (key === activeProfileName);
            const activeStyle = isActive ? 'background-color: #e8f4f8; border-left: 3px solid #004666;' : '';
            const activeIcon = isActive ? '<i class="fa fa-check-circle" style="color: #004666; margin-right: 5px;"></i>' : '';

            let dateInfo = '';
            if (pref.filters && pref.filters.dateRange && pref.filters.dateRange !== 'Custom') {
                dateInfo = `<small style="color: #666; font-size: 11px; display: block; margin-top: 2px;">📅 ${pref.filters.dateRange}</small>`;
            }

            let projectTooltip = '';
            if (hasProject && typeof projectNames !== 'undefined' && projectNames[pref.slackProjectId]) {
                projectTooltip = projectNames[pref.slackProjectId];
            } else {
                projectTooltip = 'No project set';
            }

            html += `
            <div class="preference-item" style="display: flex; align-items: center; justify-content: space-between;flex-wrap:wrap; padding: 10px 12px; border-bottom: 1px solid #eee; ${activeStyle}">
                <div class="preference-name" data-name="${key}" style="flex: 1; font-weight: 500; color: #333;">
                    ${activeIcon}${key}
                    ${dateInfo}
                </div>
                <div style="display:flex; gap:8px; align-items: center; white-space: nowrap; margin-right: 8px;">
                    <div style="${slackStyle}" title="${projectTooltip}">
                        <label style="margin: 0; line-height: 1; display: flex; align-items: center; cursor: help;">Slack</label>
                    </div>
                    <input type="checkbox" class="auto-export" data-preference-name="${key}" ${checked} ${disabled} style="margin: 0; vertical-align: middle;"/>
                </div>
                <button class="edit-slack-project" data-name="${key}" style="background: none; border: none; color: #004666; cursor: pointer; padding: 2px;" title="Set Slack Project">
                    <i class="fa fa-cog"></i>
                </button>
                <button class="edit-preference" data-name="${key}" style="background: none; border: none; color: #004666; cursor: pointer;" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-preference" data-name="${key}" style="background: none; border: none; color: #dc3545; cursor: pointer;" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        });

        return html;
    }

    function editProfile(profileName) {
        const pref = currentPreferences[profileName];
        if (!pref) return;

        // Enable edit mode
        isEditMode = true;
        
        // First, apply the profile's filters to the form
        applyFilters(pref.filters).then(function() {
            // Close the dropdown
            jQuery('#filterPreferencesDropdown').hide();
            
            // Show edit mode banner
            showEditModeBanner(profileName);
            
            // Optionally scroll to the filters section
            jQuery('html, body').animate({
                scrollTop: jQuery('#form').offset().top - 100
            }, 500);
        });
    }

    function showEditModeBanner(profileName) {
        // Remove any existing banner
        jQuery('#editModeBanner').remove();
        
        const banner = jQuery(`
            <div id="editModeBanner" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: #fff3cd;
                border: 2px solid #ffc107;
                border-radius: 8px;
                padding: 15px 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                max-width: 400px;
            ">
                <div style="display: flex; align-items: start; gap: 10px;">
                    <i class="fa fa-edit" style="color: #856404; margin-top: 2px;"></i>
                    <div style="flex: 1;">
                        <strong style="color: #856404; display: block; margin-bottom: 5px;">
                            Editing: ${profileName}
                        </strong>
                        <p style="margin: 0 0 5px 0; font-size: 13px; color: #856404;">
                            Make your changes, then click "Save Changes" to update this profile.
                        </p>
                        <p style="margin: 0 0 10px 0; font-size: 12px; color: #856404; font-style: italic;">
                            ⚠️ Auto-save is disabled
                        </p>
                        <div style="display: flex; gap: 8px;">
                            <button id="saveEditedProfile" class="btn btn-sm btn-success" style="padding: 4px 12px; font-size: 12px;">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                            <button id="cancelEditMode" class="btn btn-sm btn-secondary" style="padding: 4px 12px; font-size: 12px;">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        jQuery('body').append(banner);
        
        // Prevent banner from being affected by clicks
        banner.on('click', function(e) {
            e.stopPropagation();
        });
        
        // Save button handler
        jQuery('#saveEditedProfile').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const wasActiveProfile = (activeProfileName === profileName);
            
            savePreference(profileName).then(function(success) {
                if (success) {
                    // Disable edit mode first
                    isEditMode = false;
                    
                    // Remove banner
                    jQuery('#editModeBanner').fadeOut(300, function() {
                        jQuery(this).remove();
                    });
                    
                    // If this was the active profile, reload it to apply changes
                    if (wasActiveProfile) {
                        // Update the stored profile name in case filters changed
                        activeProfileName = profileName;
                        localStorage.setItem('activeProfileName', profileName);
                        updateActiveProfileDisplay();
                        
                        alert('Profile updated successfully! Click "Apply" or refresh to see changes.');
                        updateDropdownContent();
                    } else {
                        alert('Profile updated successfully!');
                        updateDropdownContent();
                    }
                }
            });
        });
        
        // Cancel button handler
        jQuery('#cancelEditMode').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            isEditMode = false;
            jQuery('#editModeBanner').fadeOut(300, function() {
                jQuery(this).remove();
            });
            
            // Optionally reload the original profile if it was active
            if (activeProfileName === profileName) {
                loadPreference(profileName);
            }
        });
    }


    function initAutoExportListeners() {
        jQuery(document).on('change', '.auto-export', function () {
            fetch(leantime.appUrl + '/timesheets/saveFilterPreferences', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'setAutoExport',
                    name: this.dataset.preferenceName,
                    autoExport: this.checked
                })
            })
                .then(r => r.json())
                .catch(() => {
                    this.checked = !this.checked;
                });
        });
    }

    jQuery(document).ready(initAutoExportListeners);

    function showSaveDialog() {
        const name = prompt('Enter a name for this profile:')

        if (name && name.trim() !== '') {
            savePreference(name.trim()).then(function (success) {
                if (success) {
                    // Disable edit mode
                    isEditMode = false;
                    
                    // Remove edit banner if it exists
                    jQuery('#editModeBanner').fadeOut(300, function() {
                        jQuery(this).remove();
                    });
                    
                    alert('Profile saved successfully!')
                    updateDropdownContent()
                }
            })
        } else if (name !== null) {
            // User clicked OK but entered empty name, stay in edit mode
            alert('Please enter a valid profile name');
        }
        // If user clicked Cancel on prompt, stay in edit mode
    }

    function updateDropdownContent() {
        const dropdown = document.getElementById('filterPreferencesDropdown')
        if (dropdown) {
            const content = buildPreferencesDropdown()
            const listContainer = dropdown.querySelector('.preferences-list')
            if (listContainer) {
                listContainer.innerHTML = content
                attachDropdownEventListeners()
            }
        }
    }

    function attachDropdownEventListeners() {
        jQuery('.preference-name').off('click').on('click', function (e) {
            e.stopPropagation();
            const name = jQuery(this).data('name');
            loadPreference(name);
            jQuery('#filterPreferencesDropdown').hide();
        });

        jQuery('.delete-preference').off('click').on('click', function (e) {
            e.stopPropagation();
            e.preventDefault();
            const name = jQuery(this).data('name');
            deletePreference(name).then(function (success) {
                if (success) {
                    if (activeProfileName === name) {
                        clearActiveProfile();
                    }
                    updateDropdownContent();
                }
            });
        });

        jQuery('.edit-preference').off('click').on('click', function (e) {
            e.stopPropagation();
            const name = jQuery(this).data('name');
            editProfile(name);
        });

        jQuery('.edit-slack-project').off('click').on('click', function (e) {
            e.stopPropagation();
            e.preventDefault();
            const name = jQuery(this).data('name');
            showProjectSelector(name);
        });

        jQuery('.preference-item').hover(
            function () {
                jQuery(this).css('background-color', '#f8f9fa');
            },
            function () {
                const isActive = jQuery(this).find('.preference-name').data('name') === activeProfileName;
                if (!isActive) {
                    jQuery(this).css('background-color', 'transparent');
                }
            }
        );
    }

    function showProjectSelector(profileName) {
        jQuery('#slackProjectModal').remove();

        const pref = currentPreferences[profileName];
        const currentProjectId = pref ? pref.slackProjectId : '';

        let projectOptions = '<option value="">-- No Project (Disable Slack) --</option>';
        if (typeof projectNames !== 'undefined') {
            for (const [id, name] of Object.entries(projectNames)) {
                const selected = id == currentProjectId ? 'selected' : '';
                projectOptions += `<option value="${id}" ${selected}>${name}</option>`;
            }
        }

        const modalHtml = `
            <div id="slackProjectModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; border-radius: 8px; padding: 20px; min-width: 350px; max-width: 450px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                    <h4 style="margin: 0 0 15px 0; color: #333;">
                        <i class="fa fa-slack" style="color: #4A154B;"></i> Set Slack Project
                    </h4>
                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                        Select a project for profile "<strong>${profileName}</strong>".<br/>
                        <small>The Slack channel ID must be configured in the project settings.</small>
                    </p>
                    <select id="slackProjectSelect" class="form-control" style="width: 100%; padding: 8px; margin-bottom: 15px;">
                        ${projectOptions}
                    </select>
                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="cancelSlackProject" class="btn btn-default" style="padding: 8px 16px;">Cancel</button>
                        <button type="button" id="saveSlackProject" class="btn btn-primary" style="padding: 8px 16px; background: #004666; border: none;">Save</button>
                    </div>
                </div>
            </div>
        `;

        jQuery('body').append(modalHtml);

        jQuery('#cancelSlackProject').on('click', function () {
            jQuery('#slackProjectModal').remove();
        });

        jQuery('#slackProjectModal').on('click', function (e) {
            if (e.target === this) {
                jQuery('#slackProjectModal').remove();
            }
        });

        jQuery('#saveSlackProject').on('click', function () {
            const selectedProjectId = jQuery('#slackProjectSelect').val();
            saveSlackProjectSetting(profileName, selectedProjectId).then(function (success) {
                jQuery('#slackProjectModal').remove();
                if (success) {
                    loadAllPreferences().then(function () {
                        updateDropdownContent();
                    });
                }
            });
        });
    }

    async function saveSlackProjectSetting(profileName, projectId) {
        try {
            const response = await fetch(leantime.appUrl + PROFILE_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'setSlackProject',
                    name: profileName,
                    slackProjectId: projectId
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    return true;
                }
            }
        } catch (error) {
            console.error('[Profiles] Failed to save Slack project setting:', error);
        }
        return false;
    }

    function initUI() {
        const tableButtons = jQuery('#tableButtons')

        if (!tableButtons.length) {
            return
        }

        const dtButtonsContainer = tableButtons.find('.dt-buttons')

        const preferencesButton = jQuery(`
                <button type="button" id="filterPreferencesBtn" class="dt-button">
                    <span><i class="fa fa-bookmark"></i> Profiles</span>
                </button>
            `);
        dtButtonsContainer.prepend(preferencesButton);
        updateActiveProfileDisplay();

        const dropdownHTML = `
            <div id="filterPreferencesDropdown" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #d0d5dd; border-radius: 8px; width: 300px; max-height: 400px; overflow-y: auto; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15); margin-top: 4px;">
                <div style="padding: 12px; border-bottom: 1px solid #eee; background: #f7f9fc; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <strong style="color: #333; font-size: 14px;">Saved Profiles</strong>
                        <button type="button" id="saveCurrentPreference" class="btn btn-sm" style="background: #004666; color: white; border: none; padding: 4px 10px; cursor: pointer; border-radius: 4px; font-size: 12px;">
                            <i class="fa fa-plus"></i> Save Current
                        </button>
                    </div>
                </div>
                <div class="preferences-list">
                    <!-- Will be populated dynamically -->
                </div>
            </div>
        `;

        jQuery('body').append(dropdownHTML);
        attachFilterChangeListeners();
        setupFormSubmitInterceptor();

        jQuery(document).on('click', '#filterPreferencesBtn', function (e) {
            e.stopPropagation();
            const dropdown = jQuery('#filterPreferencesDropdown');
            const button = jQuery(this);

            if (dropdown.is(':visible')) {
                dropdown.hide();
            } else {
                const buttonOffset = button.offset();
                const buttonHeight = button.outerHeight();
                dropdown.css({
                    top: buttonOffset.top + buttonHeight + 4,
                    left: buttonOffset.left
                });

                loadAllPreferences().then(function () {
                    updateDropdownContent();
                    dropdown.show();
                });
            }
        });

        jQuery(document).on('click', '#saveCurrentPreference', function (e) {
            e.stopPropagation();
            showSaveDialog();
        });

        jQuery(document).on('click', function (e) {
            if (!jQuery(e.target).closest('#filterPreferencesBtn, #filterPreferencesDropdown, #editModeBanner').length) {
                jQuery('#filterPreferencesDropdown').hide()
            }
        });

        jQuery(document).on('click', '#clearActiveProfile', function (e) {
            e.stopPropagation();
            clearActiveProfile();
            updateDropdownContent();
        });

        jQuery(document).on('change', '.auto-export-checkbox', function (e) {
            e.stopPropagation();
            const profileName = jQuery(this).data('name');
            const isEnabled = jQuery(this).is(':checked');

            saveAutoExportSetting(profileName, isEnabled);
        });

    }

    async function saveAutoExportSetting(profileName, enabled) {
        try {
            const response = await fetch(leantime.appUrl + PROFILE_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'setAutoExport',
                    name: profileName,
                    autoExport: enabled
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                }
            }
        } catch (error) {
            console.error('[Profiles] Failed to save auto-export setting:', error);
        }
    }

    window.leantimeFilterPreferences = {
        init: init,
        save: savePreference,
        load: loadPreference,
        delete: deletePreference,
        getCurrent: getCurrentFilters,
        apply: applyFilters
    }
})()