/**
 * Vanilla replacement for Bootstrap 2.x dropdown JS.
 *
 * Listens for clicks on [data-toggle="dropdown"] and toggles the .open class
 * on the parent .dropdown or .btn-group — identical to Bootstrap 2.x behavior.
 * Also handles keyboard Escape to close, and clicks outside to dismiss.
 */
document.addEventListener('click', function (e) {
    var toggle = e.target.closest('[data-toggle="dropdown"]');

    // Close all open dropdowns that don't contain the clicked toggle
    document.querySelectorAll('.dropdown.open, .btn-group.open').forEach(function (el) {
        if (!toggle || !el.contains(toggle)) {
            el.classList.remove('open');
        }
    });

    // Toggle clicked dropdown
    if (toggle) {
        e.preventDefault();
        e.stopPropagation();
        var parent = toggle.closest('.dropdown, .btn-group');
        if (parent) {
            parent.classList.toggle('open');
        }
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown.open, .btn-group.open').forEach(function (el) {
            el.classList.remove('open');
        });
    }
});
