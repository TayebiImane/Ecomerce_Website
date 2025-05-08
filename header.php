<?php
// Remplacez ces valeurs par vos propres données
$logo = 'logo.png';
$favicon = 'mon_favicon.png';
$meta_title_home = 'Bienvenue sur Mon Site E-commerce';
$meta_keyword_home = 'e-commerce, boutique en ligne, produits';
$meta_description_home = 'Découvrez notre boutique en ligne avec une large gamme de produits.';
$contact_email = 'contact@monsite.com';
$contact_phone = '+212 6 23 25 26 23';
$before_head = '<!-- Code personnalisé avant la balise </head> -->';
$after_body = '<!-- Code personnalisé après la balise <body> -->';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <link rel="icon" type="image/png" href="assets/uploads/<?php echo $favicon; ?>">
    <title><?php echo $meta_title_home; ?></title>
    <meta name="keywords" content="<?php echo $meta_keyword_home; ?>">
    <meta name="description" content="<?php echo $meta_description_home; ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personnalisé -->
    <style>
        :root {
            --primary-color: #3a86ff;
            --secondary-color: #ff006e;
            --dark-color: #212529;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .top-bar {
            background-color: var(--dark-color);
            color: white;
            font-size: 0.9rem;
            padding: 8px 0;
        }
        
        .top-bar a {
            color: white;
            text-decoration: none;
        }
        
        .top-bar a:hover {
            color: var(--secondary-color);
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            max-height: 50px;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--dark-color) !important;
            margin: 0 5px;
            position: relative;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.3s;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2a75e8;
            border-color: #2a75e8;
        }
        
        .btn-outline-danger {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-danger:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .search-form {
            position: relative;
        }
        
        .search-form .form-control {
            padding-right: 40px;
            border-radius: 20px;
        }
        
        .search-form .btn {
            position: absolute;
            right: 5px;
            top: 5px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        .cart-icon {
            position: relative;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.7rem;
        }
        
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: white;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                margin-top: 10px;
            }
            
            .nav-link::after {
                display: none;
            }
        }
    </style>
    <?php echo $before_head; ?>
</head>
<body>
    <?php echo $after_body; ?>
   
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span><i class="fas fa-envelope me-2"></i><?php echo $contact_email; ?></span>
                    <span class="ms-3"><i class="fas fa-phone me-2"></i><?php echo $contact_phone; ?></span>
                </div>
                <div class="col-md-6 text-end">
                    <a href="pages/login.php" class="me-3"><i class="fas fa-sign-in-alt me-1"></i>Connexion</a>
                    <a href="pages/register.php"><i class="fas fa-user-plus me-1"></i>Inscription</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navbar principale -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="<?php echo $logo; ?>" alt="logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="pages/index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/categories.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/about.php">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/contact.php">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link cart-icon" href="pages/cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger rounded-pill cart-badge">0</span>
                        </a>
                    </li>
                </ul>
                
                <form class="search-form d-flex ms-lg-3 mt-3 mt-lg-0" action="search-result.php" method="get">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf->generateToken(); ?>">
                    <input class="form-control me-2" type="search" placeholder="Rechercher..." name="search_text" aria-label="Rechercher">
                    <button class="btn btn-outline-danger" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="container py-5">
        <!-- Le contenu de votre page ira ici -->
        <div class="row">
            <div class="col-12">
                <h1>Bienvenue sur notre boutique en ligne</h1>
                <p class="lead">Découvrez nos derniers produits et profitez de nos offres spéciales.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>À propos de nous</h5>
                    <p>Notre boutique en ligne vous propose une large gamme de produits de qualité.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Conditions générales</a></li>
                        <li><a href="#" class="text-white">Politique de confidentialité</a></li>
                        <li><a href="#" class="text-white">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i><?php echo $contact_email; ?></li>
                        <li><i class="fas fa-phone me-2"></i><?php echo $contact_phone; ?></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Mon Site E-commerce. Tous droits réservés.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>