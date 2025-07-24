<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Eduhub</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        /* Sidebar Toggle Button */
        #sidebarToggleFixed {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1029;
            width: 20px;
            height: 40px;
            background: #343a40;
            border: none;
            border-radius: 0 4px 4px 0;
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #sidebarToggleFixed:hover {
            background: #23272b;
            width: 25px;
        }

        .sidebar.collapsed + main #sidebarToggleFixed {
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            transition: all 0.3s ease;
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 16.66667%;
        }

        .sidebar.collapsed {
            margin-left: -16.66667%;
        }

        .sidebar .nav-link {
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
        }

        main {
            transition: all 0.3s ease;
            margin-left: 16.66667%;
        }

        main.expanded {
            margin-left: 0 !important;
            width: 100% !important;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                margin-left: -100%;
                width: 100%;
            }
            
            .sidebar.show {
                margin-left: 0;
            }

            main {
                margin-left: 0 !important;
                width: 100% !important;
            }

            #sidebarToggleFixed {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">Eduhub Admin</a>
        <button class="navbar-toggler position-absolute d-md-none" type="button" onclick="toggleMobileSidebar()">
            <span class="navbar-toggler-icon"></span>
        </button>
    </header>

    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h1>
                </div>
            </main>
        </div>
    </div>

    <!-- Fixed Toggle Button -->
    <button id="sidebarToggleFixed" class="d-none d-md-flex">
        <i class="fas fa-chevron-right"></i>
    </button>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarToggleFixed = document.getElementById('sidebarToggleFixed');
        const mainContent = document.querySelector('main');
        
        // Check localStorage for saved state
        const sidebarState = localStorage.getItem('sidebarState');
        if (sidebarState === 'collapsed' && window.innerWidth > 767.98) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Desktop toggle functions
        function toggleSidebar() {
            if (window.innerWidth > 767.98) {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Update toggle button icon
                const isCollapsed = sidebar.classList.contains('collapsed');
                sidebarToggleFixed.innerHTML = `<i class="fas fa-chevron-${isCollapsed ? 'right' : 'left'}"></i>`;
                
                // Save state to localStorage
                localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
            }
        }

        // Add click handlers
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        if (sidebarToggleFixed) {
            sidebarToggleFixed.addEventListener('click', toggleSidebar);
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 767.98) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                sidebar.classList.remove('show');
            }
        });
    });

    // Mobile toggle function
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth <= 767.98) {
            sidebar.classList.toggle('show');
        }
    }
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 