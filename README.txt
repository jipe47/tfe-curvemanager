A. Pré-requis
-------------

Ce système a été développé et testé avec PHP 5.3.13. Tant que la version de PHP
est >= 5.3, tout devrait être compatible (sous réserve de modifications
mineures).

B. Installation
---------------

1. Uploader les fichiers dans le répertoire approprié ;
2. Ajouter les droits d'écriture aux dossiers "log", "templates_c" et "cache" ;
3. Spécifier les paramètres de la BDD MySQL dans le fichier "config/config.ini";
4. Importer le fichier db.sql.

C. Documentation
----------------

Voici un wiki (en cours d'écriture à l'heure actuelle) présentant le framework
PHP qui a été utilisé : http://www.itstudents.be/~jipe/jphp/wiki . Sa stabilité
a été testée et est assurée pour ce projet, néanmoins des modifications internes
ou des utilisations un peu plus exotiques (par exemple l'utilisation du cache)
peut entraîner des bugs, vu que certaines fonctionnalités n'ont pas été
terminées.