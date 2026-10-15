# Reprise du projet RUN200 Manager — 15 octobre 2026

## Source de vérité

`docs/project/board.json` est le Kanban source ; `KANBAN.md` est sa vue générée. Le rapport de la version réellement livrée et les 20 contrôles `audit-sites` figurent dans `RELEASE_2026-10-15.md`. Le rapport `AUDIT_2026-10-15.md` et `PRODUCTION_PREFLIGHT_2026-10-15.md` documentent la référence antérieure ; ne pas les lire comme état actuel.

## Production constatée

O2switch sert `https://run200cfg.clicksolutions.re/` au SHA `0c41b5a7bc9bbc89b7d792bba99aa3b10a5d16fe` (PR #17 et #18 fusionnées, quatre contrôles CI réussis chacune). HTTP redirige 307 vers HTTPS ; HTTPS `/` et `/up` répondent 200 ; assets Vite versionnés servis avec cache long. La migration `payment_receipts` est appliquée, sans autre migration en attente lors de la mise en service. Le serveur est sorti du mode maintenance.

La sauvegarde SQL privée antérieure au déploiement a été restaurée dans une base isolée avec 117 comptes, 268 inscriptions et 319 paiements identiques aux agrégats actifs ; la base temporaire a été supprimée après accord, la sauvegarde conservée. Le serveur contient des fichiers modifiés préexistants, préservés. Deux documents utilisateur non suivis dans `docs/` restent exclus de Git.

Un e-mail de test et son PDF synthétique ont été reçus à `contact@cfg.re` selon l'utilisateur. Le paiement Stripe #247, contrôlé auprès de Stripe, a produit un seul reçu réel marqué `sent` par SMTP. Le PDF de ce reçu a été téléchargé avec la session staff et vérifié sur une page, numéro, SIRET et 100 € ; taille 880 024 octets. Le clic de navigation PDF des navigateurs pilotés est bloqué côté client, tandis que le téléchargement du même lien a réussi. La réception dans la boîte du pilote n'est pas confirmée. Sans session, la route redirige vers la connexion ; refus avec autre compte réel et accès pilote restent non vérifiés.

`PAYMENT_RECEIPTS_AUTO_SEND=ON` dans le cache de production. Un rejeu du listener sur le reçu existant n'a pas créé de doublon. Aucun nouveau paiement confirmé après activation n'a encore été observé en production : l'effet complet sur ce parcours reste non vérifié. Le rattrapage global n'a pas été lancé. Le premier lot de 20 est simulé sans anomalie ni envoi.

## Branche candidate et vérifications

La PR #19 apporte les filtres de rattrapage `--stripe-only --verified-email-only`, bloque la relance automatique d'un envoi SMTP incertain et corrige un ancien message Stripe affiché sur paiement encaissé. Revue indépendante : GO pour le code, NO-GO pour les lots réels à ce stade. Localement : 572 tests / 1 791 assertions, PHPStan, Pint ciblé et compilation des vues réussis. Cette PR n'est pas la version en production tant que son SHA fusionné n'a pas été constaté sur o2switch.

## Blocages et suite priorisée

1. **P0 sécurité** : faire révoquer par le propriétaire du compte l'ancien identifiant Mailtrap encore actif dans l'historique Git public. L'utilisateur a indiqué pouvoir le contacter ; révocation non confirmée. Ne jamais partager la valeur.
2. **P0 validation métier** : exercer sur la cible une inscription complète, ses erreurs, un paiement confirmé et le reçu associé ; vérifier la route PDF avec un pilote autorisé et un autre compte refusé. Audit QA de la production actuelle : 4 OK, 8 À corriger, 7 Non vérifiés, 1 Sans objet. Aucun verdict « prêt ».
3. **P1 données historiques** : contrôler Stripe → base en sens inverse avant le rattrapage Stripe, les destinataires actuels et les comptes exclus ; les 177 références locales ont été contrôlées base → Stripe. L'utilisateur rapprochera lui-même les 75 encaissements hors Stripe avant leur envoi. Les états `sending` demandent rapprochement SMTP manuel avant toute reprise.
4. **P1 conformité** : appliquer sans cascade destructive les durées validées 3/5/10 ans ; qualifier conditions des inscriptions payantes et traceurs.
5. **P2 qualité** : meta description de `/register`, sitemap, images lourdes, contraste, parcours mobile authentifié et vitesse mesurée.

Suite immédiate : attendre les quatre checks de PR #19, fusionner si verts, déployer par fast-forward en préservant l'arbre serveur, vérifier le SHA, l'affichage du paiement #247, la santé et les nouveaux filtres en simulation **sans `--commit`**. Mettre à jour le rapport livré et le Kanban avec ces preuves. Aucun envoi historique massif avant levée des blocages ci-dessus.
