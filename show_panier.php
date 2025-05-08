<?php
include 'includes/db1.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $id_utilisateur = $_SESSION['user_id'];
} else {
    header("Location: login.php");
    exit;
}

// Récupération de l'ID du panier de l'utilisateur
$sqlPanier = "SELECT ID_PANIER FROM panier WHERE ID_USER = ?";
$stmtPanier = $conn->prepare($sqlPanier);
$stmtPanier->execute([$id_utilisateur]);

if ($stmtPanier->rowCount() > 0) {
    $panier = $stmtPanier->fetch();
    $id_panier = $panier['ID_PANIER'];
} else {
    $id_panier = null;
}

$produitsPanier = [];
$total_panier = 0;
$stock_invalide = false;

if ($id_panier) {
    // Récupération des produits du panier avec leur devise
    $sqlProduitsPanier = "
        SELECT p.ID_PRODUIT, p.NOM_PRODUIT, p.PRIX, p.QUANTITE_STOCK, pp.QUANTITE_PROUIT, i.FILE, d.SYMBOLE
        FROM panier_produit pp
        INNER JOIN produit p ON pp.ID_PRODUIT = p.ID_PRODUIT
        LEFT JOIN image i ON p.ID_PRODUIT = i.ID_PRODUIT
        LEFT JOIN devise d ON p.ID_DEVISE = d.ID_DEVISE
        WHERE pp.ID_PANIER = ?
    ";

    $stmtProduitsPanier = $conn->prepare($sqlProduitsPanier);
    $stmtProduitsPanier->execute([$id_panier]);

    $produitsPanier = $stmtProduitsPanier->fetchAll();

    foreach ($produitsPanier as &$produit) {
        if ($produit['QUANTITE_PROUIT'] > $produit['QUANTITE_STOCK']) {
            // Mettre à jour la quantité dans le panier pour correspondre au stock disponible
            $updateQuantite = "UPDATE panier_produit SET QUANTITE_PROUIT = ? WHERE ID_PANIER = ? AND ID_PRODUIT = ?";
            $stmtUpdate = $conn->prepare($updateQuantite);
            $stmtUpdate->execute([$produit['QUANTITE_STOCK'], $id_panier, $produit['ID_PRODUIT']]);

            // Mise à jour locale de la quantité pour l'affichage
           
            $stock_invalide = true;
        }

        // Calculer le total du panier
        $total_produit = $produit['PRIX'] * $produit['QUANTITE_PROUIT'];
        $total_panier += $total_produit;
        $produit['TOTAL_PRODUIT'] = $total_produit;

        // Si la quantité d'un produit est <= 0, désactiver le bouton
        if ($produit['QUANTITE_PROUIT'] <= 0) {
            $stock_invalide = true;
        }
      
    }

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Panier</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-bottom: 40px;
        }
        .cart-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .quantity-input {
            max-width: 80px;
        }
        .cart-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 4px solid #0d6efd;
        }
        .cart-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .product-name {
            font-weight: 500;
            color: #333;
        }
        .product-price {
            font-weight: 600;
            color: #0d6efd;
        }
        .empty-cart {
            text-align: center;
            padding: 40px 0;
        }
        .empty-cart i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .stock-warning {
            color: #dc3545;
            font-size: 0.9rem;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .checkout-options {
            margin-top: 20px;
        }
        .product-row {
            transition: all 0.2s ease;
        }
        .product-row:hover {
            background-color: #f8f9fa;
        }
        .selected-product {
            background-color: #e7f3ff;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="cart-container">
            <div class="cart-header">
                <h2><i class="bi bi-cart3"></i> Votre Panier</h2>
                <a href="index.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Continuer vos achats</a>
            </div>

            <?php if ($stock_invalide): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Certains produits de votre panier ont été ajustés en raison de la disponibilité du stock.
                </div>
            <?php endif; ?>

            <?php if (count($produitsPanier) > 0): ?>
                <form method="post" action="action_panier.php" id="cartForm">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" id="selectAll" class="form-check-input"> Sélectionner</th>
                                    <th>Produit</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produitsPanier as $produit): ?>
                                    <tr class="product-row">
                                        <td>
                                            <input type="checkbox" name="produits_selectionnes[<?php echo $produit['ID_PRODUIT']; ?>]" 
                                                value="1" class="form-check-input product-select"
                                                <?php echo $produit['QUANTITE_PROUIT'] <= 0 ? 'disabled' : ''; ?>>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php 
                                                if (!empty($produit['FILE'])) {
                                                    $image_data = base64_encode($produit['FILE']);
                                                    $image_type = 'image/jpeg'; // À adapter si nécessaire
                                                    echo '<img src="data:' . $image_type . ';base64,' . $image_data . '" alt="' . htmlspecialchars($produit['NOM_PRODUIT']) . '" class="product-image me-3">';
                                                } else {
                                                    echo '<img src="../storage/default.jpg" alt="default image" class="product-image me-3">';
                                                }
                                                ?>
                                                <span class="product-name"><?php echo htmlspecialchars($produit['NOM_PRODUIT']); ?></span>
                                            </div>
                                        </td>
                                        <td class="product-price">
                                            <?php 
                                            if (isset($produit['SYMBOLE']) && !empty($produit['SYMBOLE'])) {
                                                echo $produit['PRIX'] . ' ' . htmlspecialchars($produit['SYMBOLE']);
                                            } else {
                                                echo $produit['PRIX'] . ' €';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <input type="number" name="quantite[<?php echo $produit['ID_PRODUIT']; ?>]" 
                                                value="<?php echo $produit['QUANTITE_PROUIT']; ?>" 
                                                min="1" max="<?php echo $produit['QUANTITE_STOCK']; ?>" 
                                                class="form-control quantity-input">
                                            
                                            <?php if ($produit['QUANTITE_STOCK'] < 10): ?>
                                                <small class="stock-warning">
                                                    <i class="bi bi-exclamation-circle"></i> 
                                                    Stock: <?php echo $produit['QUANTITE_STOCK']; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="product-price">
                                            <?php 
                                            $total_produit = $produit['PRIX'] * $produit['QUANTITE_PROUIT'];
                                            if (isset($produit['SYMBOLE']) && !empty($produit['SYMBOLE'])) {
                                                echo $total_produit . ' ' . htmlspecialchars($produit['SYMBOLE']);
                                            } else {
                                                echo $total_produit . ' €';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-btn" 
                                                data-product-id="<?php echo $produit['ID_PRODUIT']; ?>">
                                                <i class="bi bi-trash"></i> Retirer
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" name="action" value="mettre_a_jour" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Mettre à jour le panier
                        </button>
                        <button type="button" id="clear-cart" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Vider le panier
                        </button>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="cart-summary">
                                <h4>Résumé de la commande</h4>
                                <div class="d-flex justify-content-between my-3">
                                    <span>Sous-total</span>
                                    <span class="cart-total">
                                        <?php echo $total_panier; ?> 
                                        <?php echo isset($produitsPanier[0]['SYMBOLE']) ? htmlspecialchars($produitsPanier[0]['SYMBOLE']) : '€'; ?>
                                    </span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>Total</span>
                                    <span class="cart-total">
                                        <?php echo $total_panier; ?> 
                                        <?php echo isset($produitsPanier[0]['SYMBOLE']) ? htmlspecialchars($produitsPanier[0]['SYMBOLE']) : '€'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checkout-options">
                                <div class="d-grid gap-2">
                                    <button type="button" id="checkout-selected" class="btn btn-primary btn-lg">
                                        <i class="bi bi-credit-card"></i> Payer les articles sélectionnés
                                    </button>
                                    <button type="button" id="checkout-all" class="btn btn-outline-primary">
                                        <i class="bi bi-credit-card"></i> Payer tout le panier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Formulaires cachés pour les actions -->
                <form id="removeForm" method="post" action="action_panier.php" style="display:none;">
                    <input type="hidden" name="action" value="retirer">
                    <input type="hidden" name="product_id" id="remove_product_id" value="">
                </form>
                
                <form id="checkoutForm" method="post" action="payment.php" style="display:none;">
                    <!-- sera rempli dynamiquement par JavaScript -->
                </form>
                
                <form id="clearCartForm" method="post" action="action_panier.php" style="display:none;">
                    <input type="hidden" name="action" value="vider">
                </form>

            <?php else: ?>
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <h3>Votre panier est vide</h3>
                    <p class="text-muted">Vous n'avez pas encore ajouté d'articles à votre panier.</p>
                    <a href="index.php" class="btn btn-primary mt-3">Commencer vos achats</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript pour les interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gérer la sélection de tous les produits
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.product-select:not(:disabled)');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                        updateRowStyle(checkbox);
                    });
                });
            }
            
            // Gérer le style des lignes sélectionnées
            const productCheckboxes = document.querySelectorAll('.product-select');
            productCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateRowStyle(checkbox);
                });
            });
            
            // Mettre à jour le style des lignes
            function updateRowStyle(checkbox) {
                const row = checkbox.closest('tr');
                if (checkbox.checked) {
                    row.classList.add('selected-product');
                } else {
                    row.classList.remove('selected-product');
                }
            }
            
            // Boutons de suppression
            const removeButtons = document.querySelectorAll('.remove-btn');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    document.getElementById('remove_product_id').value = productId;
                    document.getElementById('removeForm').submit();
                });
            });
            
            // Bouton pour vider le panier
            const clearCartButton = document.getElementById('clear-cart');
            if (clearCartButton) {
                clearCartButton.addEventListener('click', function() {
                    if (confirm('Êtes-vous sûr de vouloir vider votre panier?')) {
                        document.getElementById('clearCartForm').submit();
                    }
                });
            }
            
            // Bouton pour payer les articles sélectionnés
            const checkoutSelectedButton = document.getElementById('checkout-selected');
            if (checkoutSelectedButton) {
                checkoutSelectedButton.addEventListener('click', function() {
                    const selectedCheckboxes = document.querySelectorAll('.product-select:checked');
                    if (selectedCheckboxes.length === 0) {
                        alert('Veuillez sélectionner au moins un produit');
                        return;
                    }
                    
                    const checkoutForm = document.getElementById('checkoutForm');
                    checkoutForm.innerHTML = '';
                    
                    selectedCheckboxes.forEach(checkbox => {
                        const productId = checkbox.name.match(/\[(\d+)\]/)[1];
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `produits_selectionnes[${productId}]`;
                        input.value = '1';
                        checkoutForm.appendChild(input);
                    });
                    
                    checkoutForm.submit();
                });
            }
            
            // Bouton pour payer tout le panier
            const checkoutAllButton = document.getElementById('checkout-all');
            if (checkoutAllButton) {
                checkoutAllButton.addEventListener('click', function() {
                    const checkoutForm = document.getElementById('checkoutForm');
                    checkoutForm.innerHTML = '';
                    
                    const allCheckboxes = document.querySelectorAll('.product-select:not(:disabled)');
                    allCheckboxes.forEach(checkbox => {
                        const productId = checkbox.name.match(/\[(\d+)\]/)[1];
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `produits_selectionnes[${productId}]`;
                        input.value = '1';
                        checkoutForm.appendChild(input);
                    });
                    
                    checkoutForm.submit();
                });
            }
        });
    </script>
</body>
</html>