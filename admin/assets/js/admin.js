document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });

    var currentPath = window.location.pathname.split('/').pop() || 'index.php';
    var currentPage = currentPath.replace('.php', '');

    document.querySelectorAll('.sidebar-nav .nav-item').forEach(function(item) {
        item.classList.remove('active');
    });

    document.querySelectorAll('.sidebar-nav .nav-link').forEach(function(link) {
        var href = link.getAttribute('href');
        if (href) {
            var linkPage = href.split('/').pop().replace('.php', '');
            if (linkPage === currentPage) {
                var parentItem = link.closest('.nav-item');
                if (parentItem) parentItem.classList.add('active');
                var parentSubmenu = link.closest('.submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.add('show');
                    var parentNavItem = parentSubmenu.closest('.nav-item');
                    if (parentNavItem) parentNavItem.classList.add('open');
                }
            }
        }
    });

    document.querySelectorAll('.has-submenu').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var parent = this.closest('.nav-item');
            if (parent) {
                setTimeout(function() {
                    if (parent.classList.contains('open')) {
                        parent.classList.remove('open');
                    } else {
                        parent.classList.add('open');
                    }
                }, 10);
            }
        });
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    });

    if (window.innerWidth <= 768) {
        document.getElementById('sidebar').classList.add('collapsed');
    }
});
