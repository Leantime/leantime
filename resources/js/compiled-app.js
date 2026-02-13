// Core app files
import '../../public/assets/js/app/app.js';
import '../../public/assets/js/app/core/editors.js';
import '../../public/assets/js/app/core/snippets.js';
import '../../public/assets/js/app/core/modals.js';
import '../../public/assets/js/app/core/tableHandling.js';
import '../../public/assets/js/app/core/datePickers.js';
import '../../public/assets/js/app/core/dateHelper.js';
import '../../public/assets/js/app/core/accessibility.js';

// Domain JS files — auto-imported via glob
const domainModules = import.meta.glob('../../app/Domain/**/*.js', { eager: true });
