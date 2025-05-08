<?php
include 'includes/db1.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (isset($_SESSION['user_id'])) {
    $id_utilisateur = $_SESSION['user_id'];
} else {
    echo "<div class='alert alert-danger'>Veuillez vous connecter pour voir votre panier.</div>";
    exit;
}

if (isset($_POST['produits_selectionnes']) && is_array($_POST['produits_selectionnes'])) {
    $produits_selectionnes = $_POST['produits_selectionnes'];
    $montant_total = 0;
  
    // Récupérer les informations des produits sélectionnés
    foreach ($produits_selectionnes as $id_produit => $value) {
        // Vérifier si l'ID produit est bien dans le panier de l'utilisateur
        $sqlProduit = "SELECT p.ID_PRODUIT, p.NOM_PRODUIT, p.PRIX, pp.QUANTITE_PROUIT, p.ID_DEVISE
                       FROM panier_produit pp
                       JOIN produit p ON pp.ID_PRODUIT = p.ID_PRODUIT
                       WHERE pp.ID_PRODUIT = ? AND pp.ID_PANIER = (SELECT ID_PANIER FROM panier WHERE ID_USER = ?)";
        $stmtProduit = $conn->prepare($sqlProduit);
        $stmtProduit->execute([$id_produit, $id_utilisateur]);
        $produit = $stmtProduit->fetch();

        if ($produit) {
            // Calculer le montant total
            $id_devise = $produit['ID_DEVISE'];
            $montant_total += $produit['PRIX'] * $produit['QUANTITE_PROUIT'];
        }
    }
    // Récupérer le symbole de la devise du premier produit

    if (isset($_POST['confirmer_commande'])) {
        // Récupérer l'adresse de l'utilisateur
        $sqlAdresseId = "SELECT ID_ADRESSE FROM adresse WHERE ID_USER = ?";
        $stmtAdresseId = $conn->prepare($sqlAdresseId);
        $stmtAdresseId->execute([$id_utilisateur]);
        $adresseRow = $stmtAdresseId->fetch();
        $id_adresse = $adresseRow['ID_ADRESSE'] ?? null;

        if ($id_adresse) {
            // 1. LIVRAISON
            $sqlLivraison = "INSERT INTO livraison (ID_ADRESSE, ID_STATU_LIVRAISON, DATE_EXPEDITION, DATE_LIVRAISON_PREVUE)
                             VALUES (?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY))";
            $stmtLivraison = $conn->prepare($sqlLivraison);
            $stmtLivraison->execute([$id_adresse]);
            $id_livraison = $conn->lastInsertId();

            // 2. COMMANDE
            $sqlCommande = "INSERT INTO commande (ID_USER, ID_STATU_COMMANDE, ID_LIRAISON , ID_DEVISE, MONTANT_TOTAL, DATE_COMMANDE) 
                            VALUES (?, 1, ?, ?, ?, NOW())";
            $stmtCommande = $conn->prepare($sqlCommande);
            $stmtCommande->execute([$id_utilisateur, $id_livraison, $id_devise, $montant_total]);
            $id_commande = $conn->lastInsertId();

            // 3. COMMANDE_PRODUIT
            foreach ($produits_selectionnes as $id_produit => $quantite) {
                $id_produit = $produit['ID_PRODUIT'];
                $quantite = $produit['QUANTITE_PROUIT'];

                // Insérer chaque produit et sa quantité dans la table commande_produit
                $sqlCommandeProduit = "INSERT INTO commande_produit (ID_COMMANDE, ID_PRODUIT, QUANTITE_PRODUIT) 
                                       VALUES (?, ?, ?)";
                $stmtCommandeProduit = $conn->prepare($sqlCommandeProduit);
                $stmtCommandeProduit->execute([$id_commande, $id_produit, $quantite]);
            
                if ($quantite > 0) {
                    $sqlUpdateQuantite = "UPDATE produit 
                                        SET QUANTITE_STOCK = QUANTITE_STOCK - ? 
                                        WHERE ID_PRODUIT = ?";
                    $stmtUpdateQuantite = $conn->prepare($sqlUpdateQuantite);
                    $stmtUpdateQuantite->execute([$quantite, $id_produit]);
                } else {
                    $sqlUpdateQuantite = "UPDATE produit 
                                        SET QUANTITE_STOCK = 0 
                                        WHERE ID_PRODUIT = ?";
                    $stmtUpdateQuantite = $conn->prepare($sqlUpdateQuantite);
                    $stmtUpdateQuantite->execute([$id_produit]);
                    $sqlUpdateStatut = "UPDATE produit 
                    SET ID_STATU_PRODUIT = 3 
                    WHERE ID_PRODUIT = ? AND ID_STATU_PRODUIT != 3";
$stmtUpdateStatut = $conn->prepare($sqlUpdateStatut);
$stmtUpdateStatut->execute([$id_produit]);
                }
            }

            // 4. PAIEMENT
            $sqlPaiement = "INSERT INTO paiement (ID_MODE_PAIEMENT, ID_DEVISE, MONTANT) 
                            VALUES (1, ?, ?)";
            $stmtPaiement = $conn->prepare($sqlPaiement);
            $stmtPaiement->execute([$id_devise, $montant_total]);
            $id_paiement = $conn->lastInsertId();

            // 6. TABLE PAYER
            $sqlPayer = "INSERT INTO payer (ID_COMMANDE, ID_PAIEMENT) VALUES (?, ?)";
            $stmtPayer = $conn->prepare($sqlPayer);
            $stmtPayer->execute([$id_commande, $id_paiement]);
        
            $sqlViderPanier = "DELETE FROM panier_produit WHERE ID_PANIER IN (SELECT ID_PANIER FROM panier WHERE ID_USER = ?)";
        $stmtViderPanier = $conn->prepare($sqlViderPanier);
        $stmtViderPanier->execute([$id_utilisateur]);
            // Afficher un message de confirmation avec style amélioré
            ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Commande confirmée</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <style>
                    :root {
                        --primary-color: #3a7bd5;
                        --secondary-color: #00d2ff;
                        --success-color: #28a745;
                        --border-radius: 10px;
                        --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                    }
                    
                    body {
                        font-family: 'Poppins', sans-serif;
                        background-color: #f8f9fa;
                        padding: 30px 0;
                    }
                    
                    .success-container {
                        background: white;
                        border-radius: var(--border-radius);
                        box-shadow: var(--box-shadow);
                        padding: 30px;
                        text-align: center;
                        max-width: 600px;
                        margin: 0 auto;
                    }
                    
                    .success-icon {
                        width: 80px;
                        height: 80px;
                        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
                        color: white;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 20px;
                        font-size: 40px;
                    }
                    
                    .order-number {
                        font-size: 22px;
                        font-weight: 600;
                        color: var(--primary-color);
                        margin: 20px 0;
                    }
                    
                    .btn-view-order {
                        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
                        border: none;
                        padding: 12px 30px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        margin-top: 20px;
                    }
                    
                    .btn-view-order:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="success-container">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2>Commande confirmée avec succès!</h2>
                        <p class="order-number">Numéro de commande: <?= $id_commande ?></p>
                        <p>Nous avons bien reçu votre commande et nous la préparons.</p>
                        <p>Un email de confirmation a été envoyé à votre adresse.</p>
                        <a href="page_de_recapitulatif.php" class="btn btn-view-order">
                            <i class="fas fa-eye me-2"></i>Voir ma commande
                        </a>
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
                
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            <?php
        } else {
            echo "<div class='alert alert-danger'>Adresse non trouvée.</div>";
        }
    } else {
        // Infos utilisateur
        $sqlUser = "SELECT PRENOM_USER, NOM_USER, EMAIL_USER, TELEPHONE_USER FROM utilisateur WHERE ID_USER = ?";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->execute([$id_utilisateur]);
        $utilisateur = $stmtUser->fetch();

        // Infos adresse complète
        $sqlAdresse = "
            SELECT A.RUE, A.CODE_POSTAL, V.LINTITULLE_VILLE, P.INTITULE_PAYS
            FROM adresse A
            JOIN ville V ON A.ID_VILLE = V.ID_VILLE
            JOIN pays P ON A.ID_PAYS = P.ID_PAYS
            WHERE A.ID_USER = ?
        ";
        $stmtAdresse = $conn->prepare($sqlAdresse);
        $stmtAdresse->execute([$id_utilisateur]);
        $adresse = $stmtAdresse->fetch();

        $date_livraison = date('Y-m-d', strtotime('+3 days'));
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmation de la commande</title>
            <!-- Bootstrap CSS -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <!-- Font Awesome pour les icônes -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <!-- Google Fonts -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --primary-color: #3a7bd5;
                    --secondary-color: #00d2ff;
                    --dark-color: #333;
                    --light-color: #f8f9fa;
                    --success-color: #28a745;
                    --border-radius: 10px;
                    --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                }
                
                body {
                    font-family: 'Poppins', sans-serif;
                    background-color: #f8f9fa;
                    color: #333;
                    padding-bottom: 50px;
                }
                
                .order-container {
                    background: white;
                    border-radius: var(--border-radius);
                    box-shadow: var(--box-shadow);
                    padding: 30px;
                    margin-top: 30px;
                    margin-bottom: 30px;
                }
                
                .page-header {
                    background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
                    color: white;
                    padding: 20px 0;
                    border-radius: var(--border-radius) var(--border-radius) 0 0;
                    margin-bottom: 30px;
                }
                
                .section-title {
                    border-bottom: 2px solid var(--primary-color);
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                    color: var(--primary-color);
                }
                
                .customer-info, .shipping-info, .order-summary {
                    margin-bottom: 30px;
                    padding: 20px;
                    background-color: rgba(248, 249, 250, 0.7);
                    border-radius: var(--border-radius);
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
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
                
                .total-price {
                    font-size: 24px;
                    font-weight: 700;
                    color: var(--primary-color);
                }
                
                .btn-confirm {
                    background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
                    border: none;
                    padding: 12px 30px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }
                
                .btn-confirm:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                }
                
                .icon-container {
                    display: flex;
                    align-items: center;
                    margin-bottom: 5px;
                }
                
                .icon-container i {
                    margin-right: 10px;
                    color: var(--primary-color);
                }
                
                .delivery-badge {
                    background: linear-gradient(to right, #28a745, #20c997);
                    color: white;
                    padding: 8px 15px;
                    border-radius: 20px;
                    font-weight: 500;
                    display: inline-block;
                    margin-top: 10px;
                }
                
                @media (max-width: 768px) {
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
            <div class="container">
                <div class="page-header text-center">
                    <h2><i class="fas fa-check-circle me-2"></i>Confirmation de la commande</h2>
                    <p class="mb-0">Merci pour votre achat!</p>
                </div>
                
                <div class="order-container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="customer-info">
                                <h4 class="section-title"><i class="fas fa-user me-2"></i>Informations personnelles</h4>
                                
                                <div class="info-item">
                                    <div class="info-label">Prénom:</div>
                                    <div class="info-value"><?= htmlspecialchars($utilisateur['PRENOM_USER']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Nom:</div>
                                    <div class="info-value"><?= htmlspecialchars($utilisateur['NOM_USER']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Email:</div>
                                    <div class="info-value">
                                        <div class="icon-container">
                                            <i class="fas fa-envelope"></i>
                                            <?= htmlspecialchars($utilisateur['EMAIL_USER']) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Téléphone:</div>
                                    <div class="info-value">
                                        <div class="icon-container">
                                            <i class="fas fa-phone"></i>
                                            <?= htmlspecialchars($utilisateur['TELEPHONE_USER']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="shipping-info">
                                <h4 class="section-title"><i class="fas fa-shipping-fast me-2"></i>Informations de livraison</h4>
                                
                                <div class="info-item">
                                    <div class="info-label">Adresse:</div>
                                    <div class="info-value">
                                        <div class="icon-container">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($adresse['RUE']) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Ville:</div>
                                    <div class="info-value"><?= htmlspecialchars($adresse['LINTITULLE_VILLE']) ?> <?= htmlspecialchars($adresse['CODE_POSTAL']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Pays:</div>
                                    <div class="info-value"><?= htmlspecialchars($adresse['INTITULE_PAYS']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Livraison prévue:</div>
                                    <div class="info-value">
                                        <div class="icon-container">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= $date_livraison ?>
                                        </div>
                                        <span class="delivery-badge">
                                            <i class="fas fa-truck me-1"></i>Livraison en 3 jours
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="order-summary">
                                <h4 class="section-title"><i class="fas fa-shopping-cart me-2"></i>Résumé de la commande</h4>
                                
                                <div class="info-item">
                                    <div class="info-label">Total:</div>
                                    <div class="info-value total-price"><?= number_format($montant_total, 2) ?> MAD</div>
                                </div>
                                
                                <div class="mt-4">
                                    <form method="post" class="text-center">
                                        <?php foreach ($produits_selectionnes as $id_produit => $quantite): ?>
                                            <input type="hidden" name="produits_selectionnes[<?= $id_produit ?>]" value="<?= $quantite ?>">
                                        <?php endforeach; ?>
                                        <input type="hidden" name="confirmer_commande" value="1">
                                        <button type="submit" class="btn btn-confirm btn-lg w-100">
                                            <i class="fas fa-check-circle me-2"></i>Confirmer la commande
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="d-flex justify-content-center">
                                        <i class="fab fa-cc-visa fa-2x mx-2 text-primary"></i>
                                        <i class="fab fa-cc-mastercard fa-2x mx-2 text-primary"></i>
                                        <i class="fab fa-cc-paypal fa-2x mx-2 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Retour au panier
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bootstrap JS Bundle with Popper -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
} else {
    echo '<div class="container mt-5">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Aucun produit sélectionné pour la commande.
            </div>
            <a href="javascript:history.back()" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Retour au panier
            </a>
          </div>';
}
?>