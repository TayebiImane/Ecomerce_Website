<?php
include 'includes/db1.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    $id_utilisateur = $_SESSION['user_id'];
} else {
    // Rediriger vers la page de connexion
    header("Location: login.php");
    exit;
}

// Récupérer les commandes de l'utilisateur
$sqlCommandes = "
    SELECT c.ID_COMMANDE, c.DATE_COMMANDE, c.MONTANT_TOTAL, d.SYMBOLE, 
           sc.LIBELLE_STATU_COMMANDES, sl. 	LIBELLE_LIVRAISON ,
           l.DATE_LIVRAISON_PREVUE, l.DATE_EXPEDITION
    FROM commande c
    JOIN devise d ON c.ID_DEVISE = d.ID_DEVISE
    JOIN statu_commande sc ON c.ID_STATU_COMMANDE = sc.ID_STATU_COMMANDE
    JOIN livraison l ON c.ID_LIRAISON = l.ID_LIRAISON
    JOIN statu_livraison sl ON l.ID_STATU_LIVRAISON = sl.ID_STATU_LIVRAISON
    WHERE c.ID_USER = ?
    ORDER BY c.DATE_COMMANDE DESC";

$stmtCommandes = $conn->prepare($sqlCommandes);
$stmtCommandes->execute([$id_utilisateur]);
$commandes = $stmtCommandes->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour récupérer les produits d'une commande
function getProduits($conn, $id_commande) {
    $sqlProduits = "
        SELECT p.ID_PRODUIT, p.NOM_PRODUIT, p.PRIX, cp.QUANTITE_PRODUIT, p.ID_DEVISE, d.SYMBOLE, i.FILE
        FROM commande_produit cp
        JOIN produit p ON cp.ID_PRODUIT = p.ID_PRODUIT
        JOIN devise d ON p.ID_DEVISE = d.ID_DEVISE
        LEFT JOIN image i ON p.ID_PRODUIT = i.ID_PRODUIT AND i.FILE = 1
        WHERE cp.ID_COMMANDE = ?
        GROUP BY p.ID_PRODUIT";
    
    $stmtProduits = $conn->prepare($sqlProduits);
    $stmtProduits->execute([$id_commande]);
    return $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
}

// Si on demande les détails d'une commande spécifique
$commandeDetails = null;
$produitsCommande = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_commande = $_GET['id'];
    
    // Vérifier que la commande appartient bien à l'utilisateur
    $sqlVerif = "SELECT ID_COMMANDE FROM commande WHERE ID_COMMANDE = ? AND ID_USER = ?";
    $stmtVerif = $conn->prepare($sqlVerif);
    $stmtVerif->execute([$id_commande, $id_utilisateur]);
    
    if ($stmtVerif->rowCount() > 0) {
        // Récupérer les détails de la commande
        $sqlDetails = "
            SELECT c.ID_COMMANDE, c.DATE_COMMANDE, c.MONTANT_TOTAL, d.SYMBOLE_DEVISE, 
                   sc.INTITULE_STATU_COMMANDE, sl.INTITULE_STATU_LIVRAISON,
                   l.DATE_LIVRAISON_PREVUE, l.DATE_EXPEDITION,
                   mp.INTITULE_MODE_PAIEMENT, pm.MONTANT as MONTANT_PAYE,
                   u.PRENOM_USER, u.NOM_USER, u.EMAIL_USER, u.TELEPHONE_USER,
                   a.RUE, a.CODE_POSTAL, v.LINTITULLE_VILLE, p.INTITULE_PAYS
            FROM commande c
            JOIN devise d ON c.ID_DEVISE = d.ID_DEVISE
            JOIN statu_commande sc ON c.ID_STATU_COMMANDE = sc.ID_STATU_COMMANDE
            JOIN livraison l ON c.ID_LIRAISON = l.ID_LIVRAISON
            JOIN statu_livraison sl ON l.ID_STATU_LIVRAISON = sl.ID_STATU_LIVRAISON
            JOIN payer py ON c.ID_COMMANDE = py.ID_COMMANDE
            JOIN paiement pm ON py.ID_PAIEMENT = pm.ID_PAIEMENT
            JOIN mode_paiement mp ON pm.ID_MODE_PAIEMENT = mp.ID_MODE_PAIEMENT
            JOIN utilisateur u ON c.ID_USER = u.ID_USER
            JOIN adresse a ON l.ID_ADRESSE = a.ID_ADRESSE
            JOIN ville v ON a.ID_VILLE = v.ID_VILLE
            JOIN pays p ON a.ID_PAYS = p.ID_PAYS
            WHERE c.ID_COMMANDE = ?";
        
        $stmtDetails = $conn->prepare($sqlDetails);
        $stmtDetails->execute([$id_commande]);
        $commandeDetails = $stmtDetails->fetch(PDO::FETCH_ASSOC);
        
        // Récupérer les produits de cette commande
        $produitsCommande = getProduits($conn, $id_commande);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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

        :root {
            --primary-color: #3a7bd5;
            --secondary-color: #00d2ff;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --border-radius: 10px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding-bottom: 50px;
        }
        
        .page-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin-bottom: 30px;
        }
        
        .orders-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .section-title {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .order-card {
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .order-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-body {
            padding: 15px;
        }
        
        .order-number {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .order-date {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .order-total {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--dark-color);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-details {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            color: white;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .no-orders i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }
        
        /* Styles pour la page détaillée */
        .order-detail-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .detail-section {
            margin-bottom: 30px;
        }
        
        .detail-header {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-right: 15px;
        }
        
        .product-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .product-price {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .product-quantity {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .product-details {
            flex: 1;
        }
        
        .order-timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            z-index: 1;
        }
        
        .timeline-item:after {
            content: '';
            position: absolute;
            left: -23px;
            top: 15px;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }
        
        .timeline-item:last-child:after {
            display: none;
        }
        
        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .timeline-title {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .btn-back {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: #e9ecef;
        }
        
        .shipping-info, .payment-info {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            width: 130px;
            color: var(--dark-color);
        }
        
        .info-value {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .product-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .product-image {
                margin-bottom: 10px;
            }
            
            .info-item {
                flex-direction: column;
            }
            
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
       <!-- Navbar principale -->
       <nav class="navbar navbar-expand-lg sticky-top"  style="height: 80px;">
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


    <div class="container" style="margin-top: 50px;">
        <div class="page-header text-center">
            <h2><i class="fas fa-shopping-bag me-2"></i>Mes Commandes</h2>
            <p class="mb-0">Historique et détails de vos commandes</p>
        </div>
        
        <?php if ($commandeDetails): ?>
        <!-- Affichage des détails d'une commande spécifique -->
        <div class="order-detail-container">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="section-title">
                            <i class="fas fa-receipt me-2"></i>Commande #<?= $commandeDetails['ID_COMMANDE'] ?>
                        </h3>
                        <a href="mes_commandes.php" class="btn btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                        </a>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="detail-section">
                        <h4 class="detail-header"><i class="fas fa-box-open me-2"></i>Produits commandés</h4>
                        <?php if ($produitsCommande && count($produitsCommande) > 0): ?>
                            <?php foreach ($produitsCommande as $produit): ?>
                                <div class="product-item">
                                    <?php if ($produit['URL_IMAGE']): ?>
                                        <img src="<?= htmlspecialchars($produit['URL_IMAGE']) ?>" alt="<?= htmlspecialchars($produit['NOM_PRODUIT']) ?>" class="product-image">
                                    <?php else: ?>
                                        <div class="product-image bg-light d-flex justify-content-center align-items-center">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="product-details">
                                        <div class="product-name"><?= htmlspecialchars($produit['NOM_PRODUIT']) ?></div>
                                        <div class="product-price"><?= number_format($produit['PRIX'], 2) ?> <?= htmlspecialchars($produit['SYMBOLE_DEVISE']) ?></div>
                                        <div class="product-quantity">Quantité: <?= $produit['QUANTITE_PRODUIT'] ?></div>
                                    </div>
                                    
                                    <div class="product-subtotal fw-bold">
                                        <?= number_format($produit['PRIX'] * $produit['QUANTITE_PRODUIT'], 2) ?> <?= htmlspecialchars($produit['SYMBOLE_DEVISE']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="mt-3 text-end">
                                <div class="order-total">
                                    Total: <?= number_format($commandeDetails['MONTANT_TOTAL'], 2) ?> <?= htmlspecialchars($commandeDetails['SYMBOLE_DEVISE']) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-box-open mb-3" style="font-size: 2rem;"></i>
                                <p>Aucun produit trouvé pour cette commande.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="detail-section">
                        <h4 class="detail-header"><i class="fas fa-truck me-2"></i>Statut de la commande</h4>
                        <div class="order-timeline">
                            <div class="timeline-item">
                                <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($commandeDetails['DATE_COMMANDE'])) ?></div>
                                <div class="timeline-title">Commande passée</div>
                                <div class="timeline-description">Votre commande a été reçue et confirmée.</div>
                            </div>
                            
                            <?php if ($commandeDetails['DATE_EXPEDITION']): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?= date('d/m/Y', strtotime($commandeDetails['DATE_EXPEDITION'])) ?></div>
                                <div class="timeline-title">Commande expédiée</div>
                                <div class="timeline-description">Votre commande a été expédiée.</div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="timeline-item">
                                <div class="timeline-date"><?= date('d/m/Y', strtotime($commandeDetails['DATE_LIVRAISON_PREVUE'])) ?></div>
                                <div class="timeline-title">Livraison prévue</div>
                                <div class="timeline-description">
                                    Statut actuel: 
                                    <span class="status-badge 
                                        <?php 
                                        switch ($commandeDetails['INTITULE_STATU_LIVRAISON']) {
                                            case 'En attente': echo 'status-pending'; break;
                                            case 'En préparation': echo 'status-processing'; break;
                                            case 'Expédiée': echo 'status-shipped'; break;
                                            case 'Livrée': echo 'status-delivered'; break;
                                            case 'Annulée': echo 'status-cancelled'; break;
                                            default: echo 'status-pending';
                                        }
                                        ?>">
                                        <?= htmlspecialchars($commandeDetails['INTITULE_STATU_LIVRAISON']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="shipping-info">
                        <h4 class="detail-header"><i class="fas fa-map-marker-alt me-2"></i>Adresse de livraison</h4>
                        
                        <div class="info-item">
                            <div class="info-label">Nom:</div>
                            <div class="info-value"><?= htmlspecialchars($commandeDetails['PRENOM_USER'] . ' ' . $commandeDetails['NOM_USER']) ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Adresse:</div>
                            <div class="info-value"><?= htmlspecialchars($commandeDetails['RUE']) ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Ville:</div>
                            <div class="info-value"><?= htmlspecialchars($commandeDetails['LINTITULLE_VILLE'] . ' ' . $commandeDetails['CODE_POSTAL']) ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Pays:</div>
                            <div class="info-value"><?= htmlspecialchars($commandeDetails['INTITULE_PAYS']) ?></div>
                        </div>
                    </div>
                    
                    <div class="payment-info">
                        <h4 class="detail-header"><i class="fas fa-credit-card me-2"></i>Informations de paiement</h4>
                        
                        <div class="info-item">
                            <div class="info-label">Méthode:</div>
                            <div class="info-value"><?= htmlspecialchars($commandeDetails['INTITULE_MODE_PAIEMENT']) ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Statut:</div>
                            <div class="info-value">
                                <span class="status-badge 
                                    <?php 
                                    switch ($commandeDetails['INTITULE_STATU_COMMANDE']) {
                                        case 'Payée': echo 'status-delivered'; break;
                                        case 'En attente': echo 'status-pending'; break;
                                        case 'Annulée': echo 'status-cancelled'; break;
                                        default: echo 'status-pending';
                                    }
                                    ?>">
                                    <?= htmlspecialchars($commandeDetails['INTITULE_STATU_COMMANDE']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Montant:</div>
                            <div class="info-value fw-bold"><?= number_format($commandeDetails['MONTANT_PAYE'], 2) ?> <?= htmlspecialchars($commandeDetails['SYMBOLE_DEVISE']) ?></div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary w-100">
                            <i class="fas fa-shopping-cart me-2"></i>Continuer vos achats
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Affichage de la liste des commandes -->
        <div class="orders-container">
            <h3 class="section-title"><i class="fas fa-history me-2"></i>Historique de commandes</h3>
            
            <?php if (count($commandes) > 0): ?>
                <div class="row">
                    <?php foreach ($commandes as $commande): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="order-number">Commande #<?= $commande['ID_COMMANDE'] ?></div>
                                            <div class="order-date">
                                                <i class="far fa-calendar me-1"></i>
                                                <?= date('d/m/Y', strtotime($commande['DATE_COMMANDE'])) ?>
                                            </div>
                                        </div>
                                        <span class="status-badge 
                                        <?php 
$libelle_livraison = isset($commande['LIBELLE_LIVRAISON']) ? trim($commande['LIBELLE_LIVRAISON']) : 'En attente';

switch ($libelle_livraison) {
    case 'En attente': $status_class = 'status-pending'; break;
    case 'En préparation': $status_class = 'status-processing'; break;
    case 'Expédiée': $status_class = 'status-shipped'; break;
    case 'Livrée': $status_class = 'status-delivered'; break;
    case 'Annulée': $status_class = 'status-cancelled'; break;
    default: $status_class = 'status-pending';
}
?>
<span class="<?= $status_class ?>">
    <?= htmlspecialchars($libelle_livraison) ?>
</span>

                                    </div>
                                </div>
                                <div class="order-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div><i class="fas fa-box me-2"></i>Statut: <?= htmlspecialchars($commande['LIBELLE_STATU_COMMANDES']) ?></div>
                                            <?php if ($commande['DATE_LIVRAISON_PREVUE']): ?>
                                                <div class="text-muted">
                                                    <i class="fas fa-truck me-2"></i>Livraison prévue le: <?= date('d/m/Y', strtotime($commande['DATE_LIVRAISON_PREVUE'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="order-total"><?= number_format($commande['MONTANT_TOTAL'], 2) ?> <?= htmlspecialchars($commande['SYMBOLE']) ?></div>
                                        <a href="mes_commandes.php?id=<?= $commande['ID_COMMANDE'] ?>" class="btn btn-details btn-sm">
                                            <i class="fas fa-eye me-1"></i>Détails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-bag d-block"></i>
                    <h4>Vous n'avez pas encore passé de commande</h4>
                    <p>Explorez notre catalogue et commencez vos achats dès maintenant!</p>
                    <a href="index.php" class="btn btn-primary mt-3">
                        <i class="fas fa-shopping-cart me-2"></i>Découvrir nos produits
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>