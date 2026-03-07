// jQuery is loaded as a classic <script> in header.blade.php (before Vite modules)
// so that inline scripts can access it. Just grab the global reference here.
const jQuery = window.jQuery;
window.$ = jQuery;

// jQuery plugins — depend on global jQuery
import '../../public/assets/js/libs/bootstrap-fileupload.min.js';

// Pre-declare implicit globals used by tagsInput (no `var` in source —
// fails in ES module strict mode without these).
window.autocomplete_options = window.autocomplete_options || {};
window.attrname = window.attrname || '';
window.i = window.i || 0;
window.str = window.str || '';

import '../../public/assets/js/libs/jquery.tagsinput.min.js';
import '../../public/assets/js/app/core/jqueryUiSortableBridge.js';
import '../../public/assets/js/app/core/nestedSortable.js';
