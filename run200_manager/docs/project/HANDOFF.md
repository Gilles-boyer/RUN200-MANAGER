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
