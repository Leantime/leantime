/**
 * Tiptap Embed Extension for Leantime
 *
 * Provides embedding functionality for various services:
 * - YouTube, Vimeo (video)
 * - Google Docs, Sheets, Slides
 * - Microsoft Office (OneDrive)
 * - Figma
 * - Loom
 * - Miro
 * - Airtable
 * - Typeform
 * - Calendly
 */

const { Node, mergeAttributes } = require('@tiptap/core');

/**
 * URL patterns for various services
 */
var patterns = {
    // Video
    youtube: /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})(?:\S*)?$/,
    vimeo: /^(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(\d+)(?:\S*)?$/,
    loom: /^(?:https?:\/\/)?(?:www\.)?loom\.com\/share\/([a-zA-Z0-9]+)(?:\S*)?$/,

    // Google
    googleDocs: /^(?:https?:\/\/)?docs\.google\.com\/document\/d\/([a-zA-Z0-9_-]+)(?:\/\S*)?$/,
    googleSheets: /^(?:https?:\/\/)?docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9_-]+)(?:\/\S*)?$/,
    googleSlides: /^(?:https?:\/\/)?docs\.google\.com\/presentation\/d\/([a-zA-Z0-9_-]+)(?:\/\S*)?$/,
    googleForms: /^(?:https?:\/\/)?docs\.google\.com\/forms\/d\/(?:e\/)?([a-zA-Z0-9_-]+)(?:\/\S*)?$/,

    // Microsoft
    oneDrive: /^(?:https?:\/\/)?(?:1drv\.ms|onedrive\.live\.com|.*\.sharepoint\.com)\/\S+$/,
    office365: /^(?:https?:\/\/)?(?:.*\.sharepoint\.com|.*\.officeapps\.live\.com)\/\S+$/,

    // Design & Collaboration
    figma: /^(?:https?:\/\/)?(?:www\.)?figma\.com\/(file|proto|design)\/([a-zA-Z0-9]+)(?:\/\S*)?$/,
    miro: /^(?:https?:\/\/)?(?:www\.)?miro\.com\/app\/board\/([a-zA-Z0-9_=-]+)(?:\/\S*)?$/,

    // Other
    airtable: /^(?:https?:\/\/)?airtable\.com\/(?:embed\/|shr)?([a-zA-Z0-9]+)(?:\/\S*)?$/,
    typeform: /^(?:https?:\/\/)?(?:www\.)?(?:[a-zA-Z0-9-]+\.)?typeform\.com\/to\/([a-zA-Z0-9]+)(?:\S*)?$/,
    calendly: /^(?:https?:\/\/)?calendly\.com\/([a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)?)(?:\S*)?$/,

    // Code & Dev
    codepen: /^(?:https?:\/\/)?codepen\.io\/([a-zA-Z0-9_-]+)\/(?:pen|embed)\/([a-zA-Z0-9]+)(?:\S*)?$/,
    codesandbox: /^(?:https?:\/\/)?codesandbox\.io\/(?:s|embed)\/([a-zA-Z0-9_-]+)(?:\S*)?$/,
};

/**
 * Detect embed type from URL
 */
function detectEmbedType(url) {
    if (!url) return null;

    for (var type in patterns) {
        if (patterns[type].test(url)) {
            return type;
        }
    }
    return null;
}

/**
 * Extract ID from URL based on type
 */
function extractId(url, type) {
    if (!url || !type || !patterns[type]) return null;
    var match = url.match(patterns[type]);
    return match ? match[1] : null;
}

/**
 * Generate embed URL for each service
 * Uses editable/interactive versions where possible
 */
function getEmbedUrl(url, type, id) {
    switch (type) {
        case 'youtube':
            return 'https://www.youtube.com/embed/' + id;

        case 'vimeo':
            return 'https://player.vimeo.com/video/' + id;

        case 'loom':
            return 'https://www.loom.com/embed/' + id;

        case 'googleDocs':
            // Use edit mode - allows editing if user has permission
            return 'https://docs.google.com/document/d/' + id + '/edit?embedded=true';

        case 'googleSheets':
            // Use edit mode - allows editing if user has permission
            return 'https://docs.google.com/spreadsheets/d/' + id + '/edit?embedded=true&rm=minimal';

        case 'googleSlides':
            // Use edit mode for slides
            return 'https://docs.google.com/presentation/d/' + id + '/edit?embedded=true&rm=minimal';

        case 'googleForms':
            return 'https://docs.google.com/forms/d/e/' + id + '/viewform?embedded=true';

        case 'figma':
            // Figma requires the full URL for embedding
            return 'https://www.figma.com/embed?embed_host=leantime&url=' + encodeURIComponent(url);

        case 'miro':
            // Miro live embed - allows interaction if board permissions allow
            return 'https://miro.com/app/live-embed/' + id + '/?moveToViewport=-1000,-1000,2000,2000&embedAutoplay=false';

        case 'airtable':
            return 'https://airtable.com/embed/' + id + '?backgroundColor=transparent';

        case 'typeform':
            return 'https://form.typeform.com/to/' + id;

        case 'calendly':
            return 'https://calendly.com/' + id + '?embed_type=Inline';

        case 'codepen':
            var match = url.match(patterns.codepen);
            if (match) {
                // Editable codepen embed
                return 'https://codepen.io/' + match[1] + '/embed/' + match[2] + '?default-tab=result&editable=true';
            }
            return null;

        case 'codesandbox':
            return 'https://codesandbox.io/embed/' + id + '?fontsize=14&theme=light&codemirror=1';

        case 'oneDrive':
        case 'office365':
            // For Office docs, use action=edit for editable embeds
            if (url.includes('sharepoint.com')) {
                return url.replace(/\?.*$/, '') + '?action=edit&embedded=true';
            }
            // For OneDrive personal links
            if (url.includes('1drv.ms') || url.includes('onedrive.live.com')) {
                return url.replace(/\?.*$/, '') + '?action=edit&embedded=true';
            }
            return url;

        default:
            // Unknown embed type - return null to reject
            return null;
    }
}

/**
 * Get display name for embed type
 */
function getTypeName(type) {
    var names = {
        youtube: 'YouTube',
        vimeo: 'Vimeo',
        loom: 'Loom',
        googleDocs: 'Google Docs',
        googleSheets: 'Google Sheets',
        googleSlides: 'Google Slides',
        googleForms: 'Google Forms',
        figma: 'Figma',
        miro: 'Miro',
        airtable: 'Airtable',
        typeform: 'Typeform',
        calendly: 'Calendly',
        codepen: 'CodePen',
        codesandbox: 'CodeSandbox',
        oneDrive: 'OneDrive',
        office365: 'Office 365',
    };
    return names[type] || type;
}

/**
 * Get icon class for embed type
 */
function getTypeIcon(type) {
    var icons = {
        youtube: 'fa-youtube',
        vimeo: 'fa-vimeo',
        loom: 'fa-video',
        googleDocs: 'fa-file-alt',
        googleSheets: 'fa-table',
        googleSlides: 'fa-desktop',
        googleForms: 'fa-list-alt',
        figma: 'fa-pen-nib',
        miro: 'fa-object-group',
        airtable: 'fa-database',
        typeform: 'fa-wpforms',
        calendly: 'fa-calendar',
        codepen: 'fa-codepen',
        codesandbox: 'fa-cube',
        oneDrive: 'fa-cloud',
        office365: 'fa-microsoft',
    };
    return icons[type] || 'fa-link';
}

/**
 * Get aspect ratio class for embed type
 */
function getAspectRatio(type) {
    switch (type) {
        case 'youtube':
        case 'vimeo':
        case 'loom':
            return 'video'; // 16:9
        case 'googleSlides':
            return 'presentation'; // 16:9
        case 'figma':
        case 'miro':
            return 'design'; // 4:3 or flexible
        case 'googleDocs':
        case 'googleSheets':
        case 'airtable':
            return 'document'; // taller
        case 'typeform':
        case 'googleForms':
        case 'calendly':
            return 'form'; // flexible height
        default:
            return 'default';
    }
}

/**
 * Create the Embed node extension
 */
var EmbedNode = Node.create({
    name: 'embed',

    group: 'block',

    atom: true,

    addAttributes: function() {
        return {
            src: { default: null },
            type: { default: 'youtube' },
            embedId: { default: null },
            originalUrl: { default: null },
            title: { default: null },
        };
    },

    parseHTML: function() {
        return [
            {
                tag: 'div[data-embed]',
                getAttrs: function(dom) {
                    return {
                        src: dom.getAttribute('data-src'),
                        type: dom.getAttribute('data-type'),
                        embedId: dom.getAttribute('data-embed-id'),
                        originalUrl: dom.getAttribute('data-original-url'),
                        title: dom.getAttribute('data-title'),
                    };
                },
            },
            // Legacy support for direct iframes
            {
                tag: 'iframe[src*="youtube.com"]',
                getAttrs: function(dom) {
                    var src = dom.getAttribute('src');
                    var videoId = src.match(/embed\/([a-zA-Z0-9_-]{11})/);
                    return {
                        src: src,
                        type: 'youtube',
                        embedId: videoId ? videoId[1] : null,
                        title: dom.getAttribute('title'),
                    };
                },
            },
            {
                tag: 'iframe[src*="vimeo.com"]',
                getAttrs: function(dom) {
                    var src = dom.getAttribute('src');
                    var videoId = src.match(/video\/(\d+)/);
                    return {
                        src: src,
                        type: 'vimeo',
                        embedId: videoId ? videoId[1] : null,
                        title: dom.getAttribute('title'),
                    };
                },
            },
            {
                tag: 'iframe[src*="docs.google.com"]',
                getAttrs: function(dom) {
                    var src = dom.getAttribute('src');
                    var type = 'googleDocs';
                    if (src.includes('spreadsheets')) type = 'googleSheets';
                    if (src.includes('presentation')) type = 'googleSlides';
                    if (src.includes('forms')) type = 'googleForms';
                    return {
                        src: src,
                        type: type,
                        title: dom.getAttribute('title'),
                    };
                },
            },
            {
                tag: 'iframe[src*="figma.com"]',
                getAttrs: function(dom) {
                    return {
                        src: dom.getAttribute('src'),
                        type: 'figma',
                        title: dom.getAttribute('title'),
                    };
                },
            },
        ];
    },

    renderHTML: function(props) {
        var attrs = props.HTMLAttributes;
        var type = attrs.type || 'youtube';
        var embedSrc = attrs.src;
        var aspectRatio = getAspectRatio(type);

        // Trusted embeds are services that require same-origin cookie access or
        // postMessage with origin validation to authenticate and render correctly.
        // Without allow-same-origin their internal scripts receive a null origin,
        // auth cookies are inaccessible, and the embed fails with a 400 error.
        //
        // NOTE: allow-scripts + allow-same-origin together allow sandboxed content
        // to remove its own sandbox via script — this is acceptable for these
        // known first-party services but should NOT be added for arbitrary URLs.
        var trustedEmbeds = {
            googleDocs: true,
            googleSheets: true,
            googleSlides: true,
            googleForms: true,
            figma: true,
            miro: true,
            oneDrive: true,
            office365: true,
        };

        var sandboxValue = trustedEmbeds[type]
            ? 'allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-storage-access-by-user-activation allow-downloads allow-modals'
            : 'allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox allow-downloads allow-modals';

        return ['div', mergeAttributes({
            class: 'tiptap-embed tiptap-embed--' + type + ' tiptap-embed--' + aspectRatio,
            'data-embed': '',
            'data-type': type,
            'data-embed-id': attrs.embedId || '',
            'data-src': embedSrc,
            'data-original-url': attrs.originalUrl || '',
            'data-title': attrs.title || '',
        }), [
            'div', { class: 'tiptap-embed__wrapper' }, [
                'iframe', {
                    src: embedSrc,
                    frameborder: '0',
                    allowfullscreen: 'true',
                    // Allow all permissions needed for editable embeds
                    allow: 'accelerometer; autoplay; clipboard-read; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen; camera; microphone',
                    sandbox: sandboxValue,
                    title: attrs.title || getTypeName(type) + ' embed',
                    loading: 'lazy',
                }
            ]
        ]];
    },

    addCommands: function() {
        var self = this;
        return {
            setEmbed: function(options) {
                return function(props) {
                    var commands = props.commands;
                    var url = options.url || options.src;
                    var type = options.type || detectEmbedType(url);

                    if (!type) {
                        console.warn('[Embed] Unsupported URL:', url);
                        return false;
                    }

                    var embedId = extractId(url, type);
                    var embedSrc = getEmbedUrl(url, type, embedId);

                    if (!embedSrc) {
                        console.warn('[Embed] Could not generate embed URL for:', url);
                        return false;
                    }

                    return commands.insertContent({
                        type: self.name,
                        attrs: {
                            src: embedSrc,
                            type: type,
                            embedId: embedId,
                            originalUrl: url,
                            title: options.title || getTypeName(type),
                        },
                    });
                };
            },

            // Legacy commands for backwards compatibility
            setYouTubeVideo: function(options) {
                return function(props) {
                    var commands = props.commands;
                    var videoId = options.videoId || extractId(options.src, 'youtube');
                    if (!videoId) return false;

                    return commands.insertContent({
                        type: self.name,
                        attrs: {
                            type: 'youtube',
                            embedId: videoId,
                            src: 'https://www.youtube.com/embed/' + videoId,
                            originalUrl: options.src,
                            title: options.title || 'YouTube video',
                        },
                    });
                };
            },
            setVimeoVideo: function(options) {
                return function(props) {
                    var commands = props.commands;
                    var videoId = options.videoId || extractId(options.src, 'vimeo');
                    if (!videoId) return false;

                    return commands.insertContent({
                        type: self.name,
                        attrs: {
                            type: 'vimeo',
                            embedId: videoId,
                            src: 'https://player.vimeo.com/video/' + videoId,
                            originalUrl: options.src,
                            title: options.title || 'Vimeo video',
                        },
                    });
                };
            },
        };
    },
});

/**
 * Show embed dialog
 */
function showEmbedDialog(editor) {
    // Close existing dialog
    var existing = document.querySelector('.tiptap-embed-dialog');
    if (existing) {
        existing.remove();
    }

    var supportedServices = [
        { name: 'YouTube', icon: 'fa-youtube', example: 'youtube.com/watch?v=...' },
        { name: 'Vimeo', icon: 'fa-vimeo', example: 'vimeo.com/...' },
        { name: 'Loom', icon: 'fa-video', example: 'loom.com/share/...' },
        { name: 'Google Docs', icon: 'fa-file-alt', example: 'docs.google.com/document/...' },
        { name: 'Google Sheets', icon: 'fa-table', example: 'docs.google.com/spreadsheets/...' },
        { name: 'Google Slides', icon: 'fa-desktop', example: 'docs.google.com/presentation/...' },
        { name: 'Figma', icon: 'fa-pen-nib', example: 'figma.com/file/...' },
        { name: 'Miro', icon: 'fa-object-group', example: 'miro.com/app/board/...' },
        { name: 'Airtable', icon: 'fa-database', example: 'airtable.com/...' },
        { name: 'Calendly', icon: 'fa-calendar', example: 'calendly.com/...' },
    ];

    var servicesHtml = supportedServices.map(function(s) {
        return '<div class="tiptap-embed-dialog__service">' +
            '<i class="fa ' + s.icon + '"></i>' +
            '<span>' + s.name + '</span>' +
        '</div>';
    }).join('');

    var dialog = document.createElement('div');
    dialog.className = 'tiptap-embed-dialog';
    dialog.innerHTML =
        '<div class="tiptap-embed-dialog__overlay"></div>' +
        '<div class="tiptap-embed-dialog__content">' +
            '<div class="tiptap-embed-dialog__header">' +
                '<h3>Embed Content</h3>' +
                '<button type="button" class="tiptap-embed-dialog__close" aria-label="Close">&times;</button>' +
            '</div>' +
            '<div class="tiptap-embed-dialog__body">' +
                '<div class="tiptap-embed-dialog__field">' +
                    '<label>Paste URL</label>' +
                    '<input type="text" class="tiptap-embed-dialog__input" placeholder="Paste a link to embed..." />' +
                    '<div class="tiptap-embed-dialog__preview" style="display:none;"></div>' +
                '</div>' +
                '<div class="tiptap-embed-dialog__services">' +
                    '<div class="tiptap-embed-dialog__services-label">Supported services:</div>' +
                    '<div class="tiptap-embed-dialog__services-grid">' + servicesHtml + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="tiptap-embed-dialog__footer">' +
                '<button type="button" class="tiptap-embed-dialog__btn tiptap-embed-dialog__btn--cancel">Cancel</button>' +
                '<button type="button" class="tiptap-embed-dialog__btn tiptap-embed-dialog__btn--primary" disabled>Embed</button>' +
            '</div>' +
        '</div>';

    document.body.appendChild(dialog);

    var input = dialog.querySelector('.tiptap-embed-dialog__input');
    var preview = dialog.querySelector('.tiptap-embed-dialog__preview');
    var embedBtn = dialog.querySelector('.tiptap-embed-dialog__btn--primary');
    var detectedType = null;

    setTimeout(function() { input.focus(); }, 100);

    function closeDialog() {
        dialog.remove();
    }

    function updatePreview() {
        var url = input.value.trim();
        detectedType = detectEmbedType(url);

        if (detectedType) {
            var typeName = getTypeName(detectedType);
            var icon = getTypeIcon(detectedType);
            preview.innerHTML = '<i class="fa ' + icon + '"></i> ' + typeName + ' detected';
            preview.style.display = 'block';
            preview.className = 'tiptap-embed-dialog__preview tiptap-embed-dialog__preview--success';
            embedBtn.disabled = false;
        } else if (url.length > 0) {
            preview.innerHTML = '<i class="fa fa-exclamation-circle"></i> URL not recognized';
            preview.style.display = 'block';
            preview.className = 'tiptap-embed-dialog__preview tiptap-embed-dialog__preview--error';
            embedBtn.disabled = true;
        } else {
            preview.style.display = 'none';
            embedBtn.disabled = true;
        }
    }

    input.addEventListener('input', updatePreview);
    input.addEventListener('paste', function() {
        setTimeout(updatePreview, 50);
    });

    dialog.querySelector('.tiptap-embed-dialog__overlay').addEventListener('click', closeDialog);
    dialog.querySelector('.tiptap-embed-dialog__close').addEventListener('click', closeDialog);
    dialog.querySelector('.tiptap-embed-dialog__btn--cancel').addEventListener('click', closeDialog);

    embedBtn.addEventListener('click', function() {
        var url = input.value.trim();
        if (!url || !detectedType) return;

        editor.chain().focus().setEmbed({ url: url, type: detectedType }).run();
        closeDialog();
    });

    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !embedBtn.disabled) {
            embedBtn.click();
        }
    });

    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            closeDialog();
            document.removeEventListener('keydown', escHandler);
        }
    });
}

// Make available globally for slash commands
window.leantime = window.leantime || {};
window.leantime.tiptapEmbed = {
    showDialog: showEmbedDialog,
    detectType: detectEmbedType,
    getEmbedUrl: getEmbedUrl,
    getTypeName: getTypeName,
    patterns: patterns,
};

// Export
module.exports = {
    EmbedNode: EmbedNode,
    showEmbedDialog: showEmbedDialog,
    detectEmbedType: detectEmbedType,
    getEmbedUrl: getEmbedUrl,
    patterns: patterns,
};
