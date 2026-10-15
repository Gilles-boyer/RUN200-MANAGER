# Reçus de paiement : procédure de mise en service

État de référence : voir [RELEASE_2026-10-15.md](RELEASE_2026-10-15.md) pour le SHA réellement servi sur o2switch. Les commandes de confirmation d'adresse et le contrôle Stripe renforcé de la branche `codex/run200-email-verification` ne sont utilisables en production qu'après fusion, migration et vérification de cette nouvelle version.

## Règles de délivrance

- Le document est un reçu de paiement d'inscription, sans valeur de reçu fiscal pour don.
- Le lien « Télécharger le reçu » apparaît seulement après émission du reçu. Un paiement `payé` sans reçu affiche un message d'attente ; le téléchargement n'émet jamais de document par lui-même.
- Un nouveau reçu est émis après confirmation du paiement. Le PDF reste accessible au pilote authentifié ; son e-mail attend la vérification de l'adresse actuelle du compte. L'événement `Verified` reprend les envois `pending` une seule fois.
- Les reçus historiques Stripe exigent confirmation de l'adresse du compte **et** contrôle live du paiement chez Stripe : mode réel, montant, devise, identité de l'inscription et du pilote, payeur, capture, litige et remboursement. Les anomalies sont retenues sans émission ni envoi. L'ancien `payment-receipts:backfill --commit` est interdit hors de l'environnement de test.
- Les 75 paiements hors Stripe attendent le rapprochement personnel de l'utilisateur ; ils ne sont pas inclus dans la campagne Stripe.

## Prévol avant toute campagne

1. Constater le SHA déployé, les migrations appliquées, le cache de configuration, la santé HTTPS et la sauvegarde SQL privée. Revoir les contrôles touchés et critiques de `audit-sites`. Ne pas déclarer le site « prêt » tant que des points critiques sont ouverts.
2. Vérifier que l'e-mail de confirmation est en français, sans reçu ni donnée de paiement, et que son lien signé demande la connexion au compte concerné. Vérifier un compte pilote réel et un autre compte refusé pour le PDF avant d'élargir la diffusion.
3. Vérifier en lecture seule que les paiements Stripe locaux correspondent au payeur, à l'inscription et aux données live Stripe. La concordance de l'adresse copiée dans Stripe ne prouve pas que le pilote contrôle actuellement la boîte. Le contrôle inverse Stripe vers la base reste nécessaire pour établir la complétude des paiements.
4. Mesurer les comptes éligibles avec `php artisan payment-receipts:request-verification --limit=20 --after-user-id=0` : simulation, aucun e-mail ni reçu. Examiner les cas rejetés et le dernier identifiant.

## Invitations de confirmation

Après pilote de réception et revue du lot, la commande suivante envoie **seulement** le lien de confirmation aux comptes non vérifiés ayant un paiement Stripe enregistré. Elle trace une tentative par compte et par adresse ; une issue SMTP incertaine reste bloquée.

```sh
php artisan payment-receipts:request-verification --commit --limit=20 --after-user-id=0
```

Vérifier le résultat du lot avant d'avancer le curseur. Le pilote doit se connecter à son compte et confirmer l'adresse ; le service contrôle alors chaque paiement chez Stripe, émet le reçu éligible et envoie son PDF une seule fois. Ne jamais convertir directement `email_verified_at` en masse. Une nouvelle adresse demande une nouvelle confirmation. Les comptes déjà vérifiés avant cette campagne et leurs éventuels autres paiements seront traités dans un lot contrôlé distinct après vérification de leur adresse actuelle ; ne pas les marquer artificiellement comme destinataires de la campagne.

## Incidents et reprise

- `email_status=sent` : ne pas renvoyer. `sending` : résultat SMTP potentiellement incertain, rapprochement manuel des journaux avant toute intervention. `not_requested` : reçu historique non libéré. `pending` : nouveau reçu émis, e-mail encore non parti.
- En cas d'échec de la requête de reprise lors de `Verified`, l'adresse reste vérifiée et l'erreur est journalisée. Identifier les reçus `pending` de comptes dont `email_verified_at` est renseigné, puis examiner individuellement le paiement, le destinataire et l'absence d'envoi avant une reprise ciblée. Ne pas relancer l'ancien backfill en production. L'absence de cron Run200 fiable signifie que cette reprise d'incident est manuelle ; l'automatisme nominal n'est pas une garantie de livraison pendant une panne.
- Une adresse modifiée par le pilote, par l'administration ou par une commande de normalisation perd sa vérification. La campagne conserve uniquement un HMAC de l'adresse invitée ; une rotation de `APP_KEY` invalide cette preuve et impose une nouvelle invitation.
- Les reçus déjà émis gardent leur numéro et leur photographie du paiement à l'émission. Un remboursement ultérieur ou une réattribution de pilote demande un rapprochement explicite ; ne pas modifier silencieusement le PDF historique.

Pour un incident sur `pending`, commencer par cette lecture seule depuis la base de production :

```sql
SELECT r.id AS receipt_id, p.id AS payment_id, p.method, r.email_status
FROM payment_receipts r
JOIN payments p ON p.id = r.payment_id
JOIN race_registrations rr ON rr.id = p.race_registration_id
JOIN pilots pl ON pl.id = rr.pilot_id
JOIN users u ON u.id = pl.user_id
WHERE r.email_status = 'pending' AND u.email_verified_at IS NOT NULL
ORDER BY r.id;
```

Pour **chaque** identifiant, contrôler le paiement, le compte destinataire actuel, le journal SMTP et l'absence de `sending` ou `sent`. Pour Stripe, refaire le contrôle live du paiement et du payeur ; pour un paiement hors Stripe, demander le rapprochement de l'utilisateur. Si l'échec provenait seulement de la lecture DB avant SMTP, ouvrir `php artisan tinker`, recharger l'identifiant exact du reçu et son compte destinataire, puis appeler `SendPaymentReceipt::send($receipt, requireVerifiedEmail: true, expectedEmail: $user->email)` dans une intervention supervisée. Cet appel PHP n'est pas une commande shell autonome. Constater ensuite `sent` et l'absence de doublon. Conserver l'identifiant et la preuve de cette intervention dans le Kanban.

## Vérifications de sortie

Contrôler les nombres de comptes invités, de confirmations, de reçus créés, d'e-mails `sent`, de retenues et d'incidents. Inspecter un PDF authentifié, le montant, le SIRET et le remboursement connu ; tester le refus avec un autre compte. `sent` signifie acceptation SMTP, pas réception prouvée dans la boîte du pilote. Réexécuter l'audit des points touchés et critiques sur le SHA réellement déployé.
