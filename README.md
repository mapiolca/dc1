# Module DC1 / Module DC1

## 🇬🇧 English

### Overview
- French public procurement support: provide assistance with the DC1 application letter form.
- Pre-fills the DC1 template from Dolibarr commercial proposals and linked third parties.
- Generates PDF outputs compliant with the 2016 and 2019 official templates.

### Requirements
- Dolibarr 16.0 or later with the Multicompany module when required.
- PHP 7.4+ and a MySQL-compatible database.

### Installation
1. Copy the `dc1` directory into Dolibarr's `custom` folder.
2. Enable the module from the Dolibarr module configuration page.
3. Apply database migrations from `sql/llx_dc1.sql` using the Dolibarr interface.

### Configuration
- Navigate to **Setup > Modules > DC1** to activate the feature flag.
- Adjust permissions so that only authorised roles can edit the DC1 tab.

### Usage
- Open a commercial proposal and fill in the **DC1** tab.
- Generate the document via the **Proposition commerciale** tab using the DC1 model.

## 🇫🇷 Français

### Aperçu
- Assistance aux marchés publics : facilite la lettre de candidature DC1.
- Pré-remplit le modèle DC1 depuis les propositions commerciales et les tiers liés.
- Génère des PDF conformes aux modèles officiels 2016 et 2019.

### Prérequis
- Dolibarr 16.0 ou plus avec le module Multicompany si nécessaire.
- PHP 7.4+ et une base de données compatible MySQL.

### Installation
1. Copier le répertoire `dc1` dans le dossier `custom` de Dolibarr.
2. Activer le module depuis la page de configuration des modules Dolibarr.
3. Appliquer les migrations SQL depuis `sql/llx_dc1.sql` via l'interface Dolibarr.

### Configuration
- Aller dans **Configuration > Modules > DC1** pour activer le paramètre fonctionnel.
- Ajuster les permissions afin que seules les personnes autorisées modifient l'onglet DC1.

### Utilisation
- Ouvrir une proposition commerciale et remplir l'onglet **DC1**.
- Générer le document depuis l'onglet **Proposition commerciale** en choisissant le modèle DC1.
