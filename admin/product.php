<?php require_once('header.php');
include '../includes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<section class="content-header">
	<div class="content-header-left">
		<h1>View Products</h1>
	</div>
	<div class="content-header-right">
		<a href="product-add.php" class="btn btn-primary btn-sm">Add Product</a>
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
								<th>Photo</th>
								<th width="160">Nom du produit</th>
								<th width="60">Prix</th>
								<th width="60">Quantité</th>
								<th>Marque</th>
								<th>Catégorie</th>
								<th>Statut</th>
								<th>Devise</th>
								<th width="80">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							// Récupérer les produits avec les données demandées
							$statement = $pdo->prepare("
								SELECT 
									p.ID_PRODUIT,
									p.NOM_PRODUIT,
									p.DESCRIPTION_PRODUIT,
									p.PRIX,
									p.QUANTITE_STOCK,
									p.CODE_PRODUIT,
									p.MARQUE,
									p.SEUIL_ALERTE,
									c.NOM_CATEGORIE AS category_name,
									s.LIBELLE_STAT_PROD AS statut_name,
									d.NOM_DEVISE  AS devise_name,
									I.FILE 
								FROM produit p
								JOIN categorie c ON p.ID_CATEGORIE = c.ID_CATEGORIE
								JOIN statut_produit s ON p.ID_STATU_PRODUIT = s.ID_STATU_PRODUIT
								JOIN devise d ON p.ID_DEVISE = d.ID_DEVISE
								JOIN image I ON p.ID_PRODUIT = I.ID_PRODUIT
								ORDER BY p.ID_PRODUIT DESC
							");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);

							foreach ($result as $row) {
								// Récupérer toutes les images associées à ce produit
								$image_statement = $pdo->prepare("SELECT FILE_NAME FROM image WHERE ID_PRODUIT = ?");
								$image_statement->execute([$row['ID_PRODUIT']]);
								$images = $image_statement->fetchAll(PDO::FETCH_ASSOC);

								$i++;
							?>
								<tr>
									<td><?php echo $i; ?></td>
									<td style="width:82px;">
									<?php 
            // Check if the FILE column contains data
            if (!empty($row['FILE'])) {
                // Convert the binary data in FILE to base64 for display
                $image_data = base64_encode($row['FILE']);
                $image_type = 'image/jpeg'; // or image/png depending on your data type
                
                // Display the image
                echo '<img src="data:' . $image_type . ';base64,' . $image_data . '" alt="' . htmlspecialchars($row['NOM_PRODUIT']) . '" style="width:80px;">';
            } else {
                // Display a default image if no image is stored in the database
                echo '<img src="../storage/default.jpg" alt="default image" style="width:80px;">';
            }
            ?>
									</td>
									<td><?php echo htmlspecialchars($row['NOM_PRODUIT']); ?></td>
									<td><?php echo htmlspecialchars($row['PRIX']); ?></td>
									<td><?php echo htmlspecialchars($row['QUANTITE_STOCK']); ?></td>
									<td><?php echo htmlspecialchars($row['MARQUE']); ?></td>
									<td><?php echo htmlspecialchars($row['category_name']); ?></td>
									<td><?php echo htmlspecialchars($row['statut_name']); ?></td>
									<td><?php echo htmlspecialchars($row['devise_name']); ?></td>
									<td>
										<a href="product-edit.php?id=<?php echo $row['ID_PRODUIT']; ?>" class="btn btn-primary btn-xs">Edit</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="product-delete.php?id=<?php echo $row['ID_PRODUIT']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
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
				<h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this item?</p>
				<p style="color:red;">Be careful! This product will be deleted from related tables too (orders, payments, etc.).</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<a class="btn btn-danger btn-ok">Delete</a>
			</div>
		</div>
	</div>
</div>

<?php require_once('footer.php'); ?>
