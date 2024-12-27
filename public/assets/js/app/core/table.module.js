import DataTable from 'datatables.net';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-colreorder';
import 'datatables.net-responsive';

class ColumnResizer {
    constructor(table) {
        this.table = table;
        this.currentHeader = null;
        this.startX = null;
        this.startWidth = null;

        // Store the original mouseDown function
        this.originalValidateMove = DataTable.ColReorder.prototype._mouseDown;

        // Bind methods to maintain correct 'this' context
        this.handleMouseDown = this.handleMouseDown.bind(this);
        this.handleMouseMove = this.handleMouseMove.bind(this);
        this.handleMouseUp = this.handleMouseUp.bind(this);

        // Override ColReorder's mouseDown
        this.overrideColReorder();
    }

    overrideColReorder() {
        DataTable.ColReorder.prototype._mouseDown = (e, cell) => {
            const target = e.target;
            if (
                target.classList.contains('resize-handle')
                || target.closest('.resizing')
                || target.closest('th')?.classList.contains('resizing')
            ) {
                return false;
            }
            return this.originalValidateMove.call(this, e, cell);
        };
    }

    init(selector) {
        const handlers = selector.querySelectorAll('.resize-handle');
        if (!handlers.length) return;

        handlers.forEach(handler => {
            handler.addEventListener('mousedown', this.handleMouseDown);
        });
    }

    handleMouseDown(e) {
        e.preventDefault();
        e.stopPropagation();

        this.currentHeader = e.target.closest('th');
        this.startX = e.pageX;
        this.startWidth = this.currentHeader.offsetWidth;

        this.currentHeader.classList.add('resizing');

        window.addEventListener('mousemove', this.handleMouseMove);
        window.addEventListener('mouseup', this.handleMouseUp);
    }

    handleMouseMove(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!this.currentHeader?.classList.contains('resizing')) return;

        requestAnimationFrame(() => {
            this.updateColumnWidth(e);
        });
    }

    handleMouseUp(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!this.currentHeader) return;

        window.removeEventListener('mousemove', this.handleMouseMove);
        window.removeEventListener('mouseup', this.handleMouseUp);

        this.currentHeader.classList.remove('resizing');
        this.currentHeader = null;
        this.startX = null;
        this.startWidth = null;
    }

    updateColumnWidth(e) {
        // Calculate new width
        const diffX = e.pageX - this.startX;
        const newWidth = Math.max(50, this.startWidth + diffX);

        // Get column index
        const headerRow = this.currentHeader.closest('tr');
        const columnIndex = Array.from(headerRow.children).indexOf(this.currentHeader);

        // Update header width
        this.currentHeader.style.width = `${newWidth}px`;
        this.currentHeader.style.minWidth = `${newWidth}px`;

        const table = this.currentHeader.closest('table');
        if (!table) return;

        // Update colgroup if it exists
        const colgroup = table.querySelector('colgroup');
        if (colgroup?.children[columnIndex]) {
            colgroup.children[columnIndex].style.width = `${newWidth}px`;
        }

        // Update body cells
        const cells = table.querySelectorAll(`tbody tr td:nth-child(${columnIndex + 1})`);
        cells.forEach(cell => {
            cell.style.width = `${newWidth}px`;
            cell.style.minWidth = `${newWidth}px`;
        });

        // Adjust DataTable columns
        const dtApi = DataTable.Api(table);
        dtApi.columns.adjust();
    }

    // Clean up method for removing event listeners and restoring original behavior
    destroy() {
        // Restore original ColReorder behavior
        DataTable.ColReorder.prototype._mouseDown = this.originalValidateMove;
    }
}

const initDataTable = (element, settings = {}) => {
    const selector = document.querySelector(element);
    if (!selector) return;

    const table = new DataTable(element, settings);
    const resizer = new ColumnResizer(table);
    resizer.init(selector);

    return table;
};

export default {
    initDataTable: initDataTable,
};
