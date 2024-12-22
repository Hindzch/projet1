<?php
// Activer l'affichage des erreurs PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Afficher le répertoire actuel de travail
echo "Répertoire actuel de travail : " . getcwd() . "<br>";

// Étape 1: Vérifier si le dossier 'images' existe et contient des fichiers PNG
$monDossier = 'images'; // Nom du dossier contenant les images
$images = []; // Tableau pour stocker les chemins des images PNG

// Vérifier si le dossier existe
if (is_dir($monDossier)) {
    echo "Le dossier '$monDossier' a été trouvé.<br>";

    // Ouvrir le dossier et lire son contenu
    $ouvrirLeDossier = opendir($monDossier);

    // Parcourir les fichiers dans le dossier
    while (($fichier = readdir($ouvrirLeDossier)) !== false) {
        echo "Fichier trouvé : $fichier<br>";

        // Vérifier si le fichier a l'extension '.png'
        if (pathinfo($fichier, PATHINFO_EXTENSION) == 'png') {
            // Ajouter le chemin complet du fichier au tableau
            $images[] = $monDossier . '/' . $fichier;
        }
    }

    // Fermer le dossier
    closedir($ouvrirLeDossier);

    // Afficher les chemins des images PNG trouvées
    echo "<pre>";
    print_r($images);
    echo "</pre>";
} else {
    echo "Le dossier '$monDossier' n'existe pas.<br>";
    exit; // Quitter si le dossier n'existe pas
}

// Étape 2: Calculer la largeur totale et la hauteur maximale des images
$totalWidth = 0;
$maxHeight = 0;

foreach ($images as $imagePath) {
    $dimensions = getimagesize($imagePath);
    $width = $dimensions[0];
    $height = $dimensions[1];

    // Vérification de la largeur et de la hauteur des images
    echo "Largeur de $imagePath : $width px, Hauteur : $height px<br>";

    $totalWidth += $width; // Additionner la largeur au total
    if ($height > $maxHeight) {
        $maxHeight = $height; // Mettre à jour la hauteur maximale
    }
}

// Afficher les résultats des dimensions
echo "Largeur totale du sprite : $totalWidth pixels<br>";
echo "Hauteur maximale du sprite : $maxHeight pixels<br>";

// Étape 3: Créer le sprite (image vide) de la taille calculée
$sprite = imagecreatetruecolor($totalWidth, $maxHeight);
if (!$sprite) {
    echo "Erreur : Impossible de créer l'image du sprite.<br>";
    exit;
}

echo "Sprite créé avec succès.<br>";

// Étape 4: Ajouter chaque image au sprite
$positionX = 0;
foreach ($images as $imagePath) {
    $image = imagecreatefrompng($imagePath);
    if (!$image) {
        echo "Erreur : Impossible de charger l'image $imagePath.<br>";
        exit;
    }

    // Copier l'image dans le sprite
    imagecopy($sprite, $image, $positionX, 0, 0, 0, imagesx($image), imagesy($image));
    $positionX += imagesx($image); // Mettre à jour la position X

    // Libérer la mémoire utilisée par l'image
    imagedestroy($image);
}

// Étape 5: Sauvegarder le sprite
$spritePath = 'sprite.png'; // Chemin relatif pour sauvegarder le sprite
if (imagepng($sprite, $spritePath)) {
    echo "Sprite généré avec succès : $spritePath<br>";
} else {
    echo "Erreur : Impossible de générer le sprite.<br>";
    exit;
}

// Libérer la mémoire du sprite
imagedestroy($sprite);

// Étape 6: Générer le fichier CSS
$cssPath = 'style.css'; // Chemin relatif pour sauvegarder le fichier CSS
$css = fopen($cssPath, 'w');
if (!$css) {
    echo "Erreur : Impossible d'ouvrir le fichier CSS pour écriture.<br>";
    exit;
}

echo "Fichier CSS ouvert avec succès.<br>";

// Écrire les règles CSS pour chaque image
$positionX = 0;
foreach ($images as $imagePath) {
    $nomFichier = basename($imagePath, '.png'); // Nom du fichier sans extension
    $dimensions = getimagesize($imagePath);
    $width = $dimensions[0];
    $height = $dimensions[1];

    // Générer une classe CSS pour chaque image avec les bonnes dimensions et positions
    fwrite($css, ".sprite-$nomFichier {\n");
    fwrite($css, "    width: ${width}px;\n");
    fwrite($css, "    height: ${height}px;\n");
    fwrite($css, "    background: url('$spritePath') -${positionX}px 0;\n");
    fwrite($css, "}\n\n");

    $positionX += $width; // Mettre à jour la position pour la prochaine image
}

// Fermer le fichier CSS
fclose($css);
echo "Fichier CSS généré avec succès : $cssPath<br>";

?>











