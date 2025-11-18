<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Spanning Tree Visualizer')</title>
    
    <!-- Google Fonts - menggunakan font Inter untuk tampilan modern -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome untuk icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Header / Navbar -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-project-diagram"></i>
                    <span>Spanning Tree Visualizer</span>
                </div>
                <nav class="nav-menu">
                    <a href="#" class="nav-link active">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-book"></i> Documentation
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Spanning Tree Visualizer. Built with Laravel & Vis.js</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>