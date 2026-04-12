@extends($layout)

@section('content')

@php
    $wikis = $wikis ?? [];
    $wikiHeadlines = $wikiHeadlines ?? [];
    $milestones = $milestones ?? [];
    $currentWiki = $currentWiki ?? false;
    $currentArticle = $currentArticle ?? null;

    /**
     * Creates a modern tree view for wiki navigation
     */
    function createModernTreeView($array, $currentParent, $currentArticleId, int $currLevel = 0, ?\Leantime\Core\UI\Template $tplObject = null): void
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

        <h5>{{ session('currentProjectClient') }}</h5>

        @if(count($wikis) > 0)
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    @if($login::userIsAtLeast($roles::$editor) && $currentWiki)
                        <li><a class="inlineEdit" href="#/wiki/wikiModal/{{ $currentWiki->id }}">{!! __('link.edit_wiki') !!}</a></li>
                        <li><a class="delete" href="#/wiki/delWiki/{{ $currentWiki->id }}"><i class="fa fa-trash"></i> {!! __('links.delete_wiki') !!}</a></li>
                    @endif
                </ul>
            </span>
        @endif

        <h1>{!! __('headlines.documents') !!}
            @if(count($wikis) > 0)
                //
                <span class="dropdown dropdownWrapper">
                    <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                        @if($currentWiki !== false)
                            {{ $currentWiki->title }}
                        @else
                            {!! __('label.select_board') !!}
                        @endif
                        <i class="fa fa-caret-down"></i>
                    </a>

                    <ul class="dropdown-menu">
                        <li><a class="inlineEdit" href="#/wiki/wikiModal/">{!! __('link.new_wiki') !!}</a></li>
                        <li class='nav-header border'></li>
                        @foreach($wikis as $wiki)
                            <li>
                                <a href="{{ BASE_URL . '/wiki/show?setWiki=' . $wiki->id }}">{{ $wiki->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </span>
            @endif
        </h1>
    </div>
</div>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}

    @if((! $currentArticle || $currentArticle->id != null) && (! $wikis || count($wikis) == 0))
        <!-- No wikis exist - show empty state -->
        <div class="wiki-empty-state">
            <div class="wiki-empty-state-icon svgContainer">
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_book_reading_re_fu2c.svg') !!}
            </div>
            <h3 class="wiki-empty-state-title">{!! __('headlines.no_articles_yet') !!}</h3>
            <p class="wiki-empty-state-text">{!! __('text.create_new_wiki') !!}</p>
            <a href='#/wiki/wikiModal/' class='inlineEdit btn btn-primary'>{!! __('links.icon.create_new_board') !!}</a>
        </div>

    @elseif($wikis && count($wikis) > 0)

        @if($currentArticle && $currentArticle->id != null)
            <!-- Single Panel Layout: Contents | Document | Properties (all inside) -->
            <div class="wiki-layout">

                <!-- Main Content Area (contains everything) -->
                <main class="wiki-content">

                    <!-- Three-panel layout inside -->
                    <div class="wiki-content-layout">

                        <!-- Left: Contents Sidebar -->
                        <div class="wiki-contents-panel" id="contentsPanel">
                            <div class="wiki-panel-header">
                                <h4 class="widgettitle title-light"><i class="fa fa-list"></i> Contents</h4>
                                <button class="wiki-collapse-btn" id="toggleContents" title="Collapse">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                            </div>

                            <nav id="article-toc-wrapper">
                                @php createModernTreeView($wikiHeadlines, 0, $currentArticle->id, 0, $tpl); @endphp
                            </nav>

                            @if($login::userIsAtLeast($roles::$editor))
                                <button class="wiki-create-btn"
                                        hx-post="{{ BASE_URL }}/hx/wiki/articleContent/create"
                                        hx-swap="none">
                                    <i class="fa fa-plus"></i>
                                    <span>{!! __('link.create_article') !!}</span>
                                </button>
                            @endif
                        </div>

                        <!-- Toggle for collapsed Contents -->
                        <button class="wiki-panel-toggle left" id="showContentsBtn" title="Show Contents">
                            <i class="fa fa-chevron-right"></i>
                        </button>

                        <div class="wiki-content-inner">

                        <!-- Toggle for collapsed Details -->
                        <button class="wiki-panel-toggle right" id="showPropertiesBtn" title="Show Details">
                            <i class="fa fa-chevron-left"></i>
                        </button>

                            <!-- Document Header -->
                            <header class="wiki-document-header">
                                @if($login::userIsAtLeast($roles::$editor))
                                    <!-- Editable Title with Icon Picker -->
                                    <div class="form-group" id="wikiTitleWrapper">
                                        <div class="btn-group inlineDropDownContainerLeft">
                                            <button data-selected="graduation-cap" type="button"
                                                    class="icp icp-dd btn btn-default dropdown-toggle iconpicker-container titleIconPicker"
                                                    data-toggle="dropdown">
                                                <span class="iconPlaceholder"><i class="{{ e($currentArticle->data ?: 'fa fa-file-alt') }}"></i></span>
                                                <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu"></div>
                                        </div>
                                        <input type="hidden" id="wikiArticleIcon" class="articleIcon" value="{{ e($currentArticle->data) }}" />
                                        <input type="text"
                                               id="wikiTitleEditable"
                                               class="main-title-input"
                                               value="{{ e($currentArticle->title) }}"
                                               data-original="{{ e($currentArticle->title) }}"
                                               placeholder="{{ __('input.placeholders.wiki_title') }}"
                                               style="width:80%" autocomplete="off" />
                                    </div>

                                    <!-- Editable Tags -->
                                    <div class="wiki-tags-wrapper">
                                        <input type="text"
                                               id="wikiTagsInput"
                                               class="wiki-tags-input"
                                               data-role="tagsinput"
                                               value="{{ e($currentArticle->tags ?? '') }}"
                                               placeholder="Add tags..." />
                                    </div>
                                @else
                                    <h1 class="wiki-document-title">
                                        <i class="article-icon {{ e($currentArticle->data) }}"></i>
                                        {{ $currentArticle->title }}
                                    </h1>

                                    @php $tagsArray = array_filter(explode(',', $currentArticle->tags ?? '')); @endphp
                                    @if(count($tagsArray) > 0)
                                        <div class="wiki-document-tags">
                                            @foreach($tagsArray as $tag)
                                                @php $tag = trim($tag); @endphp
                                                @if(! empty($tag))
                                                    <span class="wiki-document-tag">{{ $tag }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </header>

                        <!-- Document Body - Click to Edit -->
                        <div class="wiki-document-wrapper" id="wikiDocumentWrapper" data-article-id="{{ $currentArticle->id }}">
                            @if($login::userIsAtLeast($roles::$editor))
                                <!-- Hidden textarea for Tiptap -->
                                <textarea id="wikiArticleContent" class="wiki-editor-textarea" style="display:none;">{!! $tpl->escapeMinimal($currentArticle->description) !!}</textarea>
                                <!-- Tiptap editor will be initialized here -->
                                <div id="wikiTiptapEditor" class="wiki-document"></div>
                                <!-- Edit mode indicator -->
                                <div class="wiki-edit-indicator" id="wikiEditIndicator" style="display: none;">
                                    <i class="fa fa-circle"></i>
                                    <span>Editing</span>
                                </div>
                            @else
                                <!-- Read-only view for non-editors -->
                                <article class="wiki-document" id="wikiDocumentContent">
                                    {!! $tpl->escapeMinimal($currentArticle->description) !!}
                                </article>
                            @endif
                        </div>

                        @if(! empty($currentArticle->milestoneHeadline))
                            <div class="wiki-milestone-card">
                                <div hx-trigger="load"
                                     hx-indicator=".htmx-indicator"
                                     hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $currentArticle->milestoneId }}">
                                    <div class="htmx-indicator">
                                        {!! __('label.loading_milestone') !!}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Comments Section -->
                        <section class="wiki-comments-section" id="comments">
                            <h4 class="widgettitle title-light"><span class="fa-solid fa-comments"></span> {!! __('subtitles.discussion') !!}</h4>

                            <form method="post" action="{{ BASE_URL }}/wiki/show/{{ $currentArticle->id }}#comment">
                                <input type="hidden" name="comment" value="1" />
                                @php
                                    $tpl->assign('formUrl', BASE_URL . '/wiki/show/' . $currentArticle->id . '');
                                    $tpl->displaySubmodule('comments-generalComment');
                                @endphp
                            </form>
                        </section>

                        </div><!-- /.wiki-content-inner -->

                        <!-- Properties Panel (inside content area) -->
                        <div class="wiki-properties-panel" id="propertiesPanel">
                            <div class="wiki-panel-header">
                                <h4 class="widgettitle title-light"><i class="fa fa-info-circle"></i> Details</h4>
                                <button class="wiki-collapse-btn" id="collapseProperties" title="Collapse">
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>

                    <!-- Properties Section -->
                    <div class="wiki-properties-section">

                        <!-- Status Dropdown -->
                        <div class="form-group">
                            <label class="control-label">{!! __('label.status') !!}</label>
                            <div class="">
                                @if($login::userIsAtLeast($roles::$editor))
                                    <select id="wikiStatusSelect" class="span11">
                                        <option value="draft" @selected($currentArticle->status === 'draft')>Draft</option>
                                        <option value="published" @selected($currentArticle->status !== 'draft')>Published</option>
                                    </select>
                                @else
                                    {{ ucfirst($currentArticle->status) }}
                                @endif
                            </div>
                        </div>

                        @php
                            // Find parent article name
                            $parentName = 'None';
                            if ($currentArticle->parent && $currentArticle->parent > 0) {
                                foreach ($wikiHeadlines as $headline) {
                                    if ($headline->id == $currentArticle->parent) {
                                        $parentName = e($headline->title);
                                        break;
                                    }
                                }
                            }
                        @endphp

                        <!-- Parent -->
                        <div class="form-group">
                            <label class="control-label">Parent</label>
                            <div class="">
                                @if($login::userIsAtLeast($roles::$editor))
                                    @php
                                        $parentOptions = array_filter($wikiHeadlines, function ($h) use ($currentArticle) {
                                            return $h->id != $currentArticle->id;
                                        });
                                    @endphp
                                    <select id="wikiParentSelect" class="span11">
                                        <option value="0" @selected(! $currentArticle->parent || $currentArticle->parent == 0)>None</option>
                                        @foreach($parentOptions as $headline)
                                            <option value="{{ $headline->id }}" @selected($currentArticle->parent == $headline->id)>{{ $headline->title }}@if($headline->status === 'draft') ({!! __('label.draft') !!})@endif</option>
                                        @endforeach
                                    </select>
                                @else
                                    @if($currentArticle->parent && $currentArticle->parent > 0)
                                        <a href="{{ BASE_URL }}/wiki/show/{{ $currentArticle->parent }}">
                                            {{ $parentName }}
                                        </a>
                                    @else
                                        <span>{{ $parentName }}</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Milestone -->
                        <div class="form-group">
                            <label class="control-label">{!! __('label.milestone') !!}</label>
                            <div class="">
                                @if($login::userIsAtLeast($roles::$editor))
                                    <select id="wikiMilestoneSelect" class="span11">
                                        <option value="">{!! __('label.not_assigned_to_milestone') !!}</option>
                                        @foreach($milestones as $milestone)
                                            <option value="{{ $milestone->id }}" @selected($currentArticle->milestoneId == $milestone->id)>{{ $milestone->headline }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    @if(! empty($currentArticle->milestoneHeadline))
                                        <a href="{{ BASE_URL }}/tickets/roadmap#/tickets/editMilestone/{{ $currentArticle->milestoneId }}">
                                            {{ $currentArticle->milestoneHeadline }}
                                        </a>
                                    @else
                                        <span>{!! __('label.not_assigned_to_milestone') !!}</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Author -->
                        <div class="form-group">
                            <label class="control-label">{!! __('label.author') !!}</label>
                            <div class="">
                                <div class="wiki-author">
                                    <span class="wiki-author-avatar">{{ $authorInitials }}</span>
                                    {{ $currentArticle->firstname }} {{ $currentArticle->lastname }}
                                </div>
                            </div>
                        </div>

                        <!-- Last Saved -->
                        <div class="form-group">
                            <label class="control-label">{!! __('label.last_updated') !!}</label>
                            <div class="" id="wikiLastSaved" data-timestamp="{{ $currentArticle->modified }}">
                                {{ format($currentArticle->modified)->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    <!-- Activity Section -->
                    <div class="wiki-properties-section wiki-activity-section">
                        <h4 class="widgettitle title-light"><i class="fa fa-clock-rotate-left"></i> Activity</h4>

                        <div id="wikiActivityContainer"
                             hx-get="{{ BASE_URL }}/hx/wiki/articleActivity?articleId={{ $currentArticle->id }}"
                             hx-trigger="load, refreshActivity from:body"
                             hx-swap="innerHTML">
                            <div class="wiki-activity-loading">
                                <i class="fa fa-circle-notch fa-spin"></i> Loading activity...
                            </div>
                        </div>
                    </div>

                    <!-- Delete (pinned to bottom) -->
                    @if($login::userIsAtLeast($roles::$editor))
                    <div class="wiki-properties-footer">
                        <a href="#/wiki/delArticle/{{ $currentArticle->id }}" class="wiki-action-btn delete">
                            <i class="fa fa-trash"></i> Delete Article
                        </a>
                    </div>
                    @endif

                        </div><!-- /.wiki-properties-panel -->
                    </div><!-- /.wiki-content-layout -->
                </main>

            </div>

        @else
            <!-- Wiki exists but no articles yet -->
            <div class="wiki-empty-state">
                <div class="wiki-empty-state-icon svgContainer" style="width: 200px; margin: 0 auto;">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_book_reading_re_fu2c.svg') !!}
                </div>
                <h3 class="wiki-empty-state-title">{!! __('headlines.no_articles_yet') !!}</h3>
                <p class="wiki-empty-state-text">{!! __('text.create_new_content') !!}</p>
                <button class="btn btn-primary"
                        hx-post="{{ BASE_URL }}/hx/wiki/articleContent/create"
                        hx-swap="none">
                    <i class="fa fa-plus"></i> {!! __('link.create_article') !!}
                </button>
            </div>
        @endif

    @endif

</div>

@once @push('scripts')
<script type="text/javascript">
jQuery(document).ready(function() {

    jQuery('#toggleContents').on('click', function() {
        var panel = jQuery('#contentsPanel');
        var showBtn = jQuery('#showContentsBtn');
        panel.addClass('collapsed');
        showBtn.addClass('visible');
        localStorage.setItem('wikiContentsCollapsed', 'true');
    });

    jQuery('#showContentsBtn').on('click', function() {
        var panel = jQuery('#contentsPanel');
        var showBtn = jQuery('#showContentsBtn');
        panel.removeClass('collapsed');
        showBtn.removeClass('visible');
        localStorage.setItem('wikiContentsCollapsed', 'false');
    });

    jQuery('#collapseProperties').on('click', function() {
        var panel = jQuery('#propertiesPanel');
        var showBtn = jQuery('#showPropertiesBtn');
        panel.addClass('collapsed');
        showBtn.addClass('visible');
        localStorage.setItem('wikiPropertiesCollapsed', 'true');
    });

    jQuery('#showPropertiesBtn').on('click', function() {
        var panel = jQuery('#propertiesPanel');
        var showBtn = jQuery('#showPropertiesBtn');
        panel.removeClass('collapsed');
        showBtn.removeClass('visible');
        localStorage.setItem('wikiPropertiesCollapsed', 'false');
    });

    var isSmallScreen = window.innerWidth <= 1280;

    if (isSmallScreen || localStorage.getItem('wikiContentsCollapsed') === 'true') {
        jQuery('#contentsPanel').addClass('collapsed');
        jQuery('#showContentsBtn').addClass('visible');
    }

    if (isSmallScreen || localStorage.getItem('wikiPropertiesCollapsed') === 'true') {
        jQuery('#propertiesPanel').addClass('collapsed');
        jQuery('#showPropertiesBtn').addClass('visible');
    }

    var resizeTimeout;
    jQuery(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            var nowSmall = window.innerWidth <= 1280;
            if (nowSmall) {
                jQuery('#contentsPanel').addClass('collapsed');
                jQuery('#showContentsBtn').addClass('visible');
                jQuery('#propertiesPanel').addClass('collapsed');
                jQuery('#showPropertiesBtn').addClass('visible');
            }
        }, 150);
    });

    @if($currentArticle && $login::userIsAtLeast($roles::$editor))
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

        wrapper.addEventListener('mousedown', function(e) {
            if (isEditing && !editorEl.contains(e.target)) {
                toolbarClicking = true;
            }
        });

        var tiptapInstance = leantime.tiptapController.initComplex(textarea, {
            placeholder: 'Click anywhere to start editing...',
            toolbar: false,
            autosave: false,
            onCreate: function(params) {
                params.editor.setEditable(false);
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
                if (toolbarClicking) {
                    toolbarClicking = false;
                    return;
                }

                setTimeout(function() {
                    if (!isEditing) return;

                    var active = document.activeElement;

                    if (wrapper.contains(active)) return;

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

        function showToolbar() {
            if (window.leantime.tiptapToolbar) {
                var toolbar = window.leantime.tiptapToolbar.create(editor, 'complex');
                var tiptapEditorEl = wrapper.querySelector('.tiptap-editor');
                window.leantime.tiptapToolbar.attach({ element: tiptapEditorEl || editorEl }, toolbar);
            }
        }

        function hideToolbar() {
            var toolbarEl = wrapper.querySelector('.tiptap-toolbar');
            if (toolbarEl) {
                toolbarEl.remove();
            }
        }

        function enterEditMode() {
            if (isEditing) return;
            isEditing = true;
            editor.setEditable(true);
            wrapper.classList.add('editing');
            showToolbar();
            showIndicator('editing');
            editor.commands.focus('end');
        }

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
        }

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
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    lastSavedContent = content;
                    showIndicator('saved');
                    updateLastSaved();
                    setTimeout(function() {
                        if (!isEditing) hideIndicator();
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

        function showIndicator(state) {
            if (!indicator) return;
            indicator.style.display = 'flex';
            indicator.className = 'wiki-edit-indicator ' + state;
            var icon = indicator.querySelector('i');
            var text = indicator.querySelector('span');
            switch (state) {
                case 'editing': icon.className = 'fa fa-edit'; text.textContent = 'Editing'; break;
                case 'saving': icon.className = 'fa fa-circle-notch fa-spin'; text.textContent = 'Saving...'; break;
                case 'saved': icon.className = 'fa fa-check'; text.textContent = 'Saved'; break;
                case 'error': icon.className = 'fa fa-exclamation-triangle'; text.textContent = 'Save failed'; break;
            }
        }

        function hideIndicator() {
            if (indicator) indicator.style.display = 'none';
        }

        wrapper.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) return;
            enterEditMode();
        });

        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'e') {
                e.preventDefault();
                if (isEditing) exitEditMode(); else enterEditMode();
            }
            if (e.key === 'Escape' && isEditing) {
                e.preventDefault();
                exitEditMode();
            }
            if ((e.metaKey || e.ctrlKey) && e.key === 's' && isEditing) {
                e.preventDefault();
                saveContent(editor.getHTML());
            }
        });

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
                    if (treeLink) treeLink.textContent = newTitle;
                    updateLastSaved();
                });
            }
        });

        titleEditable.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); titleEditable.blur(); }
            if (e.key === 'Escape') { titleEditable.value = originalTitle; titleEditable.blur(); }
        });
    }

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
                if (treeLink) treeLink.className = newIcon;
                updateLastSaved();
            });
        });
    }

    var tagsInput = document.getElementById('wikiTagsInput');
    if (tagsInput && jQuery.fn.tagsInput) {
        jQuery('#wikiTagsInput').tagsInput({
            width: '100%',
            height: 'auto',
            defaultText: 'Add tag...',
            placeholderColor: 'var(--secondary-font-color)',
            onChange: function(elem, elem_tags) {
                saveField('tags', elem_tags, function() { updateLastSaved(); });
            }
        });
    }

    var statusSelect = document.getElementById('wikiStatusSelect');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            var newStatus = statusSelect.value;
            saveField('status', newStatus, function() {
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
        });
    }

    var milestoneSelect = document.getElementById('wikiMilestoneSelect');
    if (milestoneSelect) {
        milestoneSelect.addEventListener('change', function() {
            saveField('milestoneId', milestoneSelect.value, function() { window.location.reload(); });
        });
    }

    var parentSelect = document.getElementById('wikiParentSelect');
    if (parentSelect) {
        parentSelect.addEventListener('change', function() {
            saveField('parent', parentSelect.value, function() { window.location.reload(); });
        });
    }

    function updateLastSaved() {
        var lastSavedEl = document.getElementById('wikiLastSaved');
        if (lastSavedEl) {
            lastSavedEl.textContent = 'Just now';
            lastSavedEl.dataset.timestamp = new Date().toISOString();
        }
        htmx.trigger(document.body, 'refreshActivity');
    }

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
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                if (onSuccess) onSuccess(data);
            } else {
                console.error('[Wiki] Failed to save ' + field);
            }
        })
        .catch(function(err) {
            console.error('[Wiki] Save failed:', err);
        });
    }
    @endif

    @if($login::userHasRole([$roles::$commenter]))
    leantime.commentsController.enableCommenterForms();
    @endif

});
</script>
@endpush @endonce

@endsection
