<?php

declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/sanitize.php';
require_once __DIR__ . '/src/helpers/flash.php';

session_start();

// Require donor authentication.
require_once __DIR__ . '/src/middleware/require_auth.php';

$donorId = (int)$_SESSION['donorId'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Donor Dashboard | Helping Paws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand" href="DonorLanding.php">Donor Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="landingPage.html">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Donation.html">Make a Donation</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">Profile</a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="donor_profile.php">Your Profile</a></li>
                        <li><a class="dropdown-item" href="DonorHistory.php">Donation History</a></li>
                    </ul>
                </li>
            </ul>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>

<header class="py-5" style="height:400px;">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h1 class="display-4 fw-bolder">Become the Shelter</h1>
            <p class="lead fw-normal text-white-50 mb-0">Make a Pawsitive Impact</p>
        </div>
    </div>
</header>

<section class="py-5">
    <div class="container px-4 px-lg-5 mt-5">
        <?php flashRender(); ?>
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
            <!-- Weekly -->
            <div class="col mb-5">
                <div class="card h-100">
                    <img class="card-img-top" src="/Images/bar1.jpg" alt="Weekly donor">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bolder">Weekly Donor</h5>
                        <p>500.00 TAKA</p>
                        <p>These little souls need undivided attention – check up on them every week.</p>
                    </div>
                </div>
            </div>
            <!-- Monthly -->
            <div class="col mb-5">
                <div class="card h-100">
                    <img class="card-img-top" src="/Images/bar2.jpg" alt="Monthly donor">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bolder">Monthly Donor</h5>
                        <p>2,000.00 TAKA</p>
                        <p>Help the paws in need on a monthly basis.</p>
                    </div>
                </div>
            </div>
            <!-- Medical -->
            <div class="col mb-5">
                <div class="card h-100">
                    <img class="card-img-top" src="/Images/bar3.jpg" alt="Medical donation">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bolder">Medical Donation</h5>
                        <p>5,000.00 TAKA</p>
                        <p>Each donation for medical care matters.</p>
                    </div>
                </div>
            </div>
            <!-- Shelter -->
            <div class="col mb-5">
                <div class="card h-100">
                    <img class="card-img-top" src="/Images/shelter bar.jpg" alt="Build a shelter">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bolder">Build a Shelter</h5>
                        <p>10,000.00 TAKA</p>
                        <p>Contribute to a safe home for animals in need.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="Donation.html" class="btn btn-primary btn-lg">Make a Donation Now</a>
        </div>
    </div>
</section>

<footer class="py-5 bg-dark">
    <div class="container">
        <p class="m-0 text-center text-white">
            Copyright &copy; <?php echo date('Y'); ?> Helping Paws | Non-Profit Organization
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
