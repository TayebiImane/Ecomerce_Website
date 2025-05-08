<?php require_once('header.php');
include '../includes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifie si l'ID de la commande est fourni dans l'URL
if(!isset($_REQUEST['id'])) {
    header('location: order.php');
    exit;
}
$id = $_REQUEST['id'];

// Récupère les détails de la commande
$statement = $pdo->prepare("
    SELECT
        c.ID_COMMANDE,
        c.ID_USER,
        c.ID_STATU_COMMANDE,
        c.ID_LIRAISON,
        c.ID_DEVISE,
        c.MONTANT_TOTAL,
        c.DATE_COMMANDE,
        u.PRENOM_USER,
        u.NOM_USER,
        u.EMAIL_USER,
        d.NOM_DEVISE,
        d.SYMBOLE,
        sc.LIBELLE_STATU_COMMANDES
    FROM commande c
    JOIN utilisateur u ON c.ID_USER = u.ID_USER
    JOIN devise d ON c.ID_DEVISE = d.ID_DEVISE
    JOIN statu_commande sc ON c.ID_STATU_COMMANDE = sc.ID_STATU_COMMANDE
    WHERE c.ID_COMMANDE = ?
");
$statement->execute([$id]);
$commande = $statement->fetch(PDO::FETCH_ASSOC);

if(!$commande) {
    header('location: order.php');
    exit;
}

// Récupère les informations de livraison
$statement = $pdo->prepare("
    SELECT
        l.*,
        sl.LIBELLE_LIVRAISON
    FROM livraison l
    JOIN statu_livraison sl ON l.ID_STATU_LIVRAISON = sl.ID_STATU_LIVRAISON
    WHERE l.ID_LIRAISON = ?
");
$statement->execute([$commande['ID_LIRAISON']]);
$livraison = $statement->fetch(PDO::FETCH_ASSOC);

// Récupère les détails des produits de la commande
$statement = $pdo->prepare("
SELECT 
    cp.ID_PRODUIT,
    cp.QUANTITE_PRODUIT,
    p.NOM_PRODUIT,
    p.PRIX,
    d.SYMBOLE AS devise_symbole
FROM commande_produit cp
JOIN produit p ON cp.ID_PRODUIT = p.ID_PRODUIT
JOIN devise d ON p.ID_DEVISE = d.ID_DEVISE
WHERE cp.ID_COMMANDE = ?
");
$statement->execute([$id]);
$produits = $statement->fetchAll(PDO::FETCH_ASSOC);

// En cas de soumission du formulaire
if(isset($_POST['form1'])) {
    $valid = 1;
    
    if($valid == 1) {
        // Mise à jour des données de la commande
        $statement = $pdo->prepare("
            UPDATE commande SET
                ID_USER = ?,
                ID_STATU_COMMANDE = ?,
                ID_DEVISE = ?,
                MONTANT_TOTAL = ?,
                DATE_COMMANDE = ?
            WHERE ID_COMMANDE = ?
        ");
        $statement->execute([
            $_POST['user_id'],
            $_POST['statut_commande'],
            $_POST['devise'],
            $_POST['montant_total'],
            $_POST['date_commande'],
            $id
        ]);
        
        // Mise à jour des informations de livraison
        $statement = $pdo->prepare("
            UPDATE livraison SET
                ID_STATU_LIVRAISON = ?,
                DATE_EXPEDITION = ?,
                DATE_LIVRAISON_PREVUE = ?
            WHERE ID_LIRAISON = ?
        ");
        $statement->execute([
            $_POST['statut_livraison'],
            $_POST['date_expedition'] ? $_POST['date_expedition'] : null,
            $_POST['date_livraison'] ? $_POST['date_livraison'] : null,
            $commande['ID_LIRAISON']
        ]);
        
        // Redirection vers la page de détails avec un message de succès
        $_SESSION['success_message'] = 'La commande a été mise à jour avec succès.';
        header('location: commande-detail.php?id='.$id);
        exit;
    }
}

// Récupère les utilisateurs pour le select
$statement = $pdo->prepare("SELECT ID_USER, PRENOM_USER, NOM_USER FROM utilisateur ORDER BY NOM_USER ASC");
$statement->execute();
$users = $statement->fetchAll(PDO::FETCH_ASSOC);

// Récupère les statuts de commande pour le select
$statement = $pdo->prepare("SELECT ID_STATU_COMMANDE, LIBELLE_STATU_COMMANDES FROM statu_commande ORDER BY ID_STATU_COMMANDE ASC");
$statement->execute();
$statuts_commande = $statement->fetchAll(PDO::FETCH_ASSOC);

// Récupère les devises pour le select
$statement = $pdo->prepare("SELECT ID_DEVISE, NOM_DEVISE, SYMBOLE FROM devise ORDER BY NOM_DEVISE ASC");
$statement->execute();
$devises = $statement->fetchAll(PDO::FETCH_ASSOC);

// Récupère les statuts de livraison pour le select
$statement = $pdo->prepare("SELECT ID_STATU_LIVRAISON, LIBELLE_LIVRAISON FROM statu_livraison ORDER BY ID_STATU_LIVRAISON ASC");
$statement->execute();
$statuts_livraison = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Détails de la Commande #<?php echo 'CMD-' . str_pad($commande['ID_COMMANDE'], 6, '0', STR_PAD_LEFT); ?></h1>
    </div>
    <div class="content-header-right">
        <a href="order.php" class="btn btn-primary btn-sm">Retour à la liste</a>
    </div>
</section>

<section class="content">
    <?php if(isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <p><?php echo $_SESSION['success_message']; ?></p>
    </div>
    <?php unset($_SESSION['success_message']); endif; ?>

    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">Informations de la Commande</h3>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">N° Commande</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo 'CMD-' . str_pad($commande['ID_COMMANDE'], 6, '0', STR_PAD_LEFT); ?>" disabled>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Date de Commande</label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control" name="date_commande" value="<?php echo date('Y-m-d', strtotime($commande['DATE_COMMANDE'])); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Client</label>
                                    <div class="col-sm-9">
                                        <select name="user_id" class="form-control select2">
                                            <?php foreach($users as $user): ?>
                                            <option value="<?php echo $user['ID_USER']; ?>" <?php if($user['ID_USER'] == $commande['ID_USER']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($user['PRENOM_USER'] . ' ' . $user['NOM_USER']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Montant Total</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control" name="montant_total" value="<?php echo $commande['MONTANT_TOTAL']; ?>">
                                            <span class="input-group-addon"><?php echo $commande['SYMBOLE']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Devise</label>
                                    <div class="col-sm-9">
                                        <select name="devise" class="form-control select2">
                                            <?php foreach($devises as $devise): ?>
                                            <option value="<?php echo $devise['ID_DEVISE']; ?>" <?php if($devise['ID_DEVISE'] == $commande['ID_DEVISE']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($devise['NOM_DEVISE'] . ' (' . $devise['SYMBOLE'] . ')'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Statut de la Commande</label>
                                    <div class="col-sm-9">
                                        <select name="statut_commande" class="form-control select2">
                                            <?php foreach($statuts_commande as $statut): ?>
                                            <option value="<?php echo $statut['ID_STATU_COMMANDE']; ?>" <?php if($statut['ID_STATU_COMMANDE'] == $commande['ID_STATU_COMMANDE']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($statut['LIBELLE_STATU_COMMANDES']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">Informations de Livraison</h3>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Statut de Livraison</label>
                                    <div class="col-sm-9">
                                        <select name="statut_livraison" class="form-control select2">
                                            <?php foreach($statuts_livraison as $statut): ?>
                                            <option value="<?php echo $statut['ID_STATU_LIVRAISON']; ?>" <?php if($statut['ID_STATU_LIVRAISON'] == $livraison['ID_STATU_LIVRAISON']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($statut['LIBELLE_LIVRAISON']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Date d'Expédition</label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control" name="date_expedition" 
                                            value="<?php echo !empty($livraison['DATE_EXPEDITION']) ? date('Y-m-d', strtotime($livraison['DATE_EXPEDITION'])) : ''; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label">Date de Livraison Prévue</label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control" name="date_livraison" 
                                            value="<?php echo !empty($livraison['DATE_LIVRAISON_PREVUE']) ? date('Y-m-d', strtotime($livraison['DATE_LIVRAISON_PREVUE'])) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                                                
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">Produits Commandés</h3>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Produit</th>
                                            <th>Prix Unitaire</th>
                                            <th>Quantité</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($produits)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Aucun produit dans cette commande</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach($produits as $produit): ?>
                                            <tr>
                                            <tr>
                                    <td><?php echo htmlspecialchars($produit['ID_PRODUIT']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['NOM_PRODUIT']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['PRIX']); ?> <?php echo htmlspecialchars($produit['devise_symbole']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['QUANTITE_PRODUIT']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['PRIX'] * $produit['QUANTITE_PRODUIT']); ?> <?php echo htmlspecialchars($produit['devise_symbole']); ?></td>
                                </tr>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Total:</th>
                                            <th><?php echo $commande['MONTANT_TOTAL']; ?> <?php echo $commande['SYMBOLE']; ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success" name="form1">Mettre à jour</button>
                                <a href="order.php" class="btn btn-danger">Annuler</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Initialiser Select2 pour une meilleure expérience utilisateur
    $('.select2').select2();
});
</script>

<?php require_once('footer.php'); ?>