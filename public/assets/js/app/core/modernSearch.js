window.leantime = window.leantime || {};

/**
 * Modern Search Component - Reusable YouTrack-style search with autocomplete
 * Can be used across different modules (Timesheets, Tickets, etc.)
 * 
 * Usage:
 * leantime.modernSearch.init({
 *   inputSelector: '#mySearchInput',
 *   containerSelector: '#mySearchContainer',
 *   dataTableInstance: myDataTable,
 *   getSuggestions: function(query) { return [...]; },
 *   onSelect: function(item) { ... }
 * });
 */

leantime.modernSearch = (function () {
    'use strict';

    /**
     * Initialize search component
     * @param {Object} config - Configuration object
     */
    function init(config) {
        const {
            inputSelector,
            containerSelector,
            dataTableInstance = null,
            getSuggestions = null,
            onSelect = null,
            onSearch = null,
            debounceDelay = 300,
            autocompleteDelay = 150
        } = config;

        const input = document.querySelector(inputSelector);
        const container = document.querySelector(containerSelector);
        
        if (!input || !container) {
            console.warn('ModernSearch: Input or container not found', inputSelector, containerSelector);
            return;
        }

        let autocomplete = null;
        let selectedIndex = -1;
        let suggestions = [];
        let searchTimer = null;
        let autocompleteTimer = null;

        // Create autocomplete dropdown
        function createAutocomplete() {
            if (!autocomplete) {
                autocomplete = document.createElement('div');
                autocomplete.className = 'modern-search-autocomplete';
                container.appendChild(autocomplete);
            }
            return autocomplete;
        }

        // Show suggestions
        function showSuggestions(query) {
            if (!getSuggestions) return;

            const dropdown = createAutocomplete();
            
            if (!query || query.trim() === '') {
                dropdown.classList.remove('active');
                return;
            }

            suggestions = getSuggestions(query);

            if (suggestions.length === 0) {
                dropdown.classList.remove('active');
                return;
            }

            // Build HTML
            let html = '';
            suggestions.forEach((item, index) => {
                const selectedClass = index === selectedIndex ? ' selected' : '';
                html += `<div class="modern-search-item${selectedClass}" data-index="${index}">`;
                html += `<div class="modern-search-item-title">`;
                html += `<i class="fa ${item.icon || 'fa-search'}" style="margin-right: 8px; width: 16px; text-align: center;"></i>`;
                html += item.label;
                html += `</div>`;
                if (item.meta) {
                    html += `<div class="modern-search-item-meta">`;
                    html += `<span class="modern-search-item-tag">${item.meta}</span>`;
                    html += `</div>`;
                }
                html += `</div>`;
            });

            // Add keyboard hints footer
            html += `<div class="modern-search-footer">`;
            html += `<kbd>↑</kbd><kbd>↓</kbd> Navigate`;
            html += `<span style="margin:0 8px;">•</span>`;
            html += `<kbd>Enter</kbd> Select or Search`;
            html += `<span style="margin:0 8px;">•</span>`;
            html += `<kbd>Esc</kbd> Close`;
            html += `</div>`;

            dropdown.innerHTML = html;
            dropdown.classList.add('active');
            selectedIndex = -1;

            // Add click handlers
            dropdown.querySelectorAll('.modern-search-item').forEach(item => {
                item.addEventListener('click', () => {
                    const index = parseInt(item.dataset.index);
                    selectSuggestion(index);
                });
            });
        }

        // Select suggestion
        function selectSuggestion(index) {
            if (index < 0 || index >= suggestions.length) return;

            const item = suggestions[index];
            input.value = item.value || item.label;

            if (autocomplete) {
                autocomplete.classList.remove('active');
            }

            if (onSelect) {
                onSelect(item);
            }

            if (dataTableInstance) {
                clearTimeout(searchTimer);
                dataTableInstance.search(input.value).draw();
            }
        }

        // Input event handler
        input.addEventListener('input', (e) => {
            const searchValue = e.target.value;

            // Show autocomplete
            clearTimeout(autocompleteTimer);
            if (searchValue.length >= 1) {
                autocompleteTimer = setTimeout(() => {
                    showSuggestions(searchValue);
                }, autocompleteDelay);
            } else {
                if (autocomplete) {
                    autocomplete.classList.remove('active');
                }
            }

            // NOTE: Search is triggered ONLY on Enter key, not on input
        });

        // Keyboard navigation
        input.addEventListener('keydown', (e) => {
            // Handle autocomplete navigation if active
            if (autocomplete && autocomplete.classList.contains('active')) {
                const items = autocomplete.querySelectorAll('.modern-search-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, suggestions.length - 1);
                    items.forEach((item, i) => {
                        item.classList.toggle('selected', i === selectedIndex);
                    });
                    if (selectedIndex >= 0) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                    return; // Don't process other keys
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    items.forEach((item, i) => {
                        item.classList.toggle('selected', i === selectedIndex);
                    });
                    if (selectedIndex >= 0) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                    return; // Don't process other keys
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    autocomplete.classList.remove('active');
                    selectedIndex = -1;
                    return;
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    // Select from autocomplete
                    e.preventDefault();
                    selectSuggestion(selectedIndex);
                    return;
                }
            }
            
            // Handle Enter key for search (when autocomplete is not active or no selection)
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // Close autocomplete if open
                if (autocomplete) {
                    autocomplete.classList.remove('active');
                    selectedIndex = -1;
                }
                
                // Trigger search
                if (onSearch) {
                    // Use custom search handler if provided
                    onSearch(input.value);
                } else if (dataTableInstance) {
                    // Fallback to default DataTables search
                    dataTableInstance.search(input.value).draw();
                }
            }
        });

        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                if (autocomplete) {
                    autocomplete.classList.remove('active');
                    selectedIndex = -1;
                }
            }
        });
    }

    // Public API
    return {
        init: init
    };
})();
