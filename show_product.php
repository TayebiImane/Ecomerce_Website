<?php
include 'includes/db1.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérification de l'ID produit
if (isset($_GET['id'])) {
    $id_produit = $_GET['id'];

    // Requête pour obtenir les détails du produit
    $sql = "
    SELECT 
        P.*, 
        C.NOM_CATEGORIE, 
        SP.LIBELLE_STAT_PROD AS STATU_PRODUIT, 
        TP.VALEUR AS TAUX_PROMOTION, 
        TP.LIBELLE_PROMOTION, 
        PR.DATE_DEBUT, 
        PR.DATE_FIN, 
        D.NOM_DEVISE, 
        D.SYMBOLE, 
        i.FILE
    FROM produit P
    INNER JOIN categorie C ON P.ID_CATEGORIE = C.ID_CATEGORIE
    INNER JOIN statut_produit SP ON P.ID_STATU_PRODUIT = SP.ID_STATU_PRODUIT
    LEFT JOIN promotion PR ON P.ID_PROMOTION = PR.ID_PROMOTION
    LEFT JOIN type_promotion TP ON PR.ID_TYPE_PROMOTION = TP.ID_TYPE_PROMOTION
    INNER JOIN devise D ON P.ID_DEVISE = D.ID_DEVISE
    LEFT JOIN image i ON P.ID_PRODUIT = i.ID_PRODUIT
    WHERE P.ID_PRODUIT = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_produit]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($produits) > 0) {
        $produit = $produits[0]; // prend la première image
    } else {
        echo '<div class="container"><div class="alert alert-danger">Produit non trouvé.</div></div>';
        exit;
    }
} else {
    echo '<div class="container"><div class="alert alert-danger">Aucun produit sélectionné.</div></div>';
    exit;
}

// Vérification si l'utilisateur est connecté et gestion du panier
$est_dans_panier = false;
$id_panier = null;
$id_utilisateur = null;

if (isset($_SESSION['user_id'])) {
    $id_utilisateur = $_SESSION['user_id'];

    // Récupération ou création du panier
    $sqlPanier = "SELECT ID_PANIER FROM panier WHERE ID_USER = ?";
    $stmtPanier = $conn->prepare($sqlPanier);
    $stmtPanier->execute([$id_utilisateur]);

    if ($stmtPanier->rowCount() > 0) {
        $panier = $stmtPanier->fetch();
        $id_panier = $panier['ID_PANIER'];  
    } else {
        $sqlInsertPanier = "INSERT INTO panier (ID_USER) VALUES (?)";
        $stmtInsertPanier = $conn->prepare($sqlInsertPanier);
        $stmtInsertPanier->execute([$id_utilisateur]);
        $id_panier = $conn->lastInsertId();
    }

    // Vérifier si le produit est déjà dans le panier
    $sqlCheck = "SELECT * FROM panier_produit WHERE ID_PANIER = ? AND ID_PRODUIT = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->execute([$id_panier, $id_produit]);
    $est_dans_panier = $stmtCheck->rowCount() > 0;
}

// Traitement des actions du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_panier']) && isset($id_panier)) {
    if ($_POST['action_panier'] === 'ajouter') {
        $sqlInsert = "INSERT INTO panier_produit (ID_PANIER, ID_PRODUIT, QUANTITE_PROUIT) VALUES (?, ?, 1)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->execute([$id_panier, $id_produit]);
        $est_dans_panier = true;
    } elseif ($_POST['action_panier'] === 'retirer') {
        $sqlDelete = "DELETE FROM panier_produit WHERE ID_PANIER = ? AND ID_PRODUIT = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->execute([$id_panier, $id_produit]);
        $est_dans_panier = false;
    }
}

// Ajouter un avis
if (isset($_POST['ajouter_avis']) && isset($id_utilisateur)) {
    $texte_avis = $_POST['avis'];
    $rating = $_POST['rating'];

    $sqlAvis = "INSERT INTO avis (ID_USER, ID_PRODUIT, TEXTE, RATING, DATE_AVIS) VALUES (?, ?, ?, ?, ?)";
    $stmtAvis = $conn->prepare($sqlAvis);
    $stmtAvis->execute([$id_utilisateur, $id_produit, $texte_avis, $rating, date('Y-m-d')]);

    echo '<div class="container"><div class="alert alert-success">Avis ajouté avec succès !</div></div>';
}

// Récupération des avis existants
$sqlAvisExistants = "SELECT A.TEXTE, A.RATING, A.DATE_AVIS, U.NOM_USER AS NOM_UTILISATEUR, U.PRENOM_USER AS PRENOM_UTILISATEUR
                     FROM avis A 
                     INNER JOIN utilisateur U ON A.ID_USER = U.ID_USER
                     WHERE A.ID_PRODUIT = ? 
                     ORDER BY A.DATE_AVIS DESC";

$stmtAvisExistants = $conn->prepare($sqlAvisExistants);
$stmtAvisExistants->execute([$id_produit]);
$avis = $stmtAvisExistants->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du produit</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        /* ======= GENERAL STYLES ======= */
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --warning-color: #fca311;
            --danger-color: #e63946;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
        }
        
        h2 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 10px;
        }
        
        h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 60px;
            background-color: var(--primary-color);
        }
        
        /* ======= PRODUCT DETAILS ======= */
        .product-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .product-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        
        .product-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 24px;
        }
        
        .product-body {
            padding: 30px;
        }
        
        .product-img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            max-height: 300px;
            object-fit: contain;
        }
        
        .product-info {
            margin-bottom: 30px;
        }
        
        .product-info p {
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .product-info strong {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .promotion-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            margin-left: 10px;
            font-size: 14px;
        }
        
        .promotion-dates {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .stock-status {
            background-color: #e2f4ea;
            color: #198754;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .stock-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .stock-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-cart {
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-cart.btn-add {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-cart.btn-add:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-cart.btn-remove {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-cart.btn-remove:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        /* ======= REVIEW SECTION ======= */
        .review-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 40px;
        }
        
        .review-container h3 {
            margin-bottom: 20px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .review-form {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 10px;
            display: block;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .review-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .review-date {
            color: #6c757d;
            font-size: 14px;
        }
        
        .rating {
            margin-bottom: 10px;
        }
        
        .rating .fa-star {
            color: #ffc107;
            margin-right: 3px;
        }
        
        .rating .fa-star.empty {
            color: #e9ecef;
        }
        
        .review-text {
            color: #495057;
            line-height: 1.6;
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        /* ======= RESPONSIVE STYLES ======= */
        @media (max-width: 768px) {
            .product-header h3 {
                font-size: 20px;
            }
            
            .product-body {
                padding: 20px;
            }
            
            .price {
                font-size: 20px;
            }
            
            .review-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg sticky-top" style="height: 80px;">
        <div class="container">
        <a class="navbar-brand" href="index.php" >
                <img src="logo.png" alt="logo" style="width: 150px; height: auto; max-height: 100px;">
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
                   
                </ul>
                
                <?php if (isset($csrf)): // Vérification si $csrf est défini ?>
                <form class="search-form d-flex ms-lg-3 mt-3 mt-lg-0" action="search-result.php" method="get">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf->generateToken(); ?>">
                    <input class="form-control me-2" type="search" placeholder="Rechercher..." name="search_text" aria-label="Rechercher">
                    <button class="btn btn-outline-danger" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php else: // Si $csrf n'est pas défini, formulaire sans token ?>
                <form class="search-form d-flex ms-lg-3 mt-3 mt-lg-0" action="search-result.php" method="get">
                    <input class="form-control me-2" type="search" placeholder="Rechercher..." name="search_text" aria-label="Rechercher">
                    <button class="btn btn-outline-danger" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </nav>



<div class="container">
    <h2>Détails du produit</h2>
    
    <div class="product-container">
        <div class="product-header">
            <h3><?php echo htmlspecialchars($produit['NOM_PRODUIT']); ?></h3>
        </div>
        
        <div class="product-body">
            <div class="row">
                <div class="col-md-5">
                    <?php 
                    if (!empty($produit['FILE'])) {
                        $image_data = base64_encode($produit['FILE']);
                        $image_type = 'image/jpeg';
                        echo '<img src="data:' . $image_type . ';base64,' . $image_data . '" alt="' . htmlspecialchars($produit['NOM_PRODUIT']) . '" class="product-img">';
                    } else {
                        echo '<img src="../storage/default.jpg" alt="Image par défaut" class="product-img">';
                    }
                    ?>
                </div>
                
                <div class="col-md-7">
                    <div class="product-info">
                        <div class="price">
                            <?php echo $produit['PRIX'] . ' ' . $produit['SYMBOLE']; ?>
                            <?php if (!empty($produit['TAUX_PROMOTION'])): ?>
                                <span class="promotion-badge">
                                    <?php echo htmlspecialchars($produit['LIBELLE_PROMOTION']) . ' (' . $produit['TAUX_PROMOTION'] . '%)'; ?>
                                </span>
                                <div class="promotion-dates">
                                    <i class="far fa-calendar-alt"></i> Du <?php echo htmlspecialchars($produit['DATE_DEBUT']); ?> au <?php echo htmlspecialchars($produit['DATE_FIN']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php
                        $stock_class = '';
                        if ($produit['QUANTITE_STOCK'] > 10) {
                            $stock_class = '';
                        } elseif ($produit['QUANTITE_STOCK'] > 0) {
                            $stock_class = 'stock-warning';
                        } else {
                            $stock_class = 'stock-danger';
                        }
                        ?>
                        
                        <?php if ($produit['QUANTITE_STOCK'] > 0): ?>
    <div class="stock-status <?php echo $stock_class; ?>">
        <i class="fas fa-cubes"></i> <?php echo htmlspecialchars($produit['QUANTITE_STOCK']); ?> en stock
    </div>
<?php endif; ?>
                        
                        <p><?php echo htmlspecialchars($produit['DESCRIPTION_PRODUIT']); ?></p>
                        
                        <table class="table table-bordered mt-4">
                            <tbody>
                                <tr>
                                    <td><strong><i class="fas fa-tag"></i> Marque</strong></td>
                                    <td><?php echo htmlspecialchars($produit['MARQUE']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-folder"></i> Catégorie</strong></td>
                                    <td><?php echo htmlspecialchars($produit['NOM_CATEGORIE']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-info-circle"></i> Statut</strong></td>
                                    <td><?php echo htmlspecialchars($produit['STATU_PRODUIT']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <?php if (isset($id_utilisateur)): ?>
    <?php if ($produit['QUANTITE_STOCK'] > 0): ?>
        <form method="post" class="mt-4">
            <input type="hidden" name="action_panier" value="<?php echo $est_dans_panier ? 'retirer' : 'ajouter'; ?>">
            <button type="submit" class="btn btn-cart <?php echo $est_dans_panier ? 'btn-remove' : 'btn-add'; ?>">
                <?php if ($est_dans_panier): ?>
                    <i class="fas fa-trash-alt"></i> Retirer du panier
                <?php else: ?>
                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                <?php endif; ?>
            </button>
        </form>
    <?php else: ?>
        <button class="btn btn-cart btn-disabled" disabled>
            <i class="fas fa-shopping-cart"></i> Produit épuisé
        </button>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Veuillez vous connecter pour ajouter ce produit à votre panier.
    </div>
<?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section des avis -->
    <div class="review-container">
        <h3><i class="far fa-comment-dots"></i> Avis des utilisateurs</h3>
        
        <!-- Formulaire pour ajouter un avis -->
        <?php if (isset($id_utilisateur)): ?>
        <div class="review-form">
            <h4 class="mb-4">Partagez votre avis</h4>
            <form method="post">
                <div class="form-group">
                    <label for="rating" class="form-label">Note :</label>
                    <div class="rating-select">
                        <select name="rating" id="rating" class="form-select" required>
                            <option value="1">1 - Très mauvais</option>
                            <option value="2">2 - Mauvais</option>
                            <option value="3">3 - Moyen</option>
                            <option value="4">4 - Bon</option>
                            <option value="5" selected>5 - Excellent</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="avis" class="form-label">Votre commentaire :</label>
                    <textarea name="avis" id="avis" class="form-control" rows="4" required placeholder="Partagez votre expérience avec ce produit..."></textarea>
                </div>
                <button type="submit" name="ajouter_avis" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Soumettre mon avis
                </button>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i> Veuillez vous connecter pour laisser un avis sur ce produit.
            </div>
        <?php endif; ?>
        
        <!-- Liste des avis existants -->
        <?php if (count($avis) > 0): ?>
            <div class="review-list">
                <?php foreach ($avis as $unAvis): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-name">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($unAvis['PRENOM_UTILISATEUR'] . ' ' . $unAvis['NOM_UTILISATEUR']); ?>
                            </div>
                            <div class="review-date">
                                <i class="far fa-calendar"></i>
                                <?php echo htmlspecialchars($unAvis['DATE_AVIS']); ?>
                            </div>
                        </div>
                        
                        <div class="rating">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $unAvis['RATING']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="fas fa-star empty"></i>';
                                }
                            }
                            ?>
                            <span class="ms-2"><?php echo $unAvis['RATING']; ?>/5</span>
                        </div>
                        
                        <div class="review-text">
                            <?php echo nl2br(htmlspecialchars($unAvis['TEXTE'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun avis n'a encore été laissé pour ce produit. Soyez le premier à donner votre avis !
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap & JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Ajouter des effets lors du chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        // Animation pour l'image du produit
        const productImg = document.querySelector('.product-img');
        if(productImg) {
            productImg.style.opacity = '0';
            setTimeout(() => {
                productImg.style.transition = 'opacity 0.5s ease-in-out';
                productImg.style.opacity = '1';
            }, 200);
        }
        
        // Animation pour les informations du produit
        const productInfo = document.querySelector('.product-info');
        if(productInfo) {
            productInfo.style.opacity = '0';
            setTimeout(() => {
                productInfo.style.transition = 'opacity 0.7s ease-in-out';
                productInfo.style.opacity = '1';
            }, 400);
        }
    });
    
</script>
</body>
</html>