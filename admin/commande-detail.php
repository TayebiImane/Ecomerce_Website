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
        c.MONTANT_TOTAL,
        c.DATE_COMMANDE,
        u.ID_USER,
        u.PRENOM_USER,
        u.NOM_USER,
        u.EMAIL_USER,
        u.TELEPHONE_USER,
        sc.ID_STATU_COMMANDE,
        sc.LIBELLE_STATU_COMMANDES AS statut_commande,
        d.NOM_DEVISE AS devise_name,
        d.SYMBOLE AS devise_symbole,
        l.ID_LIRAISON,
        sl.ID_STATU_LIVRAISON,
        sl.LIBELLE_LIVRAISON AS statut_livraison,
        l.DATE_EXPEDITION,
        l.DATE_LIVRAISON_PREVUE,
        a.ID_ADRESSE,
        a.RUE,
        a.CODE_POSTAL,
        v.LINTITULLE_VILLE AS nom_ville,
        p.INTITULE_PAYS AS nom_pays
    FROM commande c
    JOIN utilisateur u ON c.ID_USER = u.ID_USER
    JOIN statu_commande sc ON c.ID_STATU_COMMANDE = sc.ID_STATU_COMMANDE
    JOIN devise d ON c.ID_DEVISE = d.ID_DEVISE
    JOIN livraison l ON c.ID_LIRAISON = l.ID_LIRAISON
    JOIN statu_livraison sl ON l.ID_STATU_LIVRAISON = sl.ID_STATU_LIVRAISON
    JOIN adresse a ON l.ID_ADRESSE = a.ID_ADRESSE
    JOIN ville v ON a.ID_VILLE = v.ID_VILLE
    JOIN pays p ON a.ID_PAYS = p.ID_PAYS
    WHERE c.ID_COMMANDE = ?
");
$statement->execute([$id]);
$commande = $statement->fetch(PDO::FETCH_ASSOC);

if(!$commande) {
    header('location: order.php');
    exit;
}

// Récupère les produits de la commande
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

// Récupère les informations de paiement
$statement = $pdo->prepare("
    SELECT 
        pa.ID_PAIEMENT,
        pa.MONTANT,
        mp.MODE_PAI,
        d.SYMBOLE AS devise_symbole
    FROM payer p
    JOIN paiement pa ON p.ID_PAIEMENT = pa.ID_PAIEMENT
    JOIN mode_paiement mp ON pa.ID_MODE_PAIEMENT = mp.ID_MODE_PAIEMENT
    JOIN devise d ON pa.ID_DEVISE = d.ID_DEVISE
    WHERE p.ID_COMMANDE = ?
");
$statement->execute([$id]);
$paiements = $statement->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="row">
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Informations de la Commande</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width:150px;">N° Commande</th>
                            <td><?php echo 'CMD-' . str_pad($commande['ID_COMMANDE'], 6, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td><?php echo date('d/m/Y', strtotime($commande['DATE_COMMANDE'])); ?></td>
                        </tr>
                        <tr>
                            <th>Montant Total</th>
                            <td><?php echo htmlspecialchars($commande['MONTANT_TOTAL']); ?> <?php echo htmlspecialchars($commande['devise_symbole']); ?></td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                <?php 
                                    $status_class = '';
                                    switch($commande['statut_commande']) {
                                        case 'En attente':
                                            $status_class = 'label-warning';
                                            break;
                                        case 'En cours de traitement':
                                            $status_class = 'label-info';
                                            break;
                                        case 'Validée':
                                            $status_class = 'label-success';
                                            break;
                                        case 'Expédiée':
                                            $status_class = 'label-primary';
                                            break;
                                        case 'Livrée':
                                            $status_class = 'label-success';
                                            break;
                                        case 'Annulé':
                                            $status_class = 'label-danger';
                                            break;
                                        case 'Remboursée':
                                            $status_class = 'label-default';
                                            break;
                                        default:
                                            $status_class = 'label-default';
                                    }
                                ?>
                                <span class="label <?php echo $status_class; ?>"><?php echo htmlspecialchars($commande['statut_commande']); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Devise</th>
                            <td><?php echo htmlspecialchars($commande['devise_name']); ?> (<?php echo htmlspecialchars($commande['devise_symbole']); ?>)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Informations Client</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width:150px;">Nom</th>
                            <td><?php echo htmlspecialchars($commande['PRENOM_USER'] . ' ' . $commande['NOM_USER']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($commande['EMAIL_USER']); ?></td>
                        </tr>
                        <tr>
                            <th>Téléphone</th>
                            <td><?php echo htmlspecialchars($commande['TELEPHONE_USER']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Informations de Livraison</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width:150px;">Statut Livraison</th>
                            <td>
                                <?php 
                                    $livraison_class = '';
                                    switch($commande['statut_livraison']) {
                                        case 'En attente de préparation':
                                            $livraison_class = 'label-warning';
                                            break;
                                        case 'En cours de préparation':
                                            $livraison_class = 'label-info';
                                            break;
                                        case 'Prête à être expédiée':
                                            $livraison_class = 'label-primary';
                                            break;
                                        case 'Expédiée':
                                            $livraison_class = 'label-primary';
                                            break;
                                        case 'En cours de livraison':
                                            $livraison_class = 'label-info';
                                            break;
                                        case 'Livrée':
                                            $livraison_class = 'label-success';
                                            break;
                                        case 'Livraison échouée':
                                            $livraison_class = 'label-danger';
                                            break;
                                        case 'Retournée à l expéditeur':
                                            $livraison_class = 'label-danger';
                                            break;
                                        case 'Annulée':
                                            $livraison_class = 'label-danger';
                                            break;
                                        case 'En attente de retrait':
                                            $livraison_class = 'label-warning';
                                            break;
                                        default:
                                            $livraison_class = 'label-default';
                                    }
                                ?>
                                <span class="label <?php echo $livraison_class; ?>"><?php echo htmlspecialchars($commande['statut_livraison']); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Date d'expédition</th>
                            <td><?php echo $commande['DATE_EXPEDITION'] ? date('d/m/Y', strtotime($commande['DATE_EXPEDITION'])) : 'Non définie'; ?></td>
                        </tr>
                        <tr>
                            <th>Date de livraison prévue</th>
                            <td><?php echo $commande['DATE_LIVRAISON_PREVUE'] ? date('d/m/Y', strtotime($commande['DATE_LIVRAISON_PREVUE'])) : 'Non définie'; ?></td>
                        </tr>
                        <tr>
                            <th>Adresse</th>
                            <td>
                                <?php echo htmlspecialchars($commande['RUE']); ?><br>
                                <?php echo htmlspecialchars($commande['CODE_POSTAL']); ?> <?php echo htmlspecialchars($commande['nom_ville']); ?><br>
                                <?php echo htmlspecialchars($commande['nom_pays']); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Informations de Paiement</h3>
                </div>
                <div class="box-body">
                    <?php if(empty($paiements)): ?>
                        <div class="alert alert-warning">Aucun paiement enregistré pour cette commande.</div>
                    <?php else: ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID Paiement</th>
                                    <th>Mode de paiement</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($paiements as $paiement): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($paiement['ID_PAIEMENT']); ?></td>
                                    <td><?php echo htmlspecialchars($paiement['MODE_PAI']); ?></td>
                                    <td><?php echo htmlspecialchars($paiement['MONTANT']); ?> <?php echo htmlspecialchars($paiement['devise_symbole']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Produits commandés</h3>
                </div>
                <div class="box-body">
                    <?php if(empty($produits)): ?>
                        <div class="alert alert-warning">Aucun produit dans cette commande.</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID Produit</th>
                                    <th>Nom du produit</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($produits as $produit): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($produit['ID_PRODUIT']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['NOM_PRODUIT']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['PRIX']); ?> <?php echo htmlspecialchars($produit['devise_symbole']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['QUANTITE_PRODUIT']); ?></td>
                                    <td><?php echo htmlspecialchars($produit['PRIX'] * $produit['QUANTITE_PRODUIT']); ?> <?php echo htmlspecialchars($produit['devise_symbole']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total</th>
                                    <th><?php echo htmlspecialchars($commande['MONTANT_TOTAL']); ?> <?php echo htmlspecialchars($commande['devise_symbole']); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Actions</h3>
                </div>
                <div class="box-body">
                    <a href="commande_edit.php?id=<?php echo $commande['ID_COMMANDE']; ?>" class="btn btn-primary">Modifier la commande</a>
                    <a href="#" class="btn btn-warning" onclick="window.print();">Imprimer la facture</a>
                    <a href="#" class="btn btn-danger" data-href="commande_delete.php?id=<?php echo $commande['ID_COMMANDE']; ?>" data-toggle="modal" data-target="#confirm-delete">Supprimer la commande</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal for delete confirmation -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Confirmation de suppression</h4>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette commande?</p>
                <p style="color:red;">Attention! Cette commande sera également supprimée des tables associées (livraison, paiement, etc.).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <a class="btn btn-danger btn-ok">Supprimer</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>