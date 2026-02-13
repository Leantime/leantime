// jQuery — must be global (Bootstrap removed; dropdownBridge.js handles dropdown toggling)
import jQuery from 'jquery';
window.jQuery = jQuery;
window.$ = jQuery;

// jQuery plugins — depend on global jQuery
import '../../public/assets/js/libs/bootstrap-fileupload.min.js';
import '../../public/assets/js/app/core/nestedSortable.js';
