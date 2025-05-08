<?php require_once('header.php');
include '../includes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<section class="content-header">
	<div class="content-header-left">
		<h1>Liste des Commandes</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead class="thead-dark">
							<tr>
								<th width="10">#</th>
								<th width="120">N° Commande</th>
								<th width="120">Client</th>
								<th width="120">Date</th>
								<th width="100">Montant</th>
								<th width="120">Devise</th>
								<th width="120">Statut</th>
								<th width="120">Livraison</th>
								<th width="100">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							// Récupérer les commandes avec les données demandées
							$statement = $pdo->prepare("
								SELECT 
									c.ID_COMMANDE,
									c.MONTANT_TOTAL,
									c.DATE_COMMANDE,
									u.PRENOM_USER,
									u.NOM_USER,
									sc.LIBELLE_STATU_COMMANDES AS statut_commande,
									d.NOM_DEVISE AS devise_name,
									d.SYMBOLE AS devise_symbole,
									l.ID_LIRAISON,
									sl.LIBELLE_LIVRAISON AS statut_livraison
								FROM commande c
								JOIN utilisateur u ON c.ID_USER = u.ID_USER
								JOIN statu_commande sc ON c.ID_STATU_COMMANDE = sc.ID_STATU_COMMANDE
								JOIN devise d ON c.ID_DEVISE = d.ID_DEVISE
								JOIN livraison l ON c.ID_LIRAISON = l.ID_LIRAISON
								JOIN statu_livraison sl ON l.ID_STATU_LIVRAISON = sl.ID_STATU_LIVRAISON
								ORDER BY c.ID_COMMANDE DESC
							");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);

							foreach ($result as $row) {
								$i++;
							?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo 'CMD-' . str_pad($row['ID_COMMANDE'], 6, '0', STR_PAD_LEFT); ?></td>
									<td><?php echo htmlspecialchars($row['PRENOM_USER'] . ' ' . $row['NOM_USER']); ?></td>
									<td><?php echo date('d/m/Y', strtotime($row['DATE_COMMANDE'])); ?></td>
									<td><?php echo htmlspecialchars($row['MONTANT_TOTAL']); ?> <?php echo htmlspecialchars($row['devise_symbole']); ?></td>
									<td><?php echo htmlspecialchars($row['devise_name']); ?></td>
									<td>
										<?php 
											$status_class = '';
											switch($row['statut_commande']) {
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
										<span class="label <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['statut_commande']); ?></span>
									</td>
									<td>
										<?php 
											$livraison_class = '';
											switch($row['statut_livraison']) {
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
										<span class="label <?php echo $livraison_class; ?>"><?php echo htmlspecialchars($row['statut_livraison']); ?></span>
									</td>
									<td>
										<a href="commande-detail.php?id=<?php echo $row['ID_COMMANDE']; ?>" class="btn btn-info btn-xs">Détails</a>
										<a href="commande_edit.php?id=<?php echo $row['ID_COMMANDE'];?>" class="btn btn-primary btn-xs">Edit</a>
										
									</td>
								</tr>
							<?php
							}
							?>							
						</tbody>
					</table>
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