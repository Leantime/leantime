<?php
$searchCriteria = $tpl->get('searchCriteria') ?? [];
$initialSearchTerm = $searchCriteria['term'] ?? '';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components/modernSearch.css">

<style>
    .kanban-search-hidden {
        display: none !important;
    }
</style>

<div class="pull-right" style="margin-right: 15px;">
    <div class="modern-search-wrapper" id="kanbanSearchWrapper">
        <input
            type="text"
            id="kanbanSearch"
            class="modern-search-input"
            placeholder="<?= $tpl->__('label.search_term'); ?>"
            autocomplete="off"
            value="<?= $tpl->escape($initialSearchTerm); ?>"
        />
        <button type="button" id="kanbanSearchClear" class="modern-search-clear" aria-label="Clear search">
            <span class="fa fa-times"></span>
        </button>
    </div>
</div>

<script>
console.log('[KanbanSearch] Submodule script starting...');

(function () {
    var baseUrl = '<?= BASE_URL ?>';
    var initialQuery = <?= json_encode($initialSearchTerm) ?>;

    console.log('[KanbanSearch] Base URL:', baseUrl);
    console.log('[KanbanSearch] Initial query:', initialQuery);

    function loadScript(src, callback) {
        console.log('[KanbanSearch] Loading script:', src);
        var script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = function() {
            console.log('[KanbanSearch] Script loaded:', src);
            if (callback) callback();
        };
        script.onerror = function() {
            console.error('[KanbanSearch] Failed to load script:', src);
        };
        document.head.appendChild(script);
    }

    function ensureKanbanSearch() {
        console.log('[KanbanSearch] ensureKanbanSearch called');
        
        if (typeof window.leantime === 'undefined') {
            console.log('[KanbanSearch] window.leantime undefined, creating...');
            window.leantime = {};
        }

        if (typeof leantime.modernSearch === 'undefined') {
            console.log('[KanbanSearch] modernSearch undefined, loading...');
            loadScript(baseUrl + '/assets/js/app/core/modernSearch.js', ensureKanbanSearch);
            return;
        }

        console.log('[KanbanSearch] modernSearch available:', typeof leantime.modernSearch);

        if (typeof leantime.kanbanSearch === 'undefined') {
            console.log('[KanbanSearch] kanbanSearch undefined, loading...');
            loadScript(baseUrl + '/assets/js/app/tickets/kanbanSearch.js', ensureKanbanSearch);
            return;
        }

        console.log('[KanbanSearch] kanbanSearch available:', typeof leantime.kanbanSearch);

        var attempts = 0;
        var maxAttempts = 50;

        function tryInit() {
            attempts += 1;
            console.log('[KanbanSearch] tryInit attempt:', attempts);

            var input = document.querySelector('#kanbanSearch');
            var wrapper = document.querySelector('#kanbanSearchWrapper');
            
            console.log('[KanbanSearch] Input element:', input);
            console.log('[KanbanSearch] Wrapper element:', wrapper);

            if (typeof leantime.kanbanSearch === 'function' || (leantime.kanbanSearch && typeof leantime.kanbanSearch.init === 'function')) {
                console.log('[KanbanSearch] Calling kanbanSearch.init...');
                leantime.kanbanSearch.init({
                    inputSelector: '#kanbanSearch',
                    wrapperSelector: '#kanbanSearchWrapper',
                    initialQuery: initialQuery
                });
                console.log('[KanbanSearch] Init completed');
                return;
            }

            if (attempts < maxAttempts) {
                setTimeout(tryInit, 200);
            } else {
                console.error('[KanbanSearch] Max attempts reached, init failed');
            }
        }

        if (document.readyState === 'loading') {
            console.log('[KanbanSearch] Document still loading, waiting for DOMContentLoaded...');
            document.addEventListener('DOMContentLoaded', tryInit, { once: true });
        } else {
            console.log('[KanbanSearch] Document ready, calling tryInit immediately');
            tryInit();
        }
    }

    ensureKanbanSearch();
})();
</script>
