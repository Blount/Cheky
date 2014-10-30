LBCAlerte
======

Alerte mail et RSS pour Leboncoin.fr

## Introduction

À quoi sert cette application ?
* recevoir des alertes mails sur vos recherches.
* générer des flux RSS pour vos recherches.
* compléter vos recherches avec des filtres supplémentaires (multi-categories, multi-villes, etc.).

## Installation

Une fois votre hébergement trouvé, envoyez simplement le contenu du fichier téléchargé (après décompression) sur votre espace web. Vous pouvez sans problème le mettre dans un sous dossier.

**Donnez les permissions d'écriture sur le répertoire "var". Celui-ci contiendra des données générées par l'application: config, fichiers des alertes, cache des flux RSS, etc.**

### Protection du répertoire "var"

L'accès au répertoire "var" est bloqué pour les clients HTTP grâce à un fichier `.htaccess`, l'utilisation d'un serveur autre qu'Apache requiert une configuration additionnelle pour en bloquer l'accès.

* Exemple pour Nginx
  
  ```Nginx
  location /chemin-vers-LBCAlerte/var {
      deny all;
  }
  ```

## Flux RSS

Effectuez votre recherche sur Leboncoin.fr. Lorsque les résultats vous satisfont, copiez le lien de votre barre d'adresse. Retournez à votre application (onglet RSS) et collez le lien dans le champ.

Validez en cliquant sur "Générer le flux". Maintenant, un flux RSS devrait s'afficher.

Recommencez autant de fois que vous le souhaitez.

## Alerte Mail

Rendez-vous à l'adresse de l'application. Vous pouvez ajouter votre première alerte en cliquant sur le lien "Ajouter une alerte".

* **E-Mail** : entrez l'E-Mail destinataire des alertes.
* décochez la case "grouper les annonces dans un unique mail" pour recevoir les annonces dans un mail séparé.
* **Titre** : un titre. 
* **Url de recherche** : c'est l'adresse Leboncoin correspondant à votre recherche. 
* **Intervalle de contrôle d'alerte** : l'alerte sera contrôle par le script toutes les X minutes. 
* **Groupe** : indiquer un nom de groupe (optionnel). Permet de grouper les alertes sur la page d'affichage.

Maintenant, il faut définir une tâche cron. Deux solutions s'offrent à vous :

* appeler directement le fichier "check.php" en CLI :
`*/5 * * * * php -f /path/to/your/web/directory/check.php`
* appeler "check.php" via l'adresse de votre site. Exemple : http://exemple.com/alerte/check.php

Pour le second point, vous pouvez utiliser un service en ligne appelé webcron (voir dans un moteur de recherche).


## Pour finir

Surveillez bien les mises à jour de l'application. Lorsque Leboncoin effectue des modifications sur leur site, l'application risque de ne plus fonctionner. En général, j'applique un correctif dès que je suis mis au courant.
