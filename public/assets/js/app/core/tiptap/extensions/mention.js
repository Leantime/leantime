/**
 * Tiptap Mention Extension for Leantime
 *
 * Provides @mentions functionality with user autocomplete
 */

const Mention = require('@tiptap/extension-mention').default;
const { PluginKey } = require('@tiptap/pm/state');

/**
 * Fetch users from the API based on query
 */
function fetchUsers(query) {
    return new Promise(function(resolve, reject) {
        var url = leantime.appUrl + '/api/users?' +
            'projectUsersAccess=current' +
            (query ? '&query=' + encodeURIComponent(query) : '');

        fetch(url, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to fetch users');
            return response.json();
        })
        .then(function(data) {
            // Transform API response to mention format
            var users = data.map(function(user) {
                return {
                    id: user.id,
                    label: (user.firstname + ' ' + user.lastname).trim(),
                    email: user.username || user.email,
                    profileId: user.profileId || null
                };
            });
            resolve(users);
        })
        .catch(function(error) {
            console.error('[Mention] Error fetching users:', error);
            resolve([]);
        });
    });
}

/**
 * Create the suggestion dropdown popup
 */
function createSuggestionPopup() {
    var popup = document.createElement('div');
    popup.className = 'tiptap-mention-popup';
    popup.style.display = 'none';
    document.body.appendChild(popup);
    return popup;
}

/**
 * Render suggestion items in the popup
 */
function renderSuggestionItems(items, popup, selectedIndex, onSelect) {
    if (items.length === 0) {
        popup.innerHTML = '<div class="tiptap-mention-popup__empty">No users found</div>';
        return;
    }

    var html = items.map(function(item, index) {
        var activeClass = index === selectedIndex ? 'tiptap-mention-popup__item--active' : '';
        var initials = getInitials(item.label);

        return '<div class="tiptap-mention-popup__item ' + activeClass + '" data-index="' + index + '">' +
            '<div class="tiptap-mention-popup__avatar">' + initials + '</div>' +
            '<div class="tiptap-mention-popup__info">' +
                '<div class="tiptap-mention-popup__name">' + escapeHtml(item.label) + '</div>' +
                '<div class="tiptap-mention-popup__email">' + escapeHtml(item.email || '') + '</div>' +
            '</div>' +
        '</div>';
    }).join('');

    popup.innerHTML = html;

    // Add click handlers
    var itemElements = popup.querySelectorAll('.tiptap-mention-popup__item');
    itemElements.forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var index = parseInt(el.getAttribute('data-index'), 10);
            if (items[index]) {
                onSelect(items[index]);
            }
        });
    });
}

/**
 * Get initials from a name
 */
function getInitials(name) {
    if (!name) return '?';
    var parts = name.trim().split(/\s+/);
    if (parts.length === 1) {
        return parts[0].charAt(0).toUpperCase();
    }
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Position the popup near the cursor
 */
function positionPopup(popup, clientRect) {
    if (!clientRect) {
        popup.style.display = 'none';
        return;
    }

    var popupHeight = popup.offsetHeight || 200;
    var popupWidth = popup.offsetWidth || 280;
    var viewportHeight = window.innerHeight;
    var viewportWidth = window.innerWidth;
    var scrollTop = window.scrollY || document.documentElement.scrollTop;
    var scrollLeft = window.scrollX || document.documentElement.scrollLeft;

    // Calculate position
    var top = clientRect.bottom + scrollTop + 4;
    var left = clientRect.left + scrollLeft;

    // Adjust if popup would go off-screen
    if (top + popupHeight > viewportHeight + scrollTop) {
        top = clientRect.top + scrollTop - popupHeight - 4;
    }

    if (left + popupWidth > viewportWidth + scrollLeft) {
        left = viewportWidth + scrollLeft - popupWidth - 8;
    }

    if (left < scrollLeft) {
        left = scrollLeft + 8;
    }

    popup.style.top = top + 'px';
    popup.style.left = left + 'px';
    popup.style.display = 'block';
}

/**
 * Create the configured Mention extension
 */
function createMentionExtension() {
    var popup = null;
    var currentItems = [];
    var selectedIndex = 0;
    var commandRef = null;

    function selectItem(item) {
        if (commandRef && item) {
            commandRef({ id: item.id, label: item.label });
        }
    }

    return Mention.configure({
        HTMLAttributes: {
            class: 'tiptap-mention',
        },
        renderLabel: function(props) {
            return '@' + props.node.attrs.label;
        },
        suggestion: {
            char: '@',
            pluginKey: new PluginKey('mentionSuggestion'),
            allowSpaces: false,
            startOfLine: false,

            items: function(props) {
                var query = props.query || '';
                return fetchUsers(query).then(function(users) {
                    // Filter by query client-side as well for faster results
                    if (query) {
                        var lowerQuery = query.toLowerCase();
                        return users.filter(function(user) {
                            return user.label.toLowerCase().includes(lowerQuery) ||
                                (user.email && user.email.toLowerCase().includes(lowerQuery));
                        }).slice(0, 10);
                    }
                    return users.slice(0, 10);
                });
            },

            render: function() {
                return {
                    onStart: function(props) {
                        // Create popup if needed
                        if (!popup) {
                            popup = createSuggestionPopup();
                        }

                        selectedIndex = 0;
                        currentItems = props.items || [];
                        commandRef = props.command;

                        renderSuggestionItems(currentItems, popup, selectedIndex, selectItem);
                        positionPopup(popup, props.clientRect ? props.clientRect() : null);
                    },

                    onUpdate: function(props) {
                        selectedIndex = 0;
                        currentItems = props.items || [];
                        commandRef = props.command;

                        renderSuggestionItems(currentItems, popup, selectedIndex, selectItem);
                        positionPopup(popup, props.clientRect ? props.clientRect() : null);
                    },

                    onKeyDown: function(props) {
                        var event = props.event;

                        if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            selectedIndex = (selectedIndex + 1) % currentItems.length;
                            renderSuggestionItems(currentItems, popup, selectedIndex, selectItem);
                            return true;
                        }

                        if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            selectedIndex = (selectedIndex - 1 + currentItems.length) % currentItems.length;
                            renderSuggestionItems(currentItems, popup, selectedIndex, selectItem);
                            return true;
                        }

                        if (event.key === 'Enter' || event.key === 'Tab') {
                            event.preventDefault();
                            if (currentItems[selectedIndex]) {
                                selectItem(currentItems[selectedIndex]);
                            }
                            return true;
                        }

                        if (event.key === 'Escape') {
                            event.preventDefault();
                            if (popup) {
                                popup.style.display = 'none';
                            }
                            return true;
                        }

                        return false;
                    },

                    onExit: function() {
                        if (popup) {
                            popup.style.display = 'none';
                        }
                        currentItems = [];
                        selectedIndex = 0;
                        commandRef = null;
                    },
                };
            },
        },
    });
}

// Export for use in main module
module.exports = {
    createMentionExtension: createMentionExtension,
    fetchUsers: fetchUsers
};
