<?php
require_once 'config/database.php';
include 'includes/header.php';
?>
<html>
    <head>
      <style>
        .home-page {
         padding-top: 0px;
        }
        </style>
    </head>
</html>
<!-- Hero Section -->
<header class="hero-section">
    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold">Welcome to EduHub</h1>
                <p class="lead">Your one-stop destination for quality educational resources</p>
                <a href="subjects.php" class="btn btn-primary btn-lg">Explore Subjects</a>
            </div>
        </div>
    </div>
</header>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose EduHub?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                        <h3>Comprehensive Study Materials</h3>
                        <p>Access high-quality study materials for various subjects</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-laptop-code fa-3x mb-3 text-primary"></i>
                        <h3>Practical Learning</h3>
                        <p>Step-by-step practical guides and examples</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf fa-3x mb-3 text-primary"></i>
                        <h3>Previous Year Papers</h3>
                        <p>Download previous year question papers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 