# Reprise du projet RUN200 Manager — 15 octobre 2026

## Source de vérité

`docs/project/board.json` est le Kanban source ; `KANBAN.md` est sa vue générée. L'état actuellement livré, l'invitation pilote et les 20 contrôles `audit-sites` sont dans `PILOT_RELEASE_2026-10-15.md`. `RELEASE_2026-10-15.md`, `AUDIT_2026-10-15.md` et `PRODUCTION_PREFLIGHT_2026-10-15.md` décrivent des jalons antérieurs. Les deux documents utilisateur non suivis dans `docs/` sont préservés hors Git.

## Production actuelle

- o2switch sert `https://run200cfg.clicksolutions.re/` au SHA `150aa63b801b0223810a5dc42d96c86da54b9545` (PR #22 fusionnée, quatre contrôles CI verts). Le SHA, HTTPS `/` et `/up` 200, caches renouvelés et sortie du mode maintenance ont été vérifiés. Aucun fichier serveur préexistant n'a été réinitialisé. Les PR #21/#22 n'ajoutent aucune migration. La sauvegarde privée et l'essai de restauration restent documentés dans l'ancien rapport de release.
- Le code local de la PR #22 a passé 617 tests et 2 026 assertions, PHPStan, Pint ciblé et compilation des vues ; revue QA indépendante GO sur le code. Ces contrôles ne prouvent ni la réception des e-mails ni les parcours complets du site.
- La configuration active mise en cache est `auto_send=true` **booléen**, historique `pilot`, utilisateur #46, paiement #27. Le paiement #27 est celui de l'inscription #29 (100 €) ; le prévol Stripe n'a trouvé aucune retenue. Le compte #46 possède aussi trois paiements historiques (#69, #171, #255) hors portée du pilote. Les nouveaux paiements confirmés restent activés, sans nouveau parcours réel observé depuis cette activation.
- Une seule invitation de vérification **sans reçu** a été acceptée par le SMTP. L'enregistrement est `pilot_sent` avec date. Au dernier contrôle, l'adresse n'était pas vérifiée et aucun des quatre paiements n'avait de reçu. L'utilisateur demandera au titulaire de cliquer sur le lien et nous préviendra. Ne pas relancer automatiquement un envoi SMTP incertain.

## Suite et blocages

1. Après le clic du titulaire, vérifier `email_verified_at` du compte #46, un seul reçu #27 à 100 € marqué `sent`, aucun reçu #69/#171/#255, puis réception et ouverture du PDF, accès du titulaire et refus avec un autre compte. Garder le mode `pilot` tant que cette preuve manque.
2. Avant tout passage à `all` : contrôler Stripe → base en sens inverse, résoudre le paiement #226 retenu faute de métadonnées suffisantes, puis préparer de petits lots traçables. L'utilisateur rapprochera personnellement les 75 paiements hors Stripe avant leur envoi. La campagne générale n'a pas été lancée.
3. Faire révoquer par le propriétaire du compte l'ancien identifiant Mailtrap, actif lors du dernier contrôle et présent dans l'historique Git public ; la révocation n'est pas confirmée et l'utilisateur peut contacter ce propriétaire. Appliquer les durées de conservation approuvées 3/5/10 ans avec anonymisation/archivage sûr. Aucun de ces chantiers n'est clos.
4. Reprendre l'audit des parcours critiques : inscription et paiement réels, nouveau reçu, droits d'accès au PDF, anti-spam derrière proxy, mobile authentifié. Audit actuel : **4 OK, 8 À corriger, 7 Non vérifiés, 1 Sans objet ; aucun verdict « prêt »**. Après tout changement, revérifier les points touchés et critiques.

`REC-04` est en livraison après revue indépendante ; la preuve métier du PDF pilote est en attente. `REC-03` reste en revue et la diffusion générale suspendue. `REL-01` ne peut être clos tant que les points critiques de la release ne le sont pas. Aucun agent ne travaille en arrière-plan après ce relais.
