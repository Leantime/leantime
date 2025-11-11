window.leantime = window.leantime || {};

leantime.kanbanSearch = (function () {
    'use strict';

    const HIDDEN_CLASS = 'kanban-search-hidden';
    const MAX_SUGGESTIONS = 10;
    const ICONS = {
        ticket: 'fa-ticket',
        description: 'fa-align-left',
        tag: 'fa-tag',
        assignee: 'fa-user',
    };

    let cards = [];
    let currentSearchQuery = '';
    let currentSearchType = 'all';
    let initialized = false;

    function normalize(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }

    function collectCards() {
        const elements = document.querySelectorAll('.sortableTicketList.kanbanBoard .ticketBox.moveable');
        cards = Array.prototype.map.call(elements, (element) => {
            const dataset = element.dataset || {};
            const ticketId = dataset.ticketId || element.id.replace('ticket_', '');
            const headline = dataset.headline || '';
            const description = dataset.description || '';
            const tagsRaw = dataset.tags || '';
            const assignee = dataset.editorName || '';

            const tags = tagsRaw
                ? tagsRaw.split(',').map(function (tag) {
                    return tag.trim();
                }).filter(Boolean)
                : [];

            return {
                element: element,
                ticketId: ticketId,
                ticketIdNorm: normalize(ticketId),
                headline: headline,
                headlineNorm: normalize(headline),
                description: description,
                descriptionNorm: normalize(description),
                tags: tags,
                tagsNorm: tags.map(normalize),
                assignee: assignee,
                assigneeNorm: normalize(assignee),
                searchIndex: [
                    normalize(ticketId),
                    normalize(headline),
                    normalize(description),
                    tags.map(normalize).join(' '),
                    normalize(assignee),
                ].join(' ').trim(),
            };
        });
    }

    function buildSuggestions(query) {
        const normalized = normalize(query);
        if (!normalized) {
            return [];
        }

        if (!cards.length) {
            collectCards();
        }

        const suggestions = [];
        const seenIds = new Set();
        const seenTickets = new Set();
        const seenAssignees = new Set();
        const seenTags = new Set();
        const seenDescriptions = new Set();

        cards.forEach((card) => {
            if (suggestions.length >= MAX_SUGGESTIONS) {
                return;
            }

            if (card.ticketIdNorm && card.ticketIdNorm.indexOf(normalized) !== -1 && !seenIds.has(card.ticketId)) {
                seenIds.add(card.ticketId);
                suggestions.push({
                    type: 'ticket',
                    filterType: 'id',
                    icon: ICONS.ticket,
                    label: '#' + card.ticketId + ' · ' + card.headline,
                    value: card.ticketId,
                    meta: 'ticket',
                });
            }

            if (card.headlineNorm && card.headlineNorm.indexOf(normalized) !== -1 && !seenTickets.has(card.ticketId)) {
                seenTickets.add(card.ticketId);
                suggestions.push({
                    type: 'ticket',
                    filterType: 'ticket',
                    icon: ICONS.ticket,
                    label: card.headline,
                    value: card.headline,
                    meta: 'ticket',
                });
            }

            if (card.assigneeNorm && card.assigneeNorm.indexOf(normalized) !== -1 && !seenAssignees.has(card.assigneeNorm)) {
                seenAssignees.add(card.assigneeNorm);
                suggestions.push({
                    type: 'assignee',
                    filterType: 'assignee',
                    icon: ICONS.assignee,
                    label: card.assignee,
                    value: card.assignee,
                    meta: 'assignee',
                });
            }

            if (card.descriptionNorm && card.descriptionNorm.indexOf(normalized) !== -1 && !seenDescriptions.has(card.ticketId)) {
                seenDescriptions.add(card.ticketId);
                const snippet = card.description.length > 100
                    ? card.description.slice(0, 97) + '…'
                    : card.description;
                suggestions.push({
                    type: 'description',
                    filterType: 'description',
                    icon: ICONS.description,
                    label: snippet || card.headline,
                    value: query,
                    meta: 'description',
                });
            }

            card.tagsNorm.forEach((tagNorm, index) => {
                if (tagNorm.indexOf(normalized) !== -1 && !seenTags.has(tagNorm)) {
                    seenTags.add(tagNorm);
                    suggestions.push({
                        type: 'tag',
                        filterType: 'tag',
                        icon: ICONS.tag,
                        label: card.tags[index],
                        value: card.tags[index],
                        meta: 'tag',
                    });
                }
            });
        });

        return suggestions.slice(0, MAX_SUGGESTIONS);
    }

    function updateColumnCounts() {
        const totals = [];
        document.querySelectorAll('.sortableTicketList').forEach(function (list) {
            const columns = list.querySelectorAll('.column');
            columns.forEach(function (column, index) {
                const visibleTickets = column.querySelectorAll('.ticketBox.moveable:not(.' + HIDDEN_CLASS + ')').length;
                totals[index] = (totals[index] || 0) + visibleTickets;
            });
        });

        document.querySelectorAll('.widgettitle .count').forEach(function (countEl, index) {
            const value = totals[index] || 0;
            countEl.textContent = value;
        });
    }

    function matchesCard(card, normalizedQuery, type) {
        switch (type) {
            case 'id':
                return card.ticketIdNorm.indexOf(normalizedQuery) !== -1;
            case 'ticket':
                return card.headlineNorm.indexOf(normalizedQuery) !== -1
                    || card.ticketIdNorm.indexOf(normalizedQuery) !== -1;
            case 'description':
                return card.descriptionNorm.indexOf(normalizedQuery) !== -1;
            case 'tag':
                return card.tagsNorm.some(function (tag) {
                    return tag.indexOf(normalizedQuery) !== -1;
                });
            case 'assignee':
                return card.assigneeNorm.indexOf(normalizedQuery) !== -1;
            case 'all':
            default:
                return card.searchIndex.indexOf(normalizedQuery) !== -1;
        }
    }

    function applyFilter(type, query) {
        if (!cards.length) {
            collectCards();
        }

        const normalizedQuery = normalize(query);
        currentSearchType = type || 'all';
        currentSearchQuery = query || '';

        if (!normalizedQuery) {
            cards.forEach(function (card) {
                card.element.classList.remove(HIDDEN_CLASS);
            });
            updateColumnCounts();
            return;
        }

        cards.forEach(function (card) {
            if (matchesCard(card, normalizedQuery, currentSearchType)) {
                card.element.classList.remove(HIDDEN_CLASS);
            } else {
                card.element.classList.add(HIDDEN_CLASS);
            }
        });

        updateColumnCounts();
    }

    function setupInputBehavior(input, wrapper) {
        if (!input || !wrapper) {
            return;
        }

        function syncState() {
            if (input.value.trim() !== '') {
                wrapper.classList.add('has-value');
            } else {
                wrapper.classList.remove('has-value');
            }
        }

        syncState();

        input.addEventListener('input', function () {
            syncState();
            const query = input.value.trim();
            if (query === '') {
                currentSearchType = 'all';
                currentSearchQuery = '';
                applyFilter('all', '');
            } else {
                // Auto-apply filter as user types or deletes
                currentSearchType = 'all';
                currentSearchQuery = query;
                applyFilter('all', query);
            }
        });
    }

    function init(config) {
        const options = config || {};
        const inputSelector = options.inputSelector || '#kanbanSearch';
        const wrapperSelector = options.wrapperSelector || '#kanbanSearchWrapper';
        const input = document.querySelector(inputSelector);
        const wrapper = document.querySelector(wrapperSelector);
        const clearButton = document.querySelector('#kanbanSearchClear');

        if (!input || !wrapper) {
            return;
        }

        collectCards();
        setupInputBehavior(input, wrapper);

        if (clearButton) {
            clearButton.addEventListener('click', function () {
                input.value = '';
                wrapper.classList.remove('has-value');
                currentSearchType = 'all';
                currentSearchQuery = '';
                applyFilter('all', '');
                input.focus();
            });
        }

        leantime.modernSearch.init({
            inputSelector: inputSelector,
            containerSelector: wrapperSelector,
            getSuggestions: buildSuggestions,
            onSelect: function (item) {
                if (!item) {
                    return;
                }
                input.value = item.value || '';
                wrapper.classList.add('has-value');
                currentSearchType = item.filterType || item.type || 'all';
                currentSearchQuery = item.value || '';
                applyFilter(currentSearchType, currentSearchQuery);
            },
            onSearch: function (query) {
                currentSearchType = 'all';
                currentSearchQuery = query || '';
                applyFilter('all', currentSearchQuery);
            },
        });

        if (options.initialQuery && String(options.initialQuery).trim() !== '') {
            input.value = options.initialQuery;
            wrapper.classList.add('has-value');
            applyFilter('all', options.initialQuery);
        } else {
            applyFilter('all', '');
        }

        initialized = true;
    }

    return {
        init: init,
        _debug: {
            collectCards: collectCards,
            buildSuggestions: buildSuggestions,
        },
    };
})();
