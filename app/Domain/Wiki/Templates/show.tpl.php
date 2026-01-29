<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$wikis = $tpl->get('wikis');
$wikiHeadlines = $tpl->get('wikiHeadlines');

$currentWiki = $tpl->get('currentWiki');
$currentArticle = $tpl->get('currentArticle');

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
            echo '<a href="'.BASE_URL.'/wiki/show/'.$headline->id.'" class="wiki-tree-link'.$isActive.'">';
            echo '<i class="'.$tplObject->escape($headline->data).'"></i>';
            echo '<span>'.$tplObject->escape($headline->title).'</span>';
            if ($isDraft) {
                echo ' <span class="wiki-tree-draft">('.$tplObject->__('label.draft').')</span>';
            }
            echo '</a>';

            // Recursively render children
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
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-book"></span></div>
    <div class="pagetitle">

        <h5><?php $tpl->e(session('currentProjectClient')); ?></h5>

        <?php if (count($wikis) > 0) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    <?php if ($login::userIsAtLeast($roles::$editor) && $currentWiki) { ?>
                        <li><a class="inlineEdit" href="#/wiki/wikiModal/<?= $currentWiki->id ?>"><?= $tpl->__('link.edit_wiki') ?></a></li>
                        <li><a class="delete" href="#/wiki/delWiki/<?php echo $currentWiki->id; ?>" ><i class="fa fa-trash"></i> <?= $tpl->__('links.delete_wiki') ?></a></li>
                    <?php } ?>
                </ul>
            </span>
        <?php } ?>

        <h1><?php echo $tpl->__('headlines.documents'); ?>
         <?php if (count($wikis) > 0) {?>
             //
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php
                    if ($currentWiki !== false) {
                        $tpl->e($currentWiki->title);
                    } else {
                        $tpl->__('label.select_board');
                    } ?>
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a class="inlineEdit" href="#/wiki/wikiModal/"><?= $tpl->__('link.new_wiki') ?></a></li>
                    <li class='nav-header border'></li>
                    <?php foreach ($wikis as $wiki) {?>
                        <li>
                            <a href="<?= BASE_URL.'/wiki/show?setWiki='.$wiki->id ?>"><?= $tpl->escape($wiki->title)?></a>
                        </li>
                    <?php } ?>
                </ul>
            </span>
         <?php } ?>
        </h1>
    </div>
</div>

<div class="maincontent">
    <?php echo $tpl->displayNotification(); ?>

    <?php if ((! $currentArticle || $currentArticle->id != null) && (! $wikis || count($wikis) == 0)) { ?>
        <!-- No wikis exist - show empty state -->
        <div class="wiki-empty-state">
            <div class="wiki-empty-state-icon svgContainer">
                <?= file_get_contents(ROOT.'/dist/images/svg/undraw_book_reading_re_fu2c.svg'); ?>
            </div>
            <h3 class="wiki-empty-state-title"><?= $tpl->__('headlines.no_articles_yet') ?></h3>
            <p class="wiki-empty-state-text"><?= $tpl->__('text.create_new_wiki') ?></p>
            <a href='#/wiki/wikiModal/' class='inlineEdit btn btn-primary'><?= $tpl->__('links.icon.create_new_board') ?></a>
        </div>

    <?php } elseif ($wikis && count($wikis) > 0) { ?>

        <?php if ($currentArticle && $currentArticle->id != null) { ?>
            <!-- Single Panel Layout: Contents | Document | Properties (all inside) -->
            <div class="wiki-layout">

                <!-- Main Content Area (contains everything) -->
                <main class="wiki-content">

                    <!-- Three-panel layout inside -->
                    <div class="wiki-content-layout">

                        <!-- Left: Contents Sidebar -->
                        <div class="wiki-contents-panel" id="contentsPanel">
                            <div class="wiki-panel-header">
                                <h5 class="wiki-panel-title">Contents</h5>
                                <button class="wiki-collapse-btn" id="toggleContents" title="Collapse">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                            </div>

                            <nav id="article-toc-wrapper">
                                <?php createModernTreeView($wikiHeadlines, 0, $currentArticle->id, 0, $tpl); ?>
                            </nav>

                            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                <a class="wiki-create-btn inlineEdit" href="#/wiki/articleDialog/">
                                    <i class="fa fa-plus"></i>
                                    <span><?= $tpl->__('link.create_article') ?></span>
                                </a>
                            <?php } ?>
                        </div>

                        <!-- Toggle for collapsed Contents -->
                        <button class="wiki-panel-toggle left" id="showContentsBtn" title="Show Contents">
                            <i class="fa fa-chevron-right"></i>
                        </button>

                        <div class="wiki-content-inner">

                        <!-- Toggle for collapsed Properties -->
                        <button class="wiki-panel-toggle right" id="showPropertiesBtn" title="Show Properties">
                            <i class="fa fa-chevron-left"></i>
                        </button>

                            <!-- Document Header -->
                            <header class="wiki-document-header">
                                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <!-- Editable Title with Icon Picker -->
                                    <div class="wiki-title-wrapper" id="wikiTitleWrapper">
                                        <div class="wiki-icon-picker">
                                            <button type="button"
                                                    class="wiki-icon-btn icp icp-dd dropdown-toggle iconpicker-container"
                                                    data-toggle="dropdown"
                                                    title="Change icon">
                                                <i class="<?= $tpl->escape($currentArticle->data ?: 'fa fa-file-alt') ?>"></i>
                                            </button>
                                            <input type="hidden" id="wikiArticleIcon" class="articleIcon" value="<?= $tpl->escape($currentArticle->data) ?>" />
                                        </div>
                                        <h1 class="wiki-title-editable"
                                            id="wikiTitleEditable"
                                            contenteditable="true"
                                            data-placeholder="Untitled"
                                            data-original="<?= $tpl->escape($currentArticle->title) ?>"><?= $tpl->escape($currentArticle->title) ?></h1>
                                    </div>

                                    <!-- Editable Tags -->
                                    <div class="wiki-tags-wrapper">
                                        <input type="text"
                                               id="wikiTagsInput"
                                               class="wiki-tags-input"
                                               data-role="tagsinput"
                                               value="<?= $tpl->escape($currentArticle->tags ?? '') ?>"
                                               placeholder="Add tags..." />
                                    </div>
                                <?php } else { ?>
                                    <h1 class="wiki-document-title">
                                        <i class="article-icon <?= $tpl->escape($currentArticle->data) ?>"></i>
                                        <?= $tpl->escape($currentArticle->title) ?>
                                    </h1>

                                    <?php
                                    $tagsArray = array_filter(explode(',', $currentArticle->tags ?? ''));
                                    if (count($tagsArray) > 0) { ?>
                                        <div class="wiki-document-tags">
                                            <?php foreach ($tagsArray as $tag) {
                                                $tag = trim($tag);
                                                if (! empty($tag)) { ?>
                                                    <span class="wiki-document-tag"><?= $tpl->escape($tag) ?></span>
                                                <?php }
                                            } ?>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </header>

                        <!-- Document Body - Click to Edit -->
                        <div class="wiki-document-wrapper" id="wikiDocumentWrapper" data-article-id="<?= $currentArticle->id ?>">
                            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                <!-- Hidden textarea for Tiptap -->
                                <textarea id="wikiArticleContent" class="wiki-editor-textarea" style="display:none;"><?= $tpl->escapeMinimal($currentArticle->description); ?></textarea>
                                <!-- Tiptap editor will be initialized here -->
                                <div id="wikiTiptapEditor" class="wiki-document"></div>
                                <!-- Edit mode indicator -->
                                <div class="wiki-edit-indicator" id="wikiEditIndicator" style="display: none;">
                                    <i class="fa fa-circle"></i>
                                    <span>Editing</span>
                                </div>
                            <?php } else { ?>
                                <!-- Read-only view for non-editors -->
                                <article class="wiki-document" id="wikiDocumentContent">
                                    <?= $tpl->escapeMinimal($currentArticle->description); ?>
                                </article>
                            <?php } ?>
                        </div>

                        <?php if (! empty($currentArticle->milestoneHeadline)) { ?>
                            <div class="milestonContainer border" style="margin-top: 32px;">
                                <div hx-trigger="load"
                                     hx-indicator=".htmx-indicator"
                                     hx-get="<?= BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?= $currentArticle->milestoneId ?>">
                                    <div class="htmx-indicator">
                                        <?= $tpl->__('label.loading_milestone') ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- Comments Section -->
                        <section class="wiki-comments-section" id="comments">
                            <h4 class="wiki-comments-title">
                                <i class="fa fa-comments"></i>
                                <?= $tpl->__('subtitles.discussion') ?>
                            </h4>

                            <form method="post" action="<?= BASE_URL ?>/wiki/show/<?= $currentArticle->id; ?>#comment">
                                <input type="hidden" name="comment" value="1" />
                                <?php
                                $tpl->assign('formUrl', BASE_URL.'/wiki/show/'.$currentArticle->id.'');
                                $tpl->displaySubmodule('comments-generalComment');
                                ?>
                            </form>
                        </section>

                        </div><!-- /.wiki-content-inner -->

                        <!-- Properties Panel (inside content area) -->
                        <div class="wiki-properties-panel" id="propertiesPanel">
                            <div class="wiki-panel-header">
                                <h5 class="wiki-panel-title">Properties</h5>
                                <button class="wiki-collapse-btn" id="collapseProperties" title="Collapse">
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>

                    <!-- Properties Section -->
                    <div class="wiki-properties-section">

                        <!-- Status Dropdown -->
                        <div class="wiki-property-row">
                            <span class="wiki-property-label">
                                <i class="fa fa-circle-dot"></i> Status
                            </span>
                            <span class="wiki-property-value">
                                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <div class="wiki-status-dropdown dropdown" id="wikiStatusDropdown">
                                        <button class="wiki-status-pill <?= $currentArticle->status ?>" data-toggle="dropdown">
                                            <?php if ($currentArticle->status === 'draft') { ?>
                                                <i class="fa fa-pencil"></i> Draft
                                            <?php } else { ?>
                                                <i class="fa fa-check"></i> Published
                                            <?php } ?>
                                            <i class="fa fa-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu wiki-status-menu">
                                            <li><a href="javascript:void(0)" class="wiki-status-option" data-value="draft"><i class="fa fa-pencil"></i> Draft</a></li>
                                            <li><a href="javascript:void(0)" class="wiki-status-option" data-value="published"><i class="fa fa-check"></i> Published</a></li>
                                        </ul>
                                    </div>
                                <?php } else { ?>
                                    <span class="wiki-status-badge <?= $currentArticle->status ?>">
                                        <?= ucfirst($currentArticle->status) ?>
                                    </span>
                                <?php } ?>
                            </span>
                        </div>

                        <?php
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
                        ?>
                        <!-- Parent -->
                        <div class="wiki-property-row">
                            <span class="wiki-property-label">
                                <i class="fa fa-folder-tree"></i> Parent
                            </span>
                            <span class="wiki-property-value">
                                <?php if ($currentArticle->parent && $currentArticle->parent > 0) { ?>
                                    <a href="<?= BASE_URL ?>/wiki/show/<?= $currentArticle->parent ?>" class="wiki-parent-link">
                                        <?= $parentName ?>
                                    </a>
                                <?php } else { ?>
                                    <span class="wiki-no-parent"><?= $parentName ?></span>
                                <?php } ?>
                            </span>
                        </div>

                        <!-- Author -->
                        <div class="wiki-property-row">
                            <span class="wiki-property-label">
                                <i class="fa fa-user"></i> Author
                            </span>
                            <span class="wiki-property-value">
                                <div class="wiki-author">
                                    <span class="wiki-author-avatar"><?= $authorInitials ?></span>
                                    <?= $tpl->escape($currentArticle->firstname) ?> <?= $tpl->escape($currentArticle->lastname) ?>
                                </div>
                            </span>
                        </div>

                        <!-- Milestone -->
                        <?php if (! empty($currentArticle->milestoneHeadline)) { ?>
                        <div class="wiki-property-row">
                            <span class="wiki-property-label">
                                <i class="fa fa-flag"></i> Milestone
                            </span>
                            <span class="wiki-property-value">
                                <a href="<?= BASE_URL ?>/tickets/roadmap#/tickets/editMilestone/<?= $currentArticle->milestoneId ?>" class="wiki-milestone-link">
                                    <?= $tpl->escape($currentArticle->milestoneHeadline) ?>
                                </a>
                            </span>
                        </div>
                        <?php } ?>

                        <!-- Last Saved -->
                        <div class="wiki-property-row">
                            <span class="wiki-property-label">
                                <i class="fa fa-clock"></i> Last Saved
                            </span>
                            <span class="wiki-property-value" id="wikiLastSaved" data-timestamp="<?= $currentArticle->modified ?>">
                                <?= format($currentArticle->modified)->diffForHumans() ?>
                            </span>
                        </div>
                    </div>

                    <!-- Actions Section -->
                    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                    <div class="wiki-properties-section wiki-actions-section">
                        <a href="#/wiki/articleDialog/<?= $currentArticle->id; ?>" class="wiki-action-btn inlineEdit">
                            <i class="fa fa-cog"></i> Article Settings
                        </a>
                        <a href="#/wiki/delArticle/<?= $currentArticle->id; ?>" class="wiki-action-btn delete">
                            <i class="fa fa-trash"></i> Delete Article
                        </a>
                    </div>
                    <?php } ?>

                    <!-- Activity Section -->
                    <div class="wiki-properties-section">
                        <h6 class="wiki-properties-section-title">Activity</h6>

                        <div class="wiki-activity-feed" id="wikiActivityFeed">
                            <!-- Activity loaded via HTMX or shown inline -->
                            <div class="wiki-activity-item">
                                <div class="wiki-activity-icon edit">
                                    <i class="fa fa-edit"></i>
                                </div>
                                <div class="wiki-activity-content">
                                    <div class="wiki-activity-text">
                                        <strong><?= $tpl->escape($currentArticle->firstname) ?></strong> modified this
                                    </div>
                                    <div class="wiki-activity-time"><?= format($currentArticle->modified)->date() ?></div>
                                </div>
                            </div>

                            <div class="wiki-activity-item">
                                <div class="wiki-activity-icon">
                                    <i class="fa fa-plus"></i>
                                </div>
                                <div class="wiki-activity-content">
                                    <div class="wiki-activity-text">
                                        <strong><?= $tpl->escape($currentArticle->firstname) ?></strong> created this
                                    </div>
                                    <div class="wiki-activity-time"><?= format($currentArticle->created)->date() ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                        </div><!-- /.wiki-properties-panel -->
                    </div><!-- /.wiki-content-layout -->
                </main>

            </div>

        <?php } else { ?>
            <!-- Wiki exists but no articles yet -->
            <div class="wiki-empty-state">
                <div class="wiki-empty-state-icon svgContainer" style="width: 200px; margin: 0 auto;">
                    <?= file_get_contents(ROOT.'/dist/images/svg/undraw_book_reading_re_fu2c.svg'); ?>
                </div>
                <h3 class="wiki-empty-state-title"><?= $tpl->__('headlines.no_articles_yet') ?></h3>
                <p class="wiki-empty-state-text"><?= $tpl->__('text.create_new_content') ?></p>
                <a href='#/wiki/articleDialog/' class='inlineEdit btn btn-primary'>
                    <i class='fa fa-plus'></i> <?= $tpl->__('link.create_article') ?>
                </a>
            </div>
        <?php } ?>

    <?php } ?>

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

    // Restore contents panel state
    if (localStorage.getItem('wikiContentsCollapsed') === 'true') {
        jQuery('#contentsPanel').addClass('collapsed');
        jQuery('#showContentsBtn').addClass('visible');
    }

    // Restore properties panel state
    if (localStorage.getItem('wikiPropertiesCollapsed') === 'true') {
        jQuery('#propertiesPanel').addClass('collapsed');
        jQuery('#showPropertiesBtn').addClass('visible');
    }

    // ==========================================
    // Click-to-Edit with Tiptap
    // ==========================================

    <?php if ($currentArticle && $login::userIsAtLeast($roles::$editor)) { ?>
    (function() {
        var articleId = <?= json_encode($currentArticle->id) ?>;
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

        // Initialize Tiptap in read mode
        var tiptapInstance = leantime.tiptapController.initComplex(textarea, {
            placeholder: 'Click anywhere to start editing...',
            toolbar: false, // Start without toolbar
            autosave: false, // We'll handle auto-save ourselves
            onCreate: function(params) {
                // Start in non-editable mode
                params.editor.setEditable(false);
                console.log('[Wiki] Tiptap initialized in read mode');
            },
            onUpdate: function(params) {
                // Debounced auto-save while editing
                if (isEditing) {
                    clearTimeout(saveTimeout);
                    showIndicator('saving');
                    saveTimeout = setTimeout(function() {
                        saveContent(params.editor.getHTML());
                    }, 1500);
                }
            },
            onBlur: function(params) {
                // Small delay to allow for toolbar clicks
                setTimeout(function() {
                    if (isEditing && !editorEl.contains(document.activeElement)) {
                        exitEditMode();
                    }
                }, 200);
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
                window.leantime.tiptapToolbar.attach({ element: editorEl }, toolbar);
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

            // Focus the editor
            editor.commands.focus('end');

            console.log('[Wiki] Entered edit mode');
        }

        // Exit edit mode
        function exitEditMode() {
            if (!isEditing) return;

            // Save any pending changes
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

        // Save content via HTMX/fetch
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
                    // Update Last Saved in properties
                    var lastSavedEl = document.getElementById('wikiLastSaved');
                    if (lastSavedEl) {
                        lastSavedEl.textContent = 'Just now';
                    }
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
            // Don't enter edit mode if clicking on a link
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
                    // Try to save synchronously
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
            var newTitle = titleEditable.textContent.trim();
            if (newTitle && newTitle !== originalTitle) {
                saveField('title', newTitle, function() {
                    originalTitle = newTitle;
                    titleEditable.dataset.original = newTitle;
                    // Update the sidebar tree if title changed
                    var treeLink = document.querySelector('.wiki-tree-link.active span');
                    if (treeLink) {
                        treeLink.textContent = newTitle;
                    }
                    // Update Last Saved
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
                titleEditable.textContent = originalTitle;
                titleEditable.blur();
            }
        });

        // Prevent pasting rich text
        titleEditable.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });
    }

    // ==========================================
    // Icon Picker
    // ==========================================

    var iconInput = document.getElementById('wikiArticleIcon');
    if (iconInput && jQuery.fn.iconpicker) {
        jQuery('.wiki-icon-btn').iconpicker({
            component: '.wiki-icon-btn',
            input: '#wikiArticleIcon',
            inputSearch: true,
            defaultValue: iconInput.value || 'fa fa-file-alt',
            icons: [
                {title: "fa fa-file-alt", searchTerms:['document', 'file']},
                {title: "fa fa-book", searchTerms:['book', 'docs']},
                {title: "fa fa-lightbulb", searchTerms:['idea', 'light']},
                {title: "fa fa-rocket", searchTerms:['launch', 'rocket']},
                {title: "fa fa-cog", searchTerms:['settings', 'gear']},
                {title: "fa fa-code", searchTerms:['code', 'dev']},
                {title: "fa fa-star", searchTerms:['star', 'favorite']},
                {title: "fa fa-heart", searchTerms:['heart', 'love']},
                {title: "fa fa-flag", searchTerms:['flag', 'milestone']},
                {title: "fa fa-check", searchTerms:['check', 'done']},
                {title: "fa fa-list", searchTerms:['list', 'checklist']},
                {title: "fa fa-users", searchTerms:['users', 'team']},
                {title: "fa fa-calendar", searchTerms:['calendar', 'date']},
                {title: "fa fa-clipboard", searchTerms:['clipboard', 'notes']},
                {title: "fa fa-chart-bar", searchTerms:['chart', 'analytics']},
                {title: "fa fa-folder", searchTerms:['folder', 'directory']},
                {title: "fa fa-globe", searchTerms:['globe', 'world']},
                {title: "fa fa-lock", searchTerms:['lock', 'security']},
                {title: "fa fa-bolt", searchTerms:['bolt', 'quick']},
                {title: "fa fa-puzzle-piece", searchTerms:['puzzle', 'integration']}
            ]
        });

        jQuery('.wiki-icon-btn').on('iconpickerSelected', function(event) {
            var newIcon = event.iconpickerValue;
            jQuery('#wikiArticleIcon').val(newIcon);
            jQuery('.wiki-icon-btn i').attr('class', newIcon);

            saveField('icon', newIcon, function() {
                // Update sidebar tree icon
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
    // Status Dropdown
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
                        // Update pill appearance
                        statusPill.classList.remove('draft', 'published');
                        statusPill.classList.add(newStatus);

                        // Update pill content
                        if (newStatus === 'draft') {
                            statusPill.innerHTML = '<i class="fa fa-pencil"></i> Draft <i class="fa fa-chevron-down"></i>';
                        } else {
                            statusPill.innerHTML = '<i class="fa fa-check"></i> Published <i class="fa fa-chevron-down"></i>';
                        }

                        updateLastSaved();
                    });
                }
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
    }

    // Generic field save function
    function saveField(field, value, onSuccess) {
        var articleId = <?= json_encode($currentArticle->id) ?>;

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

    <?php } ?>

    <?php if ($login::userHasRole([$roles::$commenter])) { ?>
    leantime.commentsController.enableCommenterForms();
    <?php } ?>

});
</script>
