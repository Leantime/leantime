// jQuery is loaded as a classic <script> in header.blade.php (before Vite modules)
// so that inline scripts can access it. Just grab the global reference here.
const jQuery = window.jQuery;
window.$ = jQuery;

// jQuery plugins — depend on global jQuery
import '../libs/bootstrap-fileupload.min.js';
import '../app/core/jqueryUiSortableBridge.js';
import '../app/core/nestedSortable.js';
