<?php
 session_start();
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

// Connexion à la base de données et récupération des produits (déplacé en haut)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ecomerce', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les catégories avec leurs produits
    $query = "
       SELECT 
        c.NOM_CATEGORIE, 
        p.ID_PRODUIT,
        p.NOM_PRODUIT, 
        p.DESCRIPTION_PRODUIT, 
        p.PRIX, 
        p.QUANTITE_STOCK, 
        p.MARQUE, 
        i.FILE,
        COUNT(a.ID_AVIS) AS REVIEW_COUNT,
        AVG(a.RATING) AS AVG_RATING
    FROM categorie c
    JOIN produit p ON c.ID_CATEGORIE = p.ID_CATEGORIE
    LEFT JOIN image i ON p.ID_PRODUIT = i.ID_PRODUIT
    LEFT JOIN avis a ON p.ID_PRODUIT = a.ID_PRODUIT
    GROUP BY 
        c.NOM_CATEGORIE, 
        p.ID_PRODUIT, 
        p.NOM_PRODUIT, 
        p.DESCRIPTION_PRODUIT, 
        p.PRIX, 
        p.QUANTITE_STOCK, 
        p.MARQUE, 
        i.FILE
    ORDER BY c.NOM_CATEGORIE, p.NOM_PRODUIT;
    ";
    $stmt = $pdo->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gestion des erreurs
    error_log('Erreur de base de données: ' . $e->getMessage());
    $products = []; // tableau vide en cas d'erreur
}

// Classe CSRF pour la génération de token (ajout d'une implementation minimale)
class CSRF {
    public function generateToken() {
        // Implémentation simplifiée pour l'exemple
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
$csrf = new CSRF();
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
    <!-- Bootstrap Icons - ajout manquant -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
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

  
        /* Variables pour les couleurs */
        :root {
            --primary-color: #3a86ff;
            --secondary-color: #ff006e;
            --accent-color: #fb8500;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4caf50;
        }
    
        /* Style général pour les catégories */
        .category-title {
            position: relative;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 30px;
            color: var(--dark-color);
            padding-bottom: 10px;
        }
    
        .category-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--secondary-color);
        }
    
        /* Style pour les cartes de produits - version compacte */
        .product-card {
            border: none;
            transition: all 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
            max-width: 260px;
            margin: 0 auto;
        }
    
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
    
        .product-img-container {
            position: relative;
            overflow: hidden;
            height: 160px;
        }
    
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
    
        .product-card:hover .product-img {
            transform: scale(1.05);
        }
    
        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
        }
    
        .product-badge.new {
            background-color: var(--primary-color);
            color: white;
        }
    
        .product-badge.sale {
            background-color: var(--secondary-color);
            color: white;
        }
    
        .product-actions {
            position: absolute;
            right: 15px;
            top: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 2;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
        }
    
        .product-card:hover .product-actions {
            opacity: 1;
            transform: translateX(0);
        }
    
        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            color: var(--dark-color);
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
    
        .action-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }
    
        .product-content {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
    
        .product-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-color);
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
    
        .product-desc {
            color: #6c757d;
            font-size: 0.8rem;
            margin-bottom: 10px;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
        }
    
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
    
        .product-price {
            font-weight: 700;
            font-size: 1rem;
            color: var(--secondary-color);
        }
    
        .product-rating {
            display: flex;
            align-items: center;
        }
    
        .rating-stars {
            color: var(--accent-color);
            font-size: 0.75rem;
        }
    
        .rating-count {
            font-size: 0.8rem;
            color: #6c757d;
            margin-left: 5px;
        }
    
        .product-footer {
            border-top: 1px solid #eee;
            padding: 10px 12px;
            background-color: white;
        }
    
        .add-to-cart {
            padding: 6px 12px;
            border-radius: 20px;
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
            flex-grow: 1;
            font-size: 0.8rem;
        }
    
        .add-to-cart:hover {
            background-color: var(--dark-color);
        }
    
        .wishlist-btn {
            border: 1px solid #dee2e6;
            background-color: white;
            color: #6c757d;
            transition: all 0.3s;
        }
    
        .wishlist-btn:hover {
            background-color: #f8f9fa;
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
    
        .wishlist-btn.active {
            color: var(--secondary-color);
        }
    
        /* Badge de disponibilité */
        .stock-badge {
            font-size: 0.65rem;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 20px;
            margin-right: 5px;
        }
    
        .in-stock {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }
    
        .low-stock {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
    
        .out-of-stock {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }
    
        /* Styles responsive */
        @media (max-width: 768px) {
            .product-actions {
                opacity: 1;
                transform: translateX(0);
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
                <?php if (isset($_SESSION['user_id'])): ?>
    <span class="me-3">
        <i class="fas fa-user-circle me-1"></i>
        <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?>
    </span>
    <a href="pages/logout.php" class="btn btn-sm btn-outline-secondary">Déconnexion</a>
<?php else: ?>
    <a href="pages/login.php" class="me-3"><i class="fas fa-sign-in-alt me-1"></i>Connexion</a>
    <a href="pages/register.php"><i class="fas fa-user-plus me-1"></i>Inscription</a>
<?php endif; ?>
</div>

            </div>
        </div>
    </div>
    
    <!-- Navbar principale -->
    <nav class="navbar navbar-expand-lg sticky-top"  style="height: 80px;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="<?php echo $logo; ?>" alt="logo" style="width: 150px; height: auto; max-height: 100px;">
            </a>
         
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="page_de_recapitulatif.php">Mes commandes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/about.php">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/contact.php">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3">
        <a class="nav-link cart-icon" href="show_panier.php">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge bg-danger rounded-pill cart-badge"><?php echo $cartItemCount; ?></span>
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
        <!-- Affichage des catégories et produits -->
        <div class="row">
            <div class="col-12">
                <h1>Bienvenue sur notre boutique en ligne</h1>
                <p class="lead">Découvrez nos derniers produits et profitez de nos offres spéciales.</p>
            </div>
        </div>
        
        <?php foreach ($products as $category => $items): ?>
<section class="products-section mb-5">
    <h2 class="category-title mt-5"><?php echo htmlspecialchars($category); ?></h2>
    
    <!-- Conteneur avec défilement horizontal pour les produits de même catégorie -->
    <div class="products-container">
        <div class="row flex-nowrap overflow-auto g-4 pb-3">
            <?php foreach ($items as $product): 
            $rating = isset($product['AVG_RATING']) && $product['AVG_RATING'] ? $product['AVG_RATING'] / 10 : 0;
            $reviewCount = $product['REVIEW_COUNT'] ?? 0;
            $isNew = mt_rand(0, 10) > 8;
            $isSale = mt_rand(0, 10) > 8 && !$isNew;
            $stockLevel = isset($product['QUANTITE_STOCK']) ? (int)$product['QUANTITE_STOCK'] : 0;
            $stockStatus = $stockLevel <= 0 ? 'out' : ($stockLevel <= 5 ? 'low' : 'in');
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-9">
                <div class="product-card h-100">
                    <a href="show_product.php?id=<?php echo $product['ID_PRODUIT']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-img-container">
                            <?php if ($isNew): ?>
                                <div class="product-badge new">Nouveau</div>
                            <?php endif; ?>
                            
                            <div class="product-actions">
                                <button class="action-btn" title="Vue rapide">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="action-btn" title="Comparer">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                <button class="action-btn" title="Partager">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                            
                            <?php 
                            if (!empty($product['FILE'])) {
                                $image_data = base64_encode($product['FILE']);
                                $image_type = 'image/jpeg'; 
                                
                                echo '<img src="data:' . $image_type . ';base64,' . $image_data . '" alt="' . htmlspecialchars($product['NOM_PRODUIT']) . '" class="product-img">';
                            } else {
                                echo '<img src="../storage/default.jpg" alt="Image par défaut" class="product-img">';
                            }
                            ?>
                        </div>
                        
                        <div class="product-content">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['NOM_PRODUIT']); ?></h3>
                            <h4 class="product-title"><?php echo htmlspecialchars($product['MARQUE']); ?></h4>
                            <p class="product-desc">
                                <?php echo htmlspecialchars(mb_strimwidth($product['DESCRIPTION_PRODUIT'] ?? '', 0, 100, '...')); ?>
                            </p>
                            
                            <div class="product-meta">
                                <span class="product-price"><?php echo htmlspecialchars($product['PRIX']); ?> MAD</span>
                                <div class="product-rating">
                                    <div class="rating-stars">
                                        <?php 
                                        $fullStars = floor($rating);
                                        $halfStar = $rating - $fullStars >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<i class="bi bi-star-fill"></i> ';
                                        }
                                        if ($halfStar) {
                                            echo '<i class="bi bi-star-half"></i> ';
                                        }
                                        for ($i = 0; $i < $emptyStars; $i++) {
                                            echo '<i class="bi bi-star"></i> ';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-count">(<?php echo $reviewCount; ?>)</span>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($stockStatus === 'in'): ?>
                                <span class="stock-badge in-stock"><i class="bi bi-check-circle me-1"></i>En stock (<?php echo $stockLevel; ?>)</span>
                                <?php elseif ($stockStatus === 'low'): ?>
                                <span class="stock-badge low-stock"><i class="bi bi-exclamation-circle me-1"></i>Stock faible (<?php echo $stockLevel; ?>)</span>
                                <?php else: ?>
                                <span class="stock-badge out-of-stock"><i class="bi bi-x-circle me-1"></i>Épuisé</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="product-footer">
                            <div class="d-flex gap-2">
                                <button class="add-to-cart" data-product-id="<?php echo $product['ID_PRODUIT']; ?>">
                                    <i class="bi bi-cart-plus me-1"></i>Ajouter
                                </button>
                                <button class="action-btn wishlist-btn" title="Ajouter aux favoris">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    
    </div>
</section>
<?php endforeach; ?>
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
    
    <!-- Script pour la fonctionnalité des boutons favoris -->
    <script>
   
   document.addEventListener('DOMContentLoaded', function() {
        // Gestion des boutons favoris
        const wishlistBtns = document.querySelectorAll('.wishlist-btn');
        wishlistBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); // Empêcher la navigation
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                if (icon.classList.contains('bi-heart')) {
                    icon.classList.replace('bi-heart', 'bi-heart-fill');
                } else {
                    icon.classList.replace('bi-heart-fill', 'bi-heart');
                }
            });
        });
        
        // Gestion des boutons d'ajout au panier
        const addToCartBtns = document.querySelectorAll('.add-to-cart');
        const cartBadge = document.querySelector('.cart-badge');

        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Empêcher la navigation si le bouton est dans un lien
                e.preventDefault();
                e.stopPropagation(); // Arrêter la propagation pour éviter de cliquer sur le lien parent
                
                const productId = this.getAttribute('data-product-id');
                console.log('Ajout du produit #' + productId + ' au panier');
                
                // Modifier l'apparence du bouton avant l'appel AJAX
                this.innerHTML = '<i class="bi bi-cart-dash me-1"></i>Vérification...';
                this.style.backgroundColor = '#ffc107'; // Couleur d'avertissement
                
                // Appel AJAX pour ajouter au panier
                fetch('add_to_cart.php', {
                    method: 'POST',
                    body: JSON.stringify({ productId: productId }),
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour le compteur du panier avec le nouveau nombre de produits
                        cartBadge.textContent = data.cartCount;
                        this.innerHTML = '<i class="bi bi-check2 me-1"></i>Ajouté';
                        this.style.backgroundColor = '#28a745'; // Couleur de succès
                    } else {
                        // Message d'erreur si stock épuisé
                        this.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Indisponible';
                        this.style.backgroundColor = '#dc3545'; // Couleur d'erreur
                        alert(data.message);  // Afficher une alerte
                    }
                    
                    // Réinitialiser le bouton après 1.5 secondes
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Ajouter';
                        this.style.backgroundColor = '';
                    }, 1500);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    this.innerHTML = '<i class="bi bi-x-circle me-1"></i>Erreur';
                    this.style.backgroundColor = '#dc3545'; // Couleur d'erreur
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Ajouter';
                        this.style.backgroundColor = '';
                    }, 1500);
                    alert('Une erreur est survenue. Veuillez réessayer.');
                });
            });
        });
    });
    </script>
</body>
</html>