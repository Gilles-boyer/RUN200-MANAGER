# Reprise du projet RUN200 Manager — 15 octobre 2026

## Source de vérité

`docs/project/board.json` est le Kanban source ; `KANBAN.md` est sa vue générée. Le rapport de la version réellement livrée et les 20 contrôles `audit-sites` figurent dans `RELEASE_2026-10-15.md`. Le rapport `AUDIT_2026-10-15.md` et `PRODUCTION_PREFLIGHT_2026-10-15.md` documentent la référence antérieure ; ne pas les lire comme état actuel.

## Production constatée

O2switch sert `https://run200cfg.clicksolutions.re/` au SHA `dde80904721fa2b6a95d0c64e49f14f1015fd169` (PR #17, #18 et #19 fusionnées ; 4/4 contrôles CI de la PR #19 réussis). HTTP redirige 307 vers HTTPS ; HTTPS `/` et `/up` répondent 200 ; assets Vite versionnés servis avec cache long. La migration `payment_receipts` est appliquée ; la PR #19 n'ajoute aucune migration. Son déploiement par avance rapide et le renouvellement des caches ont réussi. Le serveur est sorti du mode maintenance.

La sauvegarde SQL privée antérieure au déploiement a été restaurée dans une base isolée avec 117 comptes, 268 inscriptions et 319 paiements identiques aux agrégats actifs ; la base temporaire a été supprimée après accord, la sauvegarde conservée. Le serveur contient des fichiers modifiés préexistants, préservés. Deux documents utilisateur non suivis dans `docs/` restent exclus de Git.

Un e-mail de test et son PDF synthétique ont été reçus à `contact@cfg.re` selon l'utilisateur. Le paiement Stripe #247, contrôlé auprès de Stripe, a produit un seul reçu réel marqué `sent` par SMTP. Le PDF de ce reçu a été téléchargé avec la session staff et vérifié sur une page, numéro, SIRET et 100 € ; taille 880 024 octets. Le clic de navigation PDF des navigateurs pilotés est bloqué côté client, tandis que le téléchargement du même lien a réussi. La réception dans la boîte du pilote n'est pas confirmée. Sans session, la route redirige vers la connexion ; refus avec autre compte réel et accès pilote restent non vérifiés.

`PAYMENT_RECEIPTS_AUTO_SEND=ON` dans le cache de production ; 1 reçu `sent`. Un rejeu du listener sur le reçu existant n'a pas créé de doublon. Aucun nouveau paiement confirmé après activation n'a encore été observé en production : l'effet complet sur ce parcours reste non vérifié. Le rattrapage global n'a pas été lancé. Sur le SHA déployé, la simulation filtrée `--stripe-only --verified-email-only --limit=20 --after-id=0` a examiné 20 paiements : 20 adresses de compte non vérifiées, zéro éligible et aucun envoi.

## Version fusionnée et vérifications

La PR #19 déployée apporte les filtres de rattrapage `--stripe-only --verified-email-only`, bloque la relance automatique d'un envoi SMTP incertain et corrige un ancien message Stripe affiché sur paiement encaissé. La page staff du paiement #247 n'affiche plus cette ancienne erreur et montre le lien de reçu existant. Revue indépendante : GO pour le code, NO-GO pour les lots réels à ce stade. Localement : 572 tests / 1 791 assertions, PHPStan, Pint ciblé et compilation des vues réussis. Les quatre contrôles CI de la PR ont réussi. Le SHA fusionné `dde8090` a été constaté sur o2switch.

## Destinataires des reçus historiques

Les 177 références Stripe locales ont été retrouvées côté Stripe. Parmi leurs comptes, 4 seulement ont `email_verified_at` renseigné et 173 ne l'ont pas. La comparaison agrégée a trouvé 176 adresses concordantes entre Stripe et la base et un paiement sans e-mail Stripe. Stripe reçoit toutefois vraisemblablement l'adresse fournie par l'application ; cette concordance ne prouve pas que le pilote contrôle la boîte. Le contrat `MustVerifyEmail` est commenté dans le SHA actuellement déployé. L'utilisateur a choisi l'envoi d'un message de confirmation **sans reçu**, suivi d'un envoi automatique après validation de l'adresse et contrôle du paiement. La branche candidate `codex/run200-email-verification` implémente ce flux ; elle n'est pas encore la version de production. Aucun lot historique n'a été lancé. L'utilisateur rapprochera lui-même les 75 paiements hors Stripe.

## Blocages et suite priorisée

1. **P0 sécurité** : faire révoquer par le propriétaire du compte l'ancien identifiant Mailtrap encore actif dans l'historique Git public. L'utilisateur a indiqué pouvoir le contacter ; révocation non confirmée. Ne jamais partager la valeur.
2. **P0 validation métier** : exercer sur la cible une inscription complète, ses erreurs, un paiement confirmé et le reçu associé ; vérifier la route PDF avec un pilote autorisé et un autre compte refusé. Audit QA de la production actuelle : 4 OK, 8 À corriger, 7 Non vérifiés, 1 Sans objet. Aucun verdict « prêt ».
3. **P1 données historiques** : fusionner et déployer après revue la branche de confirmation choisie, puis tester un petit pilote et mesurer les retenues avant la campagne. Le contrôle inverse Stripe → base et les comptes déjà vérifiés restent à traiter pour la complétude. Les 176 correspondances d'e-mail Stripe/base ne suffisent pas comme preuve de contrôle. L'utilisateur rapprochera lui-même les 75 encaissements hors Stripe avant leur envoi. Les états `sending` demandent rapprochement SMTP manuel avant toute reprise.
4. **P1 conformité** : appliquer sans cascade destructive les durées validées 3/5/10 ans ; qualifier conditions des inscriptions payantes et traceurs.
5. **P2 qualité** : meta description de `/register`, sitemap, images lourdes, contraste, parcours mobile authentifié et vitesse mesurée.

Suite immédiate : livrer et vérifier la branche de confirmation sans lancer de campagne ; conserver la simulation comme contrôle de préparation. Tester ensuite avec des comptes réels distincts le parcours de nouveau paiement et les droits d'accès au PDF. Réviser les 20 contrôles touchés après toute évolution et documenter la nouvelle version avant publication.
