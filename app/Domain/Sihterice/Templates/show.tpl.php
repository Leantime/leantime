<?php
defined('RESTRICTED') or exit('Restricted access');
?>

<style>
    /* Pillow Select Styles */
    .pillow-select {
        padding: 4px 12px;
        border-radius: 15px;
        border: 1px solid #ddd;
        background: white;
        font-size: 14px;
        line-height: 20px;
        cursor: pointer;
        outline: none;
        transition: all 0.2s;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2012%2012%22%3E%3Ctitle%3Edown-arrow%3C%2Ftitle%3E%3Cg%20fill%3D%22%23000000%22%3E%3Cpath%20d%3D%22M10.293%2C3.293%2C6%2C7.586%2C1.707%2C3.293A1%2C1%2C0%2C0%2C0%2C.293%2C4.707l5%2C5a1%2C1%2C0%2C0%2C0%2C1.414%2C0l5-5a1%2C1%2C0%2C1%2C0-1.414-1.414Z%22%20fill%3D%22%23000000%22%3E%3C%2Fpath%3E%3C%2Fg%3E%3C%2Fsvg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 28px;
        vertical-align: middle;
    }
    .pillow-select:hover {
        border-color: #aaa;
    }
    .pillow-select:focus {
        border-color: var(--primary-color, #1b75bb);
        box-shadow: 0 0 0 2px rgba(27, 117, 187, 0.1);
    }

    /* Delete Modal Styles */
    .delete-modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        width: 100%;
        max-width: 400px;
        animation: modalSlideIn 0.3s ease;
        text-align: center;
    }
    .delete-modal-icon {
        width: 60px;
        height: 60px;
        background: #fee2e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .delete-modal-icon i {
        font-size: 28px;
        color: #dc3545;
    }
    .delete-modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    .delete-modal-text {
        color: #666;
        margin-bottom: 25px;
        line-height: 1.5;
    }
    .delete-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: space-between;
    }

    /*Delete button*/
    .btn-danger {
        background: #c0392b;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        margin-right: auto;
    }

    .btn-danger:hover {
        background: #a93226;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .modal-overlay.active {
        display: flex;
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        width: 100%;
        max-width: 500px;
        animation: modalSlideIn 0.3s ease;
    }
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 20px;
        color: #333;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
        padding: 0;
        line-height: 1;
    }
    .modal-close:hover {
        color: #333;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #555;
        font-size: 14px;
    }
    .form-group input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }
    .form-group input:focus {
        outline: none;
        border-color: var(--primary-color, #1b75bb);
    }
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .form-actions-right {
        display: flex;
        gap: 10px;
    }
</style>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-cube"></span></div>
    <div class="pagetitle">
        <h1>Sihterice</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-6">
                <a href="javascript:void(0)" onclick="openModal()" class="btn btn-primary"><i class="fa fa-plus"></i> Add Employee</a>
            </div>
            <div class="col-md-6 align-right">
                <a href="javascript:void(0)" onclick="downloadAllPdfs()" class="btn btn-primary" style="margin-right: 15px;"><i class="fa fa-download"></i> Download All</a>
                <select id="monthSelector" class="pillow-select">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <select id="yearSelector" class="pillow-select" style="margin-left: 10px;">
                </select>
            </div>
        </div>

        <table class="table table-bordered" id="employeesTable">
            <colgroup>
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
            </colgroup>
            <thead>
                <tr>
                    <th class="head1">ID</th>
                    <th class="head0">First Name</th>
                    <th class="head1">Last Name</th>
                    <th class="head0">Email</th>
                    <th class="head1">Position</th>
                    <th class="head0 no-sort">PDF</th>
                    <th class="head1 no-sort"></th>
                </tr>
            </thead>
            <tbody id="employeesBody">
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

<!-- Modal for adding/editing-->
<div class="modal-overlay" id="employeeModal">
    <div class="modal-content">
<!--        Add Employee text header-->
        <div class="modal-header">
            <!--        Close Modal icon button-->
            <h3 id="modalTitle">Add Employee</h3>
            <button class="modal-close" onclick="closeModal()">x</button>
        </div>

        <form id="employeeForm" onsubmit="saveEmployee(event)">
            <input type="hidden" id="employeeId" value="">
            <!--input First name form-->
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName">
            </div>
            <!--input Last name form-->
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName">
            </div>
            <!--input email form-->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>
<!--input position form-->
            <div class="form-group">
                <label for="position">Position</label>
                <input type="text" id="position" name="position">
            </div>
<!--delete button-->
            <div class="form-actions">
                <button type="button" class="btn-danger" id="modalDeleteBtn" style="display: none;" onclick="deleteFromModal()">Delete</button>
                <div class="form-actions-right">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary" id="saveBtn">
                        <i class="fa fa-save" style="margin-right: 6px;"></i>Save
                    </button>
                </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="delete-modal-content">
        <div class="delete-modal-icon">
            <i class="fa fa-exclamation-triangle"></i>
        </div>
        <div class="delete-modal-title">Delete Employee</div>
        <div class="delete-modal-text">
            Are you sure you want to delete <strong id="deleteEmployeeName"></strong>?<br>
            This action cannot be undone.
        </div>
        <input type="hidden" id="deleteEmployeeId" value="">
        <div class="delete-modal-actions">
            <button class="btn-danger" id="confirmDeleteBtn" onclick="confirmDelete()">Delete</button>
            <button class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
    const API_BASE = <?php echo json_encode($tpl->get('sihtericeAddress') . '/api/employees'); ?>;
    const API_HOST = <?php echo json_encode($tpl->get('sihtericeAddress')); ?>;
    const API_TOKEN = '<?php echo $tpl->get('sihtericeToken'); ?>'
    let isEditing = false

    // MODAL FUNCTIONS
    function openModal(employee = null) {
        const modal = document.getElementById('employeeModal')
        const title = document.getElementById('modalTitle')
        const form = document.getElementById('employeeForm')
        const deleteBtn = document.getElementById('modalDeleteBtn')

        // Reset form
        form.reset()
        document.getElementById('employeeId').value = ''

        if (!employee) {
            // Add mode
            isEditing = false
            title.textContent = 'Add Employee'
            deleteBtn.style.display = 'none'
        } else {
            // Edit mode
            isEditing = true
            title.textContent = 'Edit Employee'
            document.getElementById('employeeId').value = employee.id
            document.getElementById('firstName').value = employee.firstName || ''
            document.getElementById('lastName').value = employee.lastName || ''
            document.getElementById('email').value = employee.email || ''
            document.getElementById('position').value = employee.position || ''
            deleteBtn.style.display = 'block'
        }

        modal.classList.add('active')
        document.getElementById('firstName').focus()
    }

    function closeModal() {
        const modal = document.getElementById('employeeModal')
        modal.classList.remove('active')
        isEditing = false
        document.getElementById('modalDeleteBtn').style.display = 'none'
    }

    // Close modal on overlay click
    document.getElementById('employeeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal()
        }
    })

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal()
            closeDeleteModal()
        }
    })

    // API FUNCTIONS
    async function loadEmployees() {
        // Destroy existing DataTable if it exists
        if (jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#employeesTable')) {
            jQuery('#employeesTable').DataTable().destroy();
        }

        const tbody = document.getElementById('employeesBody')
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'

        try {
            const res = await fetch(`${API_BASE}/all`, {
                headers: {'Authorization': `Bearer ${API_TOKEN}`}
            })
            const result = await res.json()

            if (result.error) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 40px;"><i class="fa fa-exclamation-triangle"></i> Error: ${result.error}</td></tr>`
                return
            }

            if (!result.data || result.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <i class="fa fa-users"></i>
                            <p>No employees found</p>
                            <a href="javascript:void(0)" onclick="openModal()" class="btn btn-primary"><i class="fa fa-plus"></i> Add First Employee</a>
                        </td>
                    </tr>
                `
                return
            }

            tbody.innerHTML = ''
            result.data.forEach(emp => {
                const tr = document.createElement('tr')
                tr.innerHTML = `
                    <td style="padding: 6px 10px;">${emp.id}</td>
                    <td style="padding: 6px 10px;"><a href="javascript:void(0)" onclick='editEmployee(${JSON.stringify(emp)})'>${escapeHtml(emp.firstName)}</a></td>
                    <td style="padding: 6px 10px;"><a href="javascript:void(0)" onclick='editEmployee(${JSON.stringify(emp)})'>${escapeHtml(emp.lastName)}</a></td>
                    <td style="padding: 6px 10px;">${escapeHtml(emp.email)}</td>
                    <td style="padding: 6px 10px;">${escapeHtml(emp.position || '-')}</td>
                    <td style="padding: 6px 10px;">
                        <a href="javascript:void(0)" onclick="downloadPdf(${emp.id})" title="Download PDF"><i class="fa fa-file-pdf-o"></i> Download</a>
                    </td>
                    <td style="padding: 6px 10px;">
                        <a href="javascript:void(0)" onclick='editEmployee(${JSON.stringify(emp)})' class="edit"><i class="fa fa-edit"></i> Edit</a>
                    </td>
                `
                tbody.appendChild(tr)
            })

            // Initialize DataTable
            if (jQuery.fn.DataTable && !jQuery.fn.DataTable.isDataTable('#employeesTable')) {
                jQuery('#employeesTable').DataTable({
                    "order": [[0, "asc"]],
                    "info": false,
                    "searching": false,
                    "paging": false,
                    "columnDefs": [
                        { "orderable": false, "targets": [5, 6] }
                    ]
                });
            }

        } catch (err) {
            console.error("Error loading employees:", err)
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 40px;"><i class="fa fa-exclamation-triangle"></i> Error loading employees</td></tr>`
        }
    }

    async function saveEmployee(event) {
        event.preventDefault()

        const id = document.getElementById('employeeId').value
        const firstName = document.getElementById('firstName').value.trim()
        const lastName = document.getElementById('lastName').value.trim()
        const email = document.getElementById('email').value.trim()
        const position = document.getElementById('position').value.trim()

        const saveBtn = document.getElementById('saveBtn')
        saveBtn.disabled = true
        saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...'

        try {
            let res
            if (id) {
                // Update existing
                res = await fetch(`${API_BASE}/edit/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${API_TOKEN}`
                    },
                    body: JSON.stringify({ firstName, lastName, email, position })
                })
            } else {
                // Create new
                res = await fetch(`${API_BASE}/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${API_TOKEN}`
                    },
                    body: JSON.stringify({ firstName, lastName, email, position })
                })
            }

            const result = await res.json()

            if (res.ok) {
                closeModal()
                loadEmployees()
                showNotification(id ? 'Employee updated successfully!' : 'Employee added successfully!', 'success')
            } else {
                showNotification(result.error || 'Error saving employee', 'error')
            }

        } catch (err) {
            console.error("Error:", err)
            showNotification('Error saving employee', 'error')
        } finally {
            saveBtn.disabled = false
            saveBtn.innerHTML = '<i class="fa fa-save" style="margin-right: 6px "></i>Save'
        }
    }

    function editEmployee(employee) {
        openModal(employee)
    }

    function deleteFromModal() {
        const id = document.getElementById('employeeId').value
        const firstName = document.getElementById('firstName').value
        const lastName = document.getElementById('lastName').value
        closeModal()
        openDeleteModal(id, firstName + ' ' + lastName)
    }

    // DELETE MODAL FUNCTIONS
    function openDeleteModal(id, name) {
        document.getElementById('deleteEmployeeId').value = id
        document.getElementById('deleteEmployeeName').textContent = name
        document.getElementById('deleteModal').classList.add('active')
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active')
        document.getElementById('deleteEmployeeId').value = ''
    }

    // Close delete modal on overlay click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal()
        }
    })

    async function confirmDelete() {
        const id = document.getElementById('deleteEmployeeId').value
        if (!id) return

        const btn = document.getElementById('confirmDeleteBtn')
        btn.disabled = true
        btn.textContent = 'Deleting...'

        try {
            const res = await fetch(`${API_BASE}/remove/${id}`, {
                method: 'DELETE',
                headers: {'Authorization': `Bearer ${API_TOKEN}`}
            })
            const result = await res.json()

            if (res.ok) {
                closeDeleteModal()
                await loadEmployees()
                showNotification('Employee deleted successfully!', 'success')
            } else {
                showNotification(result.error || 'Error deleting employee', 'error')
            }

        } catch (err) {
            console.error("Error:", err)
            showNotification('Error deleting employee', 'error')
        } finally {
            btn.disabled = false
            btn.textContent = 'Delete'
        }
    }

    // HELPER FUNCTIONS
    function escapeHtml(text) {
        if (!text) return ''
        const div = document.createElement('div')
        div.textContent = text
        return div.innerHTML
    }

    function showNotification(message, type) {
        if (typeof jQuery !== 'undefined' && jQuery.growl) {
            jQuery.growl({ message: message, style: type })
        } else {
            alert(message)
        }
    }

    // PDF DOWNLOAD (returns ZIP file from backend)
    async function downloadPdf(employeeId) {
        const month = document.getElementById('monthSelector').value
        const year = document.getElementById('yearSelector').value

        const url = `${API_HOST}/api/worksheets/download/employee/${employeeId}/${month}/${year}`

        try {
            const res = await fetch(url, {
                headers: {'Authorization': `Bearer ${API_TOKEN}`}
            })

            if (!res.ok) {
                const result = await res.json()
                showNotification(result.error || 'Error downloading worksheet', 'error')
                return
            }

            const blob = await res.blob()
            const downloadUrl = window.URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = downloadUrl
            const monthStr = month.toString().padStart(2, '0')
            a.download = `employee-${employeeId}-worksheets-${year}-${monthStr}.zip`
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
            window.URL.revokeObjectURL(downloadUrl)
        } catch (err) {
            console.error("Error downloading worksheet:", err)
            showNotification('Error downloading worksheet', 'error')
        }
    }

    // DOWNLOAD ALL EMPLOYEES PDFs
    async function downloadAllPdfs() {
        const month = document.getElementById('monthSelector').value
        const year = document.getElementById('yearSelector').value

        const url = `${API_HOST}/api/worksheets/download/employees/${month}/${year}`

        try {
            showNotification('Generating worksheets, please wait...', 'info')

            const res = await fetch(url, {
                headers: {'Authorization': `Bearer ${API_TOKEN}`}
            })

            if (!res.ok) {
                const result = await res.json()
                showNotification(result.error || 'Error downloading worksheets', 'error')
                return
            }

            const blob = await res.blob()
            const downloadUrl = window.URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = downloadUrl
            const monthStr = month.toString().padStart(2, '0')
            a.download = `worksheets-${year}-${monthStr}.zip`
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
            window.URL.revokeObjectURL(downloadUrl)

            showNotification('Download complete!', 'success')
        } catch (err) {
            showNotification('Error downloading worksheets', 'error')
        }
    }

    // INIT MONTH/YEAR SELECTORS
    function initSelectors() {
        const now = new Date()
        const currentMonth = now.getMonth() + 1
        const currentYear = now.getFullYear()

        // Set current month
        document.getElementById('monthSelector').value = currentMonth

        // Populate years (current year and 2 years back)
        const yearSelector = document.getElementById('yearSelector')

        for (let y = currentYear; y >= currentYear - 2; y--) {
            const option = document.createElement('option')
            option.value = y
            option.textContent = y
            yearSelector.appendChild(option)
        }
        yearSelector.value = currentYear
    }

    // INIT
    initSelectors()
    loadEmployees()
</script>
