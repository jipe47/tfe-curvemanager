tfe-curvemanager
================

Ce répertoire contient tous les fichiers sources du serveur de jeu pour l'application Android Curve Predictor. Il va de paire avec celui qui stocke l'application : [Curve Predictor](https://github.com/jipe47/tfe-curvepredictor "Curve Predictor").

Pré-requis
----------

Ce système a été développé et testé avec PHP 5.3.13. Tant que la version de PHP est >= 5.3, tout devrait être compatible (sous réserve de modifications mineures).

Installation
------------

1. Uploader les fichiers dans le répertoire approprié ;
2. Ajouter les droits d'écriture aux dossiers "log", "templates_c" et "cache" ;
3. Spécifier les paramètres de la BDD MySQL dans le fichier "config/config.ini";
4. Importer le fichier db.sql (compressé dans db.zip) dans la base de données.

Liste des modules
-----------------

* **bugtracker** : module gérant le bug tracker. Il nécessite le module de captcha.
* **captcha** : module gérant un captcha. Il ne dépend d'aucun autre module.
* **category** : module implémentant des catégories sur plusieurs niveaux. Il ne dépend d'aucun autre module.
* **curve** : module implémentant toute la logique du serveur de jeu. Il dépend des modules de catégorie et d'utilisateurs android
* **editor** : module intégrant un éditeur de texte "avancé". Il est présent car peut être utilisé par certains modules.
* **faq** : module de gestion de foire aux questions. Il ne dépend d'aucun autre module.
* **logviewer** : module permettant une visualisation des fichiers de log dans une interface web. Il ne dépend d'aucun autre module.
* **user** : module gérant des utilisateurs. Ici, il ne sert qu'aux administrateurs.
* **user-android** : modules gérant les utilisateurs android du système, mais n'implémentant aucune fonctionnalité particulière.

Documentation
-------------

Site internet du framework JPHP : [http://www.itstudents.be/~jipe/jphp/wiki](http://www.itstudents.be/~jipe/jphp/wiki "JPHP")

Licence
-------

Ce code est sous licence [CC BY-NC][cc] : vous avez le droit de réutiliser ce travail en mentionnant son auteur et uniquement pour des utilisations non commerciales.

[cc] : http://en.wikipedia.org/wiki/Creative_Commons_license "Creative Commons licence"
