# LPPWM - ECommerce
Site de e-commerce sur le thème de la batterie réalisé avec Symfony.

## Comptes
Deux compte administrateurs sont créés :
* Nom d'utilisateur : admin1 | Mot de passe : admin1l18
* Nom d'utilisateur : admin2 | Mot de passe : admin2l18

Un compte utilisateur est créé :
* Nom d'utilisateur : aude| Mot de passe : aude
Ce compte possède déjà une commande et un produit dans son panier.

## Fonctionnement
### Comptes
Les comptes administrateurs peuvent naviguer sur le site mais ne peuvent pas utiliser le panier ou passer de commandes.  
Côté administration ils peuvent :
* Consulter la liste des utilisateurs
* Consulter la liste des catégories, en ajouter et les modifier
* Consulter la liste des produits, en ajouter et les modifier
* Désactiver un produit ou une catégorie. Il n'est pas possible de supprimer un produit ou une catégorie car cela pourrai poser des problèmes au utilisateurs ayant passé une commande avec ces produits. Il est donc possible de retirer un produit du catalogue (il ne sera plus visible à l'achat et sera retiré de tous les paniers), ou de désactiver une catégorie (elle disparaîtra et tous les produits de la catégorie seront retirés du catalogue).

### Paniers
Les utilisateurs non connectés disposent d'un panier stocké dans la session. Les utilisateurs connecté disposent d'un panier en base de données lié à leur compte.
Lorsqu'un utilisateur se connecte et dispose d'un panier en session, celui-ci est ajouté au panier en base de données. Le panier en base de données est prioritaire en cas de conflits.
