<?php
// Simule un mot de passe utilisateur en clair
$password_clair = 'admin1234';

// Simule un hash généré précédemment
$hash_en_base = '$2y$10$leMwIWAWHYQgASXo4U0ecuBm50hUZw3VUvTTYwrtkO0rLB6fNvJ5O';

// Test
if (password_verify($password_clair, $hash_en_base)) {
    echo "Mot de passe valide !";
} else {
    echo "Mot de passe incorrect.";
}
?>
