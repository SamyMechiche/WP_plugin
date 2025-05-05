# Témoignages Plugin WordPress

## Description

Ce plugin WordPress permet aux utilisateurs d'ajouter des témoignages (avis sur un produit ou service) sur leur site WordPress. Les témoignages sont facilement récupérables via des **Custom Fields** grâce au plugin **ACF (Advanced Custom Fields)**, ce qui permet une gestion souple et personnalisée des avis.

Le plugin offre une interface simple et intuitive pour ajouter des témoignages et les afficher sur votre site.

## Fonctionnalités

- Ajout et gestion des témoignages via l'interface d'administration de WordPress.
- Affichage des témoignages sur le site via un shortcode ou une fonction PHP.
- Utilisation d'ACF pour la personnalisation et la récupération des témoignages.

## Installation

1. Téléchargez ce plugin dans le répertoire `wp-content/plugins/` de votre installation WordPress.
2. Activez le plugin depuis le tableau de bord WordPress.

## Utilisation

### Ajouter un témoignage

1. Une fois le plugin activé, vous trouverez une nouvelle section "Témoignages" dans le menu d'administration.
2. Cliquez sur "Ajouter un témoignage" et remplissez les champs nécessaires (nom, avis, note, etc.).
3. Enregistrez votre témoignage.

### Afficher les témoignages sur votre site

Vous pouvez afficher les témoignages dans vos pages ou articles en utilisant un **shortcode** ou en appelant une fonction PHP.

#### Shortcode
```[temoignage]```

#### Fonction PHP
```php
<?php
echo do_shortcode('[temoignage]');
?>
