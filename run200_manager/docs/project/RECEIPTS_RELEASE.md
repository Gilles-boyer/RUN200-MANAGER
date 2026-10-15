# Mise en service des reçus de paiement

## Portée et prérequis

Le reçu est un justificatif de paiement d'inscription, sans valeur de reçu fiscal de don. Chaque paiement confirmé a un numéro stable et un PDF privé. Les nouveaux paiements déclenchent l'e-mail après validation de la transaction. Les paiements anciens sont traités par commande explicite, en lots reprenables.

Avant la mise en production, vérifier le SHA déployé, sauvegarder la base et tester sa restauration, confirmer l'expéditeur SMTP, révoquer l'ancien identifiant SMTP exposé dans l'historique Git s'il est encore actif, et vérifier les contrôles critiques de l'audit du site. Le [prévol du 15 octobre 2026](PRODUCTION_PREFLIGHT_2026-10-15.md) constate 252 paiements confirmés pour 101 adresses, une sauvegarde JetBackup de la base active et un transport SMTP configuré, mais aucune restauration ni livraison réelle d'e-mail. Le serveur reste au commit `2dc62f2` ; les étapes de migration et d'envoi ci-dessous n'y ont pas été exécutées.

## Simulation puis envoi historique

Depuis la racine Laravel, après déploiement du code et migration de la table `payment_receipts` :

```sh
php artisan payment-receipts:backfill --limit=20 --after-id=0
```

La simulation n'envoie aucun e-mail. Elle donne les paiements examinés, les reçus déjà présents, les montants ou remboursements incohérents, les adresses manquantes, les envois en cours et le dernier identifiant examiné. Résoudre chaque anomalie du lot, puis relancer exactement le même lot. Ne pas déduire un montant manquant d'un tarif de course.

Une fois un lot sans anomalie, l'envoi est autorisé par l'utilisateur :

```sh
php artisan payment-receipts:backfill --limit=20 --after-id=0 --commit
```

Vérifier d'abord un reçu PDF et un envoi maîtrisé, puis le nombre envoyé et les échecs de chaque lot. Rejouer le même lot après correction d'un échec ; les reçus et les envois déjà marqués `sent` ne sont pas renvoyés. Passer au lot suivant avec le `--after-id` affiché. Conserver le bilan agrégé de chaque lot et arrêter en cas de taux d'échec SMTP ou d'anomalie imprévue. Un état `sending` exige un rapprochement manuel avec les journaux SMTP avant toute relance, car un serveur peut avoir accepté l'e-mail sans que l'application ait enregistré `sent`.

## Contrôles de sortie

- Un pilote authentifié télécharge uniquement les reçus de ses inscriptions ; le staff autorisé peut consulter ceux des inscriptions qu'il gère.
- Vérifier les reçus Stripe, manuels et historiques sur la version livrée, sans déclencher de transaction réelle de test en production. Tester les remboursements partiels et totaux en local ou en préproduction ; aucun cas remboursé n'est présent dans les données de production contrôlées, donc ce parcours restera non vérifié en production jusqu'à un cas réel ou contrôlé.
- Contrôler PDF, numéro, association, SIRET, pilote concerné, montant initial, remboursement connu à la date d'émission, et message e-mail historique sans promesse de course future.
- Confirmer qu'aucun `pending`, `failed` ou `cancelled` n'a reçu de justificatif et que les e-mails historiques ne sont pas dupliqués.
- Revoir les points touchés et critiques de `AUDIT_2026-10-15.md`, puis vérifier la version réellement servie en production. Le prévol a contrôlé la cohérence interne des paiements historiques ; l'émission de leurs reçus et la délivrabilité SMTP restent non vérifiées tant que cette procédure n'a pas été exécutée.
