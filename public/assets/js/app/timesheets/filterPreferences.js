(function invokeFilterPreferences() {
    'use strict';

    const PROFILE_ENDPOINT = '/timesheets/saveFilterPreferences';

    let currentPreferences = {};
    let dataTableInstance = null;
    let selectedRangeName = null;
    let activeProfileName = localStorage.getItem('activeProfileName') || null;


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
            }

        };

        setTimeout(checkAndInit, 100);
    }

    function initDateRangeTracking() {
        const dateInput = jQuery('input[name="dateFrom"]');

        if (dateInput.length && dateInput.data('daterangepicker')) {
            dateInput.on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel) {
                    selectedRangeName = picker.chosenLabel;
                }
            });
        } else {
            setTimeout(initDateRangeTracking, 500);
        }
    }

    async function loadPreference(name) {
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
                    await applyFilters(data.preference.filters);
                    activeProfileName = name;
                    localStorage.setItem('activeProfileName', name);
                    updateActiveProfileDisplay();
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
        updateActiveProfileDisplay();
    }

    function onFilterChange() {
        if (activeProfileName) {
            clearActiveProfile();
        }
    }

    function attachFilterChangeListeners() {
        jQuery('select[name="clientId"], select[name="userId"], select[name="kind"]').on('change', onFilterChange);
        jQuery('input[name="invEmpl"], input[name="invComp"], input[name="paid"]').on('change', onFilterChange);
        jQuery('input[name="project[]"]').on('change', onFilterChange);
        jQuery('input[name="dateFrom"]').on('apply.daterangepicker', onFilterChange);
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
        const ranges = picker.ranges;

        if (ranges && ranges[rangeName]) {
            const range = ranges[rangeName];
            const startDate = range[0];
            const endDate = range[1];

            picker.setStartDate(startDate);
            picker.setEndDate(endDate);
            picker.chosenLabel = rangeName;

            jQuery('input[name="dateFrom"]').val(startDate.format('YYYY-MM-DD'));
            jQuery('input[name="dateTo"]').val(endDate.format('YYYY-MM-DD'));

            selectedRangeName = rangeName;

            return true;
        }

        return false;
    }

    async function applyFilters(filters) {
        if (!filters) {
            return;
        }
        if (filters.clientId) {
            jQuery('select[name="clientId"]').val(filters.clientId);
        }
        if (filters.userId) {
            jQuery('select[name="userId"]').val(filters.userId);
        }
        if (filters.kind) {
            jQuery('select[name="kind"]').val(filters.kind);
        }

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

        jQuery('input[name="invEmpl"]').prop('checked', filters.invEmpl === '1');
        jQuery('input[name="invComp"]').prop('checked', filters.invComp === '1');
        jQuery('input[name="paid"]').prop('checked', filters.paid === '1');

        if (filters.projects && Array.isArray(filters.projects)) {
            jQuery('input[name="project[]"]').prop('checked', false);

            if (filters.projects.includes('-1') || filters.projects.length === 0) {
                jQuery('#projectCheckboxAll').prop('checked', true);
            } else {
                filters.projects.forEach(function (projectId) {
                    jQuery('input[name="project[]"][value="' + projectId + '"]').prop('checked', true);
                });
                jQuery('#projectCheckboxAll').prop('checked', false);
            }

            if (typeof updateProjectCountInline === 'function') {
                updateProjectCountInline();
            }
        }

        if (filters.columnState && dataTableInstance && typeof dataTableInstance.columns === 'function') {
            dataTableInstance.columns().every(function (index) {
                const column = this;
                const columnName = jQuery(column.header()).data('column-name');
                if (columnName && filters.columnState.hasOwnProperty(columnName)) {
                    column.visible(filters.columnState[columnName]);
                }
            });

            if (typeof window.leantimeDataTablesColumnState !== 'undefined') {
                await window.leantimeDataTablesColumnState.save('allTimesheetsTable', filters.columnState, true);
            }
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
            if (picker.chosenLabel) {
                dateRange = picker.chosenLabel;
            } else if (selectedRangeName) {
                dateRange = selectedRangeName;
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
            const checked = pref.autoExport ? 'checked' : '';
            const isActive = (key === activeProfileName);
            const activeStyle = isActive ? 'background-color: #e8f4f8; border-left: 3px solid #004666;' : '';
            const activeIcon = isActive ? '<i class="fa fa-check-circle" style="color: #004666; margin-right: 5px;"></i>' : '';

            let dateInfo = '';
            if (pref.filters && pref.filters.dateRange && pref.filters.dateRange !== 'Custom') {
                dateInfo = `<small style="color: #666; font-size: 11px; display: block; margin-top: 2px;">📅 ${pref.filters.dateRange}</small>`;
            }

            html += `
            <div class="preference-item" style="display: flex; align-items: center; justify-content: space-between;flex-wrap:wrap; padding: 10px 12px; border-bottom: 1px solid #eee; ${activeStyle}">
                <div class="preference-name" data-name="${key}" style="flex: 1; font-weight: 500; color: #333;">
                    ${activeIcon}${key}
                    ${dateInfo}
                </div>
                <div style="display:flex; gap:8px; align-items: center; white-space: nowrap; margin-right: 15px;">
                    <label style="margin: 0; line-height: 1; display: flex; align-items: center;">Slack</label>
                    <input type="checkbox" class="auto-export" data-preference-name="${key}" ${checked} style="margin: 0; vertical-align: middle;"/>
                </div>
                <button class="delete-preference" data-name="${key}" style="background: none; border: none; color: #dc3545; cursor: pointer;" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        });

        return html;
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
                    alert('Profile saved successfully!')
                    updateDropdownContent()
                }
            })
        }
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
                    updateDropdownContent();
                }
            });
        });

        jQuery('.preference-item').hover(
            function () {
                jQuery(this).css('background-color', '#f8f9fa');
            },
            function () {
                jQuery(this).css('background-color', 'transparent');
            }
        );
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
            if (!jQuery(e.target).closest('#filterPreferencesBtn, #filterPreferencesDropdown').length) {
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