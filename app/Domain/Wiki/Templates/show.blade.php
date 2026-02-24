@php
    $wikis = $tpl->get('wikis');
    $wikiHeadlines = $tpl->get('wikiHeadlines');
    $milestones = $tpl->get('milestones') ?? [];

    $currentWiki = $tpl->get('currentWiki');
    $currentArticle = $tpl->get('currentArticle');

    /**
     * Creates a modern tree view for wiki navigation.
     */
    function createModernTreeView(array $array, int|string $currentParent, int|string $currentArticleId, int $currLevel = 0, ?\Leantime\Core\UI\Template $tplObject = null): void
    {
        $hasChildren = false;

        foreach ($array as $headline) {
            if ((int) $currentParent === (int) $headline->parent) {
                if (! $hasChildren) {
                    echo '<ul class="wiki-tree">';
                    $hasChildren = true;
                }

                $isActive = ($currentArticleId == $headline->id) ? ' active' : '';
                $isDraft = ($headline->status == 'draft');

                echo '<li class="wiki-tree-item">';
                echo '<a href="' . BASE_URL . '/wiki/show/' . $headline->id . '" class="wiki-tree-link' . $isActive . '">';
                echo '<i class="' . $tplObject->escape($headline->data) . '"></i>';
                echo '<span>' . $tplObject->escape($headline->title) . '</span>';
                if ($isDraft) {
                    echo ' <span class="wiki-tree-draft">(' . $tplObject->__('label.draft') . ')</span>';
                }
                echo '</a>';

                createModernTreeView($array, $headline->id, $currentArticleId, $currLevel + 1, $tplObject);

                echo '</li>';
            }
        }

        if ($hasChildren) {
            echo '</ul>';
        }
    }

    // Get author initials for avatar
    $authorInitials = '';
    if ($currentArticle && ! empty($currentArticle->firstname)) {
        $authorInitials .= strtoupper(substr($currentArticle->firstname, 0, 1));
        if (! empty($currentArticle->lastname)) {
            $authorInitials .= strtoupper(substr($currentArticle->lastname, 0, 1));
        }
    }
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-book"></span></div>
    <div class="pagetitle">

        <h5>{{ e(session('currentProjectClient')) }}</h5>

        @if (count($wikis) > 0 && $login::userIsAtLeast($roles::$editor) && $currentWiki)
            <x-global::elements.dropdown containerClass="headerEditDropdown">
                <li><a class="inlineEdit" href="#/wiki/wikiModal/{{ $currentWiki->id }}">{{ __('link.edit_wiki') }}</a></li>
                <li><a class="delete" href="#/wiki/delWiki/{{ $currentWiki->id }}"><i class="fa fa-trash"></i> {{ __('links.delete_wiki') }}</a></li>
            </x-global::elements.dropdown>
        @endif

        <h1>{{ __('headlines.documents') }}
         @if (count($wikis) > 0)
             //
            <x-global::elements.link-dropdown :label="$currentWiki !== false ? e($currentWiki->title) : __('label.select_board')" triggerClass="header-title-dropdown">
                <li><a class="inlineEdit" href="#/wiki/wikiModal/">{{ __('link.new_wiki') }}</a></li>
                <li class="nav-header"></li>
                @foreach ($wikis as $wiki)
                    <li>
                        <a href="{{ BASE_URL }}/wiki/show?setWiki={{ $wiki->id }}">{{ $wiki->title }}</a>
                    </li>
                @endforeach
            </x-global::elements.link-dropdown>
         @endif
        </h1>
    </div>
</div>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}

    @if ((! $currentArticle || $currentArticle->id != null) && (! $wikis || count($wikis) == 0))
        {{-- No wikis exist - show empty state --}}
        <div class="wiki-empty-state">
            <div class="wiki-empty-state-icon svgContainer">
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_book_reading_re_fu2c.svg') !!}
            </div>
            <h3 class="wiki-empty-state-title">{{ __('headlines.no_articles_yet') }}</h3>
            <p class="wiki-empty-state-text">{{ __('text.create_new_wiki') }}</p>
            <x-global::button link="#/wiki/wikiModal/" type="primary" class="inlineEdit">{{ __('links.icon.create_new_board') }}</x-global::button>
        </div>

    @elseif ($wikis && count($wikis) > 0)

        @if ($currentArticle && $currentArticle->id != null)
            {{-- Single Panel Layout: Contents | Document | Properties (all inside) --}}
            <div class="wiki-layout">

                {{-- Main Content Area (contains everything) --}}
                <main class="wiki-content">

                    {{-- Three-panel layout inside --}}
                    <div class="wiki-content-layout">

                        {{-- Left: Contents Sidebar --}}
                        <div class="wiki-contents-panel" id="contentsPanel">
                            <div class="wiki-panel-header">
                                <h4 class="widgettitle title-light"><i class="fa fa-list"></i> Contents</h4>
                                <button class="wiki-collapse-btn" id="toggleContents" title="Collapse">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                            </div>

                            <nav id="article-toc-wrapper">
                                @php
                                    createModernTreeView($wikiHeadlines, 0, $currentArticle->id, 0, $tpl);
                                @endphp
                            </nav>

                            @if ($login::userIsAtLeast($roles::$editor))
                                <button class="wiki-create-btn"
                                        hx-post="{{ BASE_URL }}/hx/wiki/articleContent/create"
                                        hx-swap="none">
                                    <i class="fa fa-plus"></i>
                                    <span>{{ __('link.create_article') }}</span>
                                </button>
                            @endif
                        </div>

                        {{-- Toggle for collapsed Contents --}}
                        <button class="wiki-panel-toggle left" id="showContentsBtn" title="Show Contents">
                            <i class="fa fa-chevron-right"></i>
                        </button>

                        <div class="wiki-content-inner">

                        {{-- Toggle for collapsed Details --}}
                        <button class="wiki-panel-toggle right" id="showPropertiesBtn" title="Show Details">
                            <i class="fa fa-chevron-left"></i>
                        </button>

                            {{-- Document Header --}}
                            <header class="wiki-document-header">
                                @if ($login::userIsAtLeast($roles::$editor))
                                    {{-- Editable Title with Icon Picker --}}
                                    <div class="wiki-title-wrapper" id="wikiTitleWrapper">
                                        <div class="wiki-icon-picker">
                                            <button data-selected="graduation-cap" type="button"
                                                    class="icp icp-dd btn btn-default dropdown-toggle iconpicker-container titleIconPicker"
                                                    data-toggle="dropdown"
                                                    title="Change icon">
                                                <span class="iconPlaceholder"><i class="{{ $tpl->escape($currentArticle->data ?: 'fa fa-file-alt') }}"></i></span>
                                                <span class="wiki-icon-caret"><i class="fa fa-chevron-down"></i></span>
                                            </button>
                                            <div class="dropdown-menu"></div>
                                        </div>
                                        <input type="hidden" id="wikiArticleIcon" class="articleIcon" value="{{ $tpl->escape($currentArticle->data) }}" />
                                        <input type="text"
                                               id="wikiTitleEditable"
                                               class="wiki-title-editable main-title-input"
                                               value="{{ $tpl->escape($currentArticle->title) }}"
                                               data-original="{{ $tpl->escape($currentArticle->title) }}"
                                               placeholder="{{ __('input.placeholders.wiki_title') }}"
                                               style="width:80%" autocomplete="off" />
                                    </div>

                                    {{-- Editable Tags --}}
                                    <div class="wiki-tags-wrapper">
                                        <input type="text"
                                               id="wikiTagsInput"
                                               class="wiki-tags-input"
                                               data-role="tagsinput"
                                               value="{{ $tpl->escape($currentArticle->tags ?? '') }}"
                                               placeholder="Add tags..." />
                                    </div>
                                @else
                                    <h1 class="wiki-document-title">
                                        <i class="article-icon {{ $tpl->escape($currentArticle->data) }}"></i>
                                        {{ $currentArticle->title }}
                                    </h1>

                                    @php
                                        $tagsArray = array_filter(explode(',', $currentArticle->tags ?? ''));
                                    @endphp
                                    @if (count($tagsArray) > 0)
                                        <div class="wiki-document-tags">
                                            @foreach ($tagsArray as $tag)
                                                @php $tag = trim($tag); @endphp
                                                @if (! empty($tag))
                                                    <span class="wiki-document-tag">{{ $tag }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </header>

                        {{-- Document Body - Click to Edit --}}
                        <div class="wiki-document-wrapper" id="wikiDocumentWrapper" data-article-id="{{ $currentArticle->id }}">
                            @if ($login::userIsAtLeast($roles::$editor))
                                {{-- Hidden textarea for Tiptap --}}
                                <textarea id="wikiArticleContent" class="wiki-editor-textarea" style="display:none;">{!! $tpl->escapeMinimal($currentArticle->description) !!}</textarea>
                                {{-- Tiptap editor will be initialized here --}}
                                <div id="wikiTiptapEditor" class="wiki-document"></div>
                                {{-- Edit mode indicator --}}
                                <div class="wiki-edit-indicator" id="wikiEditIndicator" style="display: none;">
                                    <i class="fa fa-circle"></i>
                                    <span>Editing</span>
                                </div>
                            @else
                                {{-- Read-only view for non-editors --}}
                                <article class="wiki-document" id="wikiDocumentContent">
                                    {!! $tpl->escapeMinimal($currentArticle->description) !!}
                                </article>
                            @endif
                        </div>

                        @if (! empty($currentArticle->milestoneHeadline))
                            <div class="wiki-milestone-card">
                                <div hx-trigger="load"
                                     hx-indicator=".htmx-indicator"
                                     hx-target="closest .wiki-milestone-card"
                                     hx-select="unset"
                                     hx-swap="innerHTML"
                                     hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $currentArticle->milestoneId }}">
                                    <div class="htmx-indicator">
                                        {{ __('label.loading_milestone') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Comments Section --}}
                        <section class="wiki-comments-section" id="comments">
                            <h4 class="wiki-comments-title">
                                <i class="fa fa-comments"></i>
                                {{ __('subtitles.discussion') }}
                            </h4>

                            <form method="post" action="{{ BASE_URL }}/wiki/show/{{ $currentArticle->id }}#comment">
                                <input type="hidden" name="comment" value="1" />
                                @php
                                    $tpl->assign('formUrl', BASE_URL . '/wiki/show/' . $currentArticle->id);
                                    $tpl->displaySubmodule('comments-generalComment');
                                @endphp
                            </form>
                        </section>

                        </div>{{-- /.wiki-content-inner --}}

                        {{-- Properties Panel (inside content area) --}}
                        <div class="wiki-properties-panel" id="propertiesPanel">
                            <div class="wiki-panel-header">
                                <h4 class="widgettitle title-light"><i class="fa fa-info-circle"></i> Details</h4>
                                <button class="wiki-collapse-btn" id="collapseProperties" title="Collapse">
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>

                            {{-- Properties Section --}}
                            <div class="wiki-properties-section">

                                {{-- Status Dropdown --}}
                                <div class="wiki-property-row">
                                    <span class="wiki-property-label">
                                        <i class="fa fa-circle-dot"></i> Status
                                    </span>
                                    <span class="wiki-property-value">
                                        @if ($login::userIsAtLeast($roles::$editor))
                                            <div class="wiki-status-dropdown dropdown" id="wikiStatusDropdown">
                                                <a href="javascript:void(0);" data-toggle="dropdown" class="dropdown-toggle wiki-status-pill {{ $currentArticle->status }}">
                                                    @if ($currentArticle->status === 'draft')
                                                        <i class="fa fa-pencil"></i> Draft
                                                    @else
                                                        <i class="fa fa-check"></i> Published
                                                    @endif
                                                    <i class="fa fa-chevron-down"></i>
                                                </a>
                                                <ul class="dropdown-menu wiki-status-menu">
                                                    <li><a href="javascript:void(0)" class="wiki-status-option draft-option" data-value="draft"><i class="fa fa-pencil"></i> Draft</a></li>
                                                    <li><a href="javascript:void(0)" class="wiki-status-option published-option" data-value="published"><i class="fa fa-check"></i> Published</a></li>
                                                </ul>
                                            </div>
                                        @else
                                            <span class="wiki-status-badge {{ $currentArticle->status }}">
                                                {{ ucfirst($currentArticle->status) }}
                                            </span>
                                        @endif
                                    </span>
                                </div>

                                @php
                                    // Find parent article name
                                    $parentName = 'None';
                                    if ($currentArticle->parent && $currentArticle->parent > 0) {
                                        foreach ($wikiHeadlines as $headline) {
                                            if ($headline->id == $currentArticle->parent) {
                                                $parentName = $tpl->escape($headline->title);
                                                break;
                                            }
                                        }
                                    }
                                @endphp

                                {{-- Parent --}}
                                <div class="wiki-property-row">
                                    <span class="wiki-property-label">
                                        <i class="fa fa-folder-tree"></i> Parent
                                    </span>
                                    <span class="wiki-property-value">
                                        @if ($login::userIsAtLeast($roles::$editor))
                                            <div class="wiki-parent-dropdown dropdown" id="wikiParentDropdown">
                                                <a href="javascript:void(0);" data-toggle="dropdown" class="dropdown-toggle wiki-milestone-btn">
                                                    <span class="parent-text{{ (! $currentArticle->parent || $currentArticle->parent == 0) ? ' none' : '' }}">{{ $parentName }}</span>
                                                    <i class="fa fa-chevron-down"></i>
                                                </a>
                                                <ul class="dropdown-menu wiki-milestone-menu">
                                                    <li><a href="javascript:void(0)" class="wiki-parent-option{{ (! $currentArticle->parent || $currentArticle->parent == 0) ? ' active' : '' }}" data-value="0"><i class="fa fa-times"></i> None</a></li>
                                                    @php
                                                        $parentOptions = array_filter($wikiHeadlines, function ($h) use ($currentArticle) {
                                                            return $h->id != $currentArticle->id;
                                                        });
                                                    @endphp
                                                    @if (count($parentOptions) > 0)
                                                        <li class="divider"></li>
                                                        @foreach ($parentOptions as $headline)
                                                            <li>
                                                                <a href="javascript:void(0)"
                                                                   class="wiki-parent-option{{ $currentArticle->parent == $headline->id ? ' active' : '' }}"
                                                                   data-value="{{ $headline->id }}">
                                                                    <i class="{{ $headline->data ?: 'fa fa-file-alt' }}"></i> {{ $headline->title }}@if ($headline->status === 'draft') <span class="wiki-tree-draft">({{ __('label.draft') }})</span>@endif
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                        @else
                                            @if ($currentArticle->parent && $currentArticle->parent > 0)
                                                <a href="{{ BASE_URL }}/wiki/show/{{ $currentArticle->parent }}" class="wiki-parent-link">
                                                    {{ $parentName }}
                                                </a>
                                            @else
                                                <span class="wiki-no-parent">{{ $parentName }}</span>
                                            @endif
                                        @endif
                                    </span>
                                </div>

                                {{-- Author --}}
                                <div class="wiki-property-row">
                                    <span class="wiki-property-label">
                                        <i class="fa fa-user"></i> Author
                                    </span>
                                    <span class="wiki-property-value">
                                        <div class="wiki-author">
                                            <span class="wiki-author-avatar">{{ $authorInitials }}</span>
                                            {{ $tpl->escape($currentArticle->firstname) }} {{ $tpl->escape($currentArticle->lastname) }}
                                        </div>
                                    </span>
                                </div>

                                {{-- Milestone --}}
                                <div class="wiki-property-row">
                                    <span class="wiki-property-label">
                                        <i class="fa fa-flag"></i> Milestone
                                    </span>
                                    <span class="wiki-property-value">
                                        @if ($login::userIsAtLeast($roles::$editor))
                                            <div class="wiki-milestone-dropdown dropdown" id="wikiMilestoneDropdown">
                                                <a href="javascript:void(0);" data-toggle="dropdown" class="dropdown-toggle wiki-milestone-btn">
                                                    @if (! empty($currentArticle->milestoneHeadline))
                                                        <span class="milestone-text">{{ $currentArticle->milestoneHeadline }}</span>
                                                    @else
                                                        <span class="milestone-text none">None</span>
                                                    @endif
                                                    <i class="fa fa-chevron-down"></i>
                                                </a>
                                                <ul class="dropdown-menu wiki-milestone-menu">
                                                    <li><a href="javascript:void(0)" class="wiki-milestone-option" data-value="0"><i class="fa fa-times"></i> None</a></li>
                                                    @if (count($milestones) > 0)
                                                        <li class="divider"></li>
                                                        @foreach ($milestones as $milestone)
                                                            <li>
                                                                <a href="javascript:void(0)"
                                                                   class="wiki-milestone-option{{ $currentArticle->milestoneId == $milestone->id ? ' active' : '' }}"
                                                                   data-value="{{ $milestone->id }}">
                                                                    <i class="fa fa-flag"></i> {{ $milestone->headline }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                        @else
                                            @if (! empty($currentArticle->milestoneHeadline))
                                                <a href="#/tickets/editMilestone/{{ $currentArticle->milestoneId }}" class="wiki-milestone-link">
                                                    {{ $currentArticle->milestoneHeadline }}
                                                </a>
                                            @else
                                                <span class="wiki-no-milestone">None</span>
                                            @endif
                                        @endif
                                    </span>
                                </div>

                                {{-- Last Saved --}}
                                <div class="wiki-property-row">
                                    <span class="wiki-property-label">
                                        <i class="fa fa-clock"></i> Last Saved
                                    </span>
                                    <span class="wiki-property-value" id="wikiLastSaved" data-timestamp="{{ $currentArticle->modified }}">
                                        {{ format($currentArticle->modified)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            {{-- Activity Section --}}
                            <div class="wiki-properties-section wiki-activity-section">
                                <h6 class="wiki-properties-section-title">Activity</h6>

                                <div id="wikiActivityContainer"
                                     hx-get="{{ BASE_URL }}/hx/wiki/articleActivity?articleId={{ $currentArticle->id }}"
                                     hx-trigger="load, refreshActivity from:body"
                                     hx-target="this"
                                     hx-select="unset"
                                     hx-swap="innerHTML">
                                    <div class="wiki-activity-loading">
                                        <i class="fa fa-circle-notch fa-spin"></i> Loading activity...
                                    </div>
                                </div>
                            </div>

                            {{-- Delete (pinned to bottom) --}}
                            @if ($login::userIsAtLeast($roles::$editor))
                            <div class="wiki-properties-footer">
                                <a href="#/wiki/delArticle/{{ $currentArticle->id }}" class="wiki-action-btn delete">
                                    <i class="fa fa-trash"></i> Delete Article
                                </a>
                            </div>
                            @endif

                        </div>{{-- /.wiki-properties-panel --}}
                    </div>{{-- /.wiki-content-layout --}}
                </main>

            </div>

        @else
            {{-- Wiki exists but no articles yet --}}
            <div class="wiki-empty-state">
                <div class="wiki-empty-state-icon svgContainer" style="width: 200px; margin: 0 auto;">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_book_reading_re_fu2c.svg') !!}
                </div>
                <h3 class="wiki-empty-state-title">{{ __('headlines.no_articles_yet') }}</h3>
                <p class="wiki-empty-state-text">{{ __('text.create_new_content') }}</p>
                <x-global::button tag="button" type="primary"
                        hx-post="{{ BASE_URL }}/hx/wiki/articleContent/create"
                        hx-swap="none"
                        icon="fa fa-plus">{{ __('link.create_article') }}</x-global::button>
            </div>
        @endif

    @endif

</div>

<script type="text/javascript">
jQuery(document).ready(function() {

    // ==========================================
    // Panel Toggle Functionality
    // ==========================================

    // Toggle contents panel (collapse / expand)
    jQuery('#toggleContents').on('click', function() {
        var panel = jQuery('#contentsPanel');
        var showBtn = jQuery('#showContentsBtn');
        panel.addClass('collapsed');
        showBtn.addClass('visible');
        localStorage.setItem('wikiContentsCollapsed', 'true');
    });

    // Show contents panel when clicking the expand button
    jQuery('#showContentsBtn').on('click', function() {
        var panel = jQuery('#contentsPanel');
        var showBtn = jQuery('#showContentsBtn');
        panel.removeClass('collapsed');
        showBtn.removeClass('visible');
        localStorage.setItem('wikiContentsCollapsed', 'false');
    });

    // Toggle properties panel (collapse / expand)
    jQuery('#collapseProperties').on('click', function() {
        var panel = jQuery('#propertiesPanel');
        var showBtn = jQuery('#showPropertiesBtn');
        panel.addClass('collapsed');
        showBtn.addClass('visible');
        localStorage.setItem('wikiPropertiesCollapsed', 'true');
    });

    // Show properties panel when clicking the expand button
    jQuery('#showPropertiesBtn').on('click', function() {
        var panel = jQuery('#propertiesPanel');
        var showBtn = jQuery('#showPropertiesBtn');
        panel.removeClass('collapsed');
        showBtn.removeClass('visible');
        localStorage.setItem('wikiPropertiesCollapsed', 'false');
    });

    // Responsive: auto-collapse panels on smaller screens
    var isSmallScreen = window.innerWidth <= 1280;

    // Restore contents panel state (or auto-collapse on small screens)
    if (isSmallScreen || localStorage.getItem('wikiContentsCollapsed') === 'true') {
        jQuery('#contentsPanel').addClass('collapsed');
        jQuery('#showContentsBtn').addClass('visible');
    }

    // Restore properties panel state (or auto-collapse on small screens)
    if (isSmallScreen || localStorage.getItem('wikiPropertiesCollapsed') === 'true') {
        jQuery('#propertiesPanel').addClass('collapsed');
        jQuery('#showPropertiesBtn').addClass('visible');
    }

    // Handle window resize
    var resizeTimeout;
    jQuery(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            var nowSmall = window.innerWidth <= 1280;
            if (nowSmall) {
                // Auto-collapse both panels on small screens
                jQuery('#contentsPanel').addClass('collapsed');
                jQuery('#showContentsBtn').addClass('visible');
                jQuery('#propertiesPanel').addClass('collapsed');
                jQuery('#showPropertiesBtn').addClass('visible');
            }
        }, 150);
    });

    // ==========================================
    // Click-to-Edit with Tiptap
    // ==========================================

    @if ($currentArticle && $login::userIsAtLeast($roles::$editor))
    (function() {
        var articleId = @json($currentArticle->id);
        var wrapper = document.getElementById('wikiDocumentWrapper');
        var editorEl = document.getElementById('wikiTiptapEditor');
        var textarea = document.getElementById('wikiArticleContent');
        var indicator = document.getElementById('wikiEditIndicator');

        if (!editorEl || !textarea || !window.leantime || !window.leantime.tiptapController) {
            console.warn('[Wiki] Tiptap controller not available');
            return;
        }

        var isEditing = false;
        var saveTimeout = null;
        var lastSavedContent = textarea.value;
        var toolbarClicking = false;

        // Track mousedown on the wrapper so we know if a blur was caused
        // by clicking within the editing UI (toolbar buttons, etc.)
        wrapper.addEventListener('mousedown', function(e) {
            if (isEditing && !editorEl.contains(e.target)) {
                toolbarClicking = true;
            }
        });

        // Initialize Tiptap in read mode
        var tiptapInstance = leantime.tiptapController.initComplex(textarea, {
            placeholder: 'Click anywhere to start editing...',
            toolbar: false,
            autosave: false,
            onCreate: function(params) {
                params.editor.setEditable(false);
                console.log('[Wiki] Tiptap initialized in read mode');
            },
            onUpdate: function(params) {
                if (isEditing) {
                    clearTimeout(saveTimeout);
                    showIndicator('saving');
                    saveTimeout = setTimeout(function() {
                        saveContent(params.editor.getHTML());
                    }, 1500);
                }
            },
            onBlur: function(params) {
                // If the blur was caused by clicking toolbar/wrapper elements, skip
                if (toolbarClicking) {
                    toolbarClicking = false;
                    return;
                }

                // Delay to allow for toolbar popover clicks (color, heading, font
                // pickers are appended to document.body outside the wrapper)
                setTimeout(function() {
                    if (!isEditing) return;

                    var active = document.activeElement;

                    // Check if focus moved to something inside the wrapper
                    if (wrapper.contains(active)) return;

                    // Check if a tiptap popover is currently open (they are
                    // appended to document.body so won't be inside wrapper)
                    var openPopover = document.querySelector(
                        '.tiptap-color-popover, .tiptap-font-popover, .tiptap-heading-popover, .tiptap-image-popover'
                    );
                    if (openPopover) return;

                    exitEditMode();
                }, 300);
            }
        });

        if (!tiptapInstance) {
            console.error('[Wiki] Failed to initialize Tiptap');
            return;
        }

        var editor = tiptapInstance.editor;

        // Show toolbar after initialization
        function showToolbar() {
            if (window.leantime.tiptapToolbar) {
                var toolbar = window.leantime.tiptapToolbar.create(editor, 'complex');
                var tiptapEditorEl = wrapper.querySelector('.tiptap-editor');
                window.leantime.tiptapToolbar.attach({ element: tiptapEditorEl || editorEl }, toolbar);
            }
        }

        // Hide toolbar
        function hideToolbar() {
            var toolbarEl = wrapper.querySelector('.tiptap-toolbar');
            if (toolbarEl) {
                toolbarEl.remove();
            }
        }

        // Enter edit mode
        function enterEditMode() {
            if (isEditing) return;
            isEditing = true;

            editor.setEditable(true);
            wrapper.classList.add('editing');
            showToolbar();
            showIndicator('editing');

            editor.commands.focus('end');

            console.log('[Wiki] Entered edit mode');
        }

        // Exit edit mode
        function exitEditMode() {
            if (!isEditing) return;

            var currentContent = editor.getHTML();
            if (currentContent !== lastSavedContent) {
                saveContent(currentContent);
            }

            isEditing = false;
            editor.setEditable(false);
            wrapper.classList.remove('editing');
            hideToolbar();
            hideIndicator();

            console.log('[Wiki] Exited edit mode');
        }

        // Save content via fetch
        function saveContent(content) {
            showIndicator('saving');

            fetch(leantime.appUrl + '/hx/wiki/articleContent/save?articleId=' + articleId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include',
                body: 'description=' + encodeURIComponent(content)
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    lastSavedContent = content;
                    showIndicator('saved');
                    updateLastSaved();
                    setTimeout(function() {
                        if (!isEditing) {
                            hideIndicator();
                        }
                    }, 2000);
                } else {
                    showIndicator('error');
                }
            })
            .catch(function(err) {
                console.error('[Wiki] Save failed:', err);
                showIndicator('error');
            });
        }

        // Show edit indicator
        function showIndicator(state) {
            if (!indicator) return;

            indicator.style.display = 'flex';
            indicator.className = 'wiki-edit-indicator ' + state;

            var icon = indicator.querySelector('i');
            var text = indicator.querySelector('span');

            switch (state) {
                case 'editing':
                    icon.className = 'fa fa-edit';
                    text.textContent = 'Editing';
                    break;
                case 'saving':
                    icon.className = 'fa fa-circle-notch fa-spin';
                    text.textContent = 'Saving...';
                    break;
                case 'saved':
                    icon.className = 'fa fa-check';
                    text.textContent = 'Saved';
                    break;
                case 'error':
                    icon.className = 'fa fa-exclamation-triangle';
                    text.textContent = 'Save failed';
                    break;
            }
        }

        // Hide indicator
        function hideIndicator() {
            if (indicator) {
                indicator.style.display = 'none';
            }
        }

        // Click handler for the document wrapper - enters edit mode
        wrapper.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                return;
            }
            enterEditMode();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Cmd/Ctrl + E to toggle edit mode
            if ((e.metaKey || e.ctrlKey) && e.key === 'e') {
                e.preventDefault();
                if (isEditing) {
                    exitEditMode();
                } else {
                    enterEditMode();
                }
            }

            // Escape to exit edit mode
            if (e.key === 'Escape' && isEditing) {
                e.preventDefault();
                exitEditMode();
            }

            // Cmd/Ctrl + S to save
            if ((e.metaKey || e.ctrlKey) && e.key === 's' && isEditing) {
                e.preventDefault();
                saveContent(editor.getHTML());
            }
        });

        // Save before leaving page
        window.addEventListener('beforeunload', function(e) {
            if (isEditing) {
                var currentContent = editor.getHTML();
                if (currentContent !== lastSavedContent) {
                    navigator.sendBeacon(
                        leantime.appUrl + '/hx/wiki/articleContent/save?articleId=' + articleId,
                        new URLSearchParams({ description: currentContent })
                    );
                }
            }
        });

    })();

    // ==========================================
    // Title Editing (contenteditable)
    // ==========================================

    var titleEditable = document.getElementById('wikiTitleEditable');
    if (titleEditable) {
        var originalTitle = titleEditable.dataset.original;

        titleEditable.addEventListener('blur', function() {
            var newTitle = titleEditable.value.trim();
            if (newTitle && newTitle !== originalTitle) {
                saveField('title', newTitle, function() {
                    originalTitle = newTitle;
                    titleEditable.dataset.original = newTitle;
                    var treeLink = document.querySelector('.wiki-tree-link.active span');
                    if (treeLink) {
                        treeLink.textContent = newTitle;
                    }
                    updateLastSaved();
                });
            }
        });

        titleEditable.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                titleEditable.blur();
            }
            if (e.key === 'Escape') {
                titleEditable.value = originalTitle;
                titleEditable.blur();
            }
        });
    }

    // ==========================================
    // Icon Picker
    // ==========================================

    var iconInput = document.getElementById('wikiArticleIcon');
    if (iconInput && jQuery.fn.iconpicker) {
        jQuery('.titleIconPicker').iconpicker({
            component: '.btn > .iconPlaceholder',
            input: '.articleIcon',
            inputSearch: true,
            defaultValue: 'far fa-file-alt',
            selected: iconInput.value || 'far fa-file-alt',
            showFooter: false,
            searchInFooter: false,
            icons: [
                {title: "far fa-file-alt", searchTerms:['icons']},
                {title: "fab fa-accessible-icon", searchTerms:['icons']},
                {title: "far fa-address-book", searchTerms:['icons']},
                {title: "fas fa-archive", searchTerms:['icons']},
                {title: "fas fa-asterisk", searchTerms:['icons']},
                {title: "fas fa-balance-scale", searchTerms:['icons']},
                {title: "fas fa-ban", searchTerms:['icons']},
                {title: "fas fa-bell", searchTerms:['icons']},
                {title: "fas fa-binoculars", searchTerms:['icons']},
                {title: "fas fa-birthday-cake", searchTerms:['icons']},
                {title: "fas fa-bolt", searchTerms:['icons']},
                {title: "fas fa-book", searchTerms:['icons']},
                {title: "fas fa-bookmark", searchTerms:['icons']},
                {title: "fas fa-briefcase", searchTerms:['icons']},
                {title: "fas fa-bug", searchTerms:['icons']},
                {title: "far fa-building", searchTerms:['icons']},
                {title: "fas fa-bullhorn", searchTerms:['icons']},
                {title: "far fa-calendar-alt", searchTerms:['icons']},
                {title: "fas fa-chart-bar", searchTerms:['icons']},
                {title: "fas fa-check-circle", searchTerms:['icons']},
                {title: "fas fa-chart-line", searchTerms:['icons']},
                {title: "fas fa-chess", searchTerms:['icons']},
                {title: "fas fa-cogs", searchTerms:['icons']},
                {title: "fas fa-comments", searchTerms:['icons']},
                {title: "fas fa-compass", searchTerms:['icons']},
                {title: "fas fa-database", searchTerms:['icons']},
                {title: "fas fa-envelope", searchTerms:['icons']},
                {title: "fas fa-exclamation-triangle", searchTerms:['icons']},
                {title: "fas fa-flask", searchTerms:['icons']},
                {title: "fas fa-globe", searchTerms:['icons']},
                {title: "fas fa-gem", searchTerms:['icons']},
                {title: "fas fa-graduation-cap", searchTerms:['icons']},
                {title: "fas fa-hand-spock", searchTerms:['icons']},
                {title: "fas fa-heart", searchTerms:['icons']},
                {title: "fas fa-home", searchTerms:['icons']},
                {title: "fas fa-image", searchTerms:['icons']},
                {title: "fas fa-info-circle", searchTerms:['icons']},
                {title: "fas fa-key", searchTerms:['icons']},
                {title: "fas fa-leaf", searchTerms:['icons']},
                {title: "fas fa-life-ring", searchTerms:['icons']},
                {title: "fas fa-lightbulb", searchTerms:['icons']},
                {title: "fas fa-link", searchTerms:['icons']},
                {title: "fas fa-location-arrow", searchTerms:['icons']},
                {title: "fas fa-lock", searchTerms:['icons']},
                {title: "fas fa-map", searchTerms:['icons']},
                {title: "fas fa-map-signs", searchTerms:['icons']},
                {title: "fas fa-money-bill-alt", searchTerms:['icons']},
                {title: "fas fa-paper-plane", searchTerms:['icons']},
                {title: "fas fa-paperclip", searchTerms:['icons']},
                {title: "fas fa-question-circle", searchTerms:['icons']},
                {title: "fas fa-quote-left", searchTerms:['icons']},
                {title: "fas fa-road", searchTerms:['icons']},
                {title: "fas fa-rocket", searchTerms:['icons']},
                {title: "fas fa-shopping-cart", searchTerms:['icons']},
                {title: "fas fa-sitemap", searchTerms:['icons']},
                {title: "fas fa-sliders-h", searchTerms:['icons']},
                {title: "fas fa-star", searchTerms:['icons']},
                {title: "fas fa-tachometer-alt", searchTerms:['icons']},
                {title: "fas fa-thermometer-half", searchTerms:['icons']},
                {title: "fas fa-thumbs-down", searchTerms:['icons']},
                {title: "fas fa-thumbs-up", searchTerms:['icons']},
                {title: "fas fa-trash-alt", searchTerms:['icons']},
                {title: "fas fa-trophy", searchTerms:['icons']},
                {title: "fas fa-user-circle", searchTerms:['icons']},
                {title: "fas fa-utensils", searchTerms:['icons']}
            ]
        });

        jQuery('.titleIconPicker').on('iconpickerSelected', function(event) {
            var newIcon = event.iconpickerValue;
            jQuery('.articleIcon').val(newIcon);
            jQuery('.titleIconPicker .iconPlaceholder > i').attr('class', newIcon);

            saveField('icon', newIcon, function() {
                var treeLink = document.querySelector('.wiki-tree-link.active i');
                if (treeLink) {
                    treeLink.className = newIcon;
                }
                updateLastSaved();
            });
        });
    }

    // ==========================================
    // Tags Input
    // ==========================================

    var tagsInput = document.getElementById('wikiTagsInput');
    if (tagsInput && jQuery.fn.tagsInput) {
        jQuery('#wikiTagsInput').tagsInput({
            width: '100%',
            height: 'auto',
            defaultText: 'Add tag...',
            placeholderColor: 'var(--secondary-font-color)',
            onChange: function(elem, elem_tags) {
                saveField('tags', elem_tags, function() {
                    updateLastSaved();
                });
            }
        });
    }

    // ==========================================
    // Status Select
    // ==========================================

    var statusDropdown = document.getElementById('wikiStatusDropdown');
    if (statusDropdown) {
        var statusOptions = statusDropdown.querySelectorAll('.wiki-status-option');
        var statusPill = statusDropdown.querySelector('.wiki-status-pill');

        statusOptions.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                var newStatus = option.dataset.value;
                var currentClass = statusPill.classList.contains('draft') ? 'draft' : 'published';

                if (newStatus !== currentClass) {
                    saveField('status', newStatus, function() {
                        statusPill.classList.remove('draft', 'published');
                        statusPill.classList.add(newStatus);

                        if (newStatus === 'draft') {
                            statusPill.innerHTML = '<i class="fa fa-pencil"></i> Draft <i class="fa fa-chevron-down"></i>';
                        } else {
                            statusPill.innerHTML = '<i class="fa fa-check"></i> Published <i class="fa fa-chevron-down"></i>';
                        }

                        var activeLink = document.querySelector('.wiki-tree-link.active');
                        if (activeLink) {
                            var draftLabel = activeLink.querySelector('.wiki-tree-draft');
                            if (newStatus === 'draft') {
                                if (!draftLabel) {
                                    draftLabel = document.createElement('span');
                                    draftLabel.className = 'wiki-tree-draft';
                                    activeLink.appendChild(draftLabel);
                                }
                                draftLabel.textContent = '({{ __('label.draft') }})';
                            } else if (draftLabel) {
                                draftLabel.remove();
                            }
                        }

                        updateLastSaved();
                    });
                }
            });
        });
    }

    // ==========================================
    // Milestone Select
    // ==========================================

    var milestoneDropdown = document.getElementById('wikiMilestoneDropdown');
    if (milestoneDropdown) {
        var milestoneOptions = milestoneDropdown.querySelectorAll('.wiki-milestone-option');
        var milestoneBtn = milestoneDropdown.querySelector('.wiki-milestone-btn');
        var milestoneText = milestoneBtn.querySelector('.milestone-text');

        milestoneOptions.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                var newMilestoneId = option.dataset.value;

                milestoneOptions.forEach(function(opt) {
                    opt.classList.remove('active');
                });
                option.classList.add('active');

                saveField('milestoneId', newMilestoneId, function() {
                    window.location.reload();
                });
            });
        });
    }

    // ==========================================
    // Parent Select
    // ==========================================

    var parentDropdown = document.getElementById('wikiParentDropdown');
    if (parentDropdown) {
        var parentOptions = parentDropdown.querySelectorAll('.wiki-parent-option');
        var parentBtn = parentDropdown.querySelector('.wiki-milestone-btn');
        var parentText = parentBtn.querySelector('.parent-text');

        parentOptions.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                var newParentId = option.dataset.value;

                parentOptions.forEach(function(opt) {
                    opt.classList.remove('active');
                });
                option.classList.add('active');

                saveField('parent', newParentId, function() {
                    window.location.reload();
                });
            });
        });
    }

    // ==========================================
    // Last Saved Update
    // ==========================================

    function updateLastSaved() {
        var lastSavedEl = document.getElementById('wikiLastSaved');
        if (lastSavedEl) {
            lastSavedEl.textContent = 'Just now';
            lastSavedEl.dataset.timestamp = new Date().toISOString();
        }
        htmx.trigger(document.body, 'refreshActivity');
    }

    // Generic field save function
    function saveField(field, value, onSuccess) {
        var articleId = @json($currentArticle->id);

        fetch(leantime.appUrl + '/hx/wiki/articleContent/save?articleId=' + articleId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include',
            body: field + '=' + encodeURIComponent(value)
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                if (onSuccess) onSuccess(data);
                console.log('[Wiki] ' + field + ' saved');
            } else {
                console.error('[Wiki] Failed to save ' + field);
            }
        })
        .catch(function(err) {
            console.error('[Wiki] Save failed:', err);
        });
    }

    @endif

    @if ($login::userHasRole([$roles::$commenter]))
    leantime.commentsController.enableCommenterForms();
    @endif

});
</script>
