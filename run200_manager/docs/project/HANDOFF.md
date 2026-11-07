# Reprise du projet RUN200 Manager — 16 octobre 2026

## Source de vérité

`docs/project/board.json` est le Kanban source ; `KANBAN.md` est sa vue générée. La campagne de reçus est détaillée dans `RECORDED_RECEIPTS_RELEASE_2026-10-16.md`, son audit de 20 points dans `AUDIT_RECEIPTS_2026-10-16.md` et son prévol dans `RECORDED_RECEIPTS_CAMPAIGN_2026-10-16.md`. Les deux documents utilisateur non suivis dans `docs/` restent hors Git et doivent être préservés.

## Production vérifiée

- La PR #24 a été fusionnée et le serveur o2switch sert le SHA `165bd5a3543f2f9bd50411f4e3386aae0bde4965`. Les quatre contrôles CI, 630 tests locaux/2 111 assertions, Pint et PHPStan ciblé étaient verts. Le serveur répond 200 sur `/` et `/up`, HTTPS et HSTS sont actifs. La sauvegarde SQL privée du 16 octobre est hors racine web, mode 0600 ; elle n'a pas encore été restaurée isolément.
- Configuration en cache : `production`, `payment_receipts.auto_send=true`, `payment_receipts.historical_delivery.mode=pilot`. Le mode automatique général `all` n'a pas été activé. Le checkout serveur conserve ses anciens fichiers modifiés/non suivis ; ne pas les réinitialiser.
- Sur 252 paiements `paid`, 251 reçus sont `sent` : 250 nouveaux pendant la campagne et un antérieur. Les 75 hors Stripe ont tous un reçu `sent` selon la demande de l'association. Zéro `sending` ou `failed`; les 251 lignes de reçus ont une adresse de livraison concordante avec le compte pilote, et 251 dates d'envoi/adresses de livraison sont renseignées. `sent` prouve l'acceptation SMTP, pas la réception en boîte.
- Le paiement Stripe #226 demeure le seul `paid` sans reçu. Son intent est payé et capturé, mais les métadonnées d'inscription/pilote manquent ; la session enregistrée n'est ni liée ni payée et Stripe ne retourne aucune session Checkout liée à cet intent. Ne pas envoyer avant rapprochement manuel de l'affectation de l'encaissement.

## Limites et prochain travail

1. Demander au propriétaire du compte Mailtrap historique de révoquer l'identifiant exposé dans l'histoire Git publique ; l'association peut le contacter mais la révocation n'est pas confirmée. Concevoir la purge 3/5/10 ans approuvée sans détruire les preuves comptables et sportives.
2. Contrôler avec un compte pilote réel le téléchargement de son PDF et le refus d'un autre compte ; confirmer la réception d'au moins un reçu réel. Le test PDF de `contact@cfg.re` est confirmé, mais il ne remplace pas ces parcours. Reprendre l'audit mobile et l'anti-spam sur site.
3. Rapprocher #226 avec les données Stripe et le dossier avant une correction documentée. Les 75 paiements hors Stripe ont été envoyés sur la foi de la saisie applicative ; aucune preuve de caisse/banque indépendante n'a été consultée.
4. Garder le mode historique automatique `pilot` tant que la stratégie de vérification des adresses et les risques résiduels ne sont pas revalidés. L'audit `audit-sites` conserve **4 OK, 8 À corriger, 7 Non vérifiés, 1 Sans objet** avant publication, avec recontrôle ciblé après publication ; aucun verdict global « prêt ».

`REC-05` est **terminée** dans le Kanban après revue QA indépendante du code et du rapport ; #226 est l'exception retenue documentée. `REC-04` reste en livraison faute de preuve de réception par le titulaire ; `REC-03` reflète l'ancien parcours de vérification et demande un recadrage avant reprise. Aucun agent n'opère en arrière-plan après ce relais.

## Contrôle RCCR 2026 — 7 novembre 2026, candidat local

- La branche isolée `codex/licence-rccr-2026` part de `origin/main` au SHA `ebdb319` ; elle exclut la PR Stripe #27. Le serveur o2switch servait encore `ebdb319` lors de la lecture cPanel du 7 novembre. La ligne de production `races.id=5` porte la course « Epreuve D'accélération Run 200 - Novembre » du 8 novembre 2026 ; cette identification a été vérifiée en lecture seule avant toute configuration.
- Source FFSA observée : `licence.ffsa.org` redirige après connexion humaine vers la recherche `ligue.ffsa.org`. Une fiche consultée montrait l'année 2026, `Code type : RCCR`, `Etat : Active` et une date de fin après la course ; aucune donnée nominative FFSA n'est enregistrée dans les contrats. La recherche `verification.ffsa.org` n'affiche pas le type. Le compte Ligue propose un export CSV de licences actives par saison, mais aucun fichier/colonne ni droit de réutilisation n'a été vérifié.
- LIC-2026-01 : six contrats v0.2 revus, code backend et frontend localement intégré. Le contrôle humain est tracé ; les badges vert/rouge exigent des observations cohérentes ; absence, NCC et ambiguïté restent à examiner. VA/VT ne sont pas bloquées. Aucun identifiant FFSA n'est stocké ni transmis par RUN200. La migration n'active aucune course automatiquement.
- QA indépendante locale : 40 tests, 108 assertions réussis ; défauts de cohérence rouge et de bouton corrigés. Build Vite de la branche isolée réussi. L'audit prépublication `LIC_RCCR_AUDIT_2026-11-07.md` relève 2 OK, 9 À corriger, 8 Non vérifiés, 1 Sans objet justifié ; le site global ne peut pas être déclaré prêt. La revocation du secret Mailtrap historique et les défauts de confidentialité antérieurs restent ouverts.
- Prochaines preuves : CI exacte de la branche, contrôle navigateur authentifié bureau/mobile, sauvegarde de production, migration, configuration ciblée `required_license_code=RCCR` sur la ligne #5 après relecture, puis vérification de l'indicateur sur la version servie. Ne pas saisir un verdict FFSA sur un pilote réel comme test de mise en production ; laisser ces décisions aux commissaires.
