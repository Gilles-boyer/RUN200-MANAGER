# Mise en service des reçus de paiement

> État réel de production au 15 octobre 2026 : voir [RELEASE_2026-10-15.md](RELEASE_2026-10-15.md). Le prévol et les commandes ci-dessous documentent la procédure ; les passages indiquant que le code n'était pas encore déployé sont historiques. Le SHA `0c41b5a7bc9bbc89b7d792bba99aa3b10a5d16fe` sert les nouveaux reçus avec `PAYMENT_RECEIPTS_AUTO_SEND=ON`. Un reçu Stripe réel a été envoyé et un PDF de contrôle a été reçu. Le rattrapage général attend toujours le rapprochement des 75 encaissements hors Stripe par l'utilisateur.

## Portée et prérequis

Le reçu est un justificatif de paiement d'inscription, sans valeur de reçu fiscal de don. Chaque reçu émis a un numéro stable et un PDF privé. L'envoi automatique des nouveaux paiements est désactivé par défaut via `PAYMENT_RECEIPTS_AUTO_SEND=false` et ne doit être activé qu'après validation du parcours réel. Les paiements anciens sont traités par commande explicite, en lots reprenables. Le téléchargement d'un paiement sans reçu existant ne crée rien et répond 404 ; le lien n'apparaît qu'après émission contrôlée.

Avant la mise en production, vérifier le SHA déployé, sauvegarder la base et tester sa restauration, confirmer l'expéditeur SMTP, révoquer l'ancien identifiant SMTP exposé dans l'historique Git s'il est encore actif, et vérifier les contrôles critiques de l'audit du site. Le [prévol du 15 octobre 2026](PRODUCTION_PREFLIGHT_2026-10-15.md) constate 252 paiements confirmés pour 101 adresses, une sauvegarde JetBackup de la base active et un test de message SMTP simple reçu à `contact@cfg.re`. La restauration, le PDF joint et le parcours d'envoi web restent non vérifiés. Le serveur reste au commit `2dc62f2` ; les étapes de migration et d'envoi ci-dessous n'y ont pas été exécutées.

## Simulation puis envoi historique

Depuis la racine Laravel, après déploiement du code et migration de la table `payment_receipts` :

```sh
php artisan payment-receipts:backfill --limit=20 --after-id=0
```

Pour la tranche Stripe déjà rapprochée en lecture seule, appliquer les deux filtres sur la **simulation et l'envoi du même lot** :

```sh
php artisan payment-receipts:backfill --stripe-only --verified-email-only --limit=20 --after-id=0
```

`--stripe-only` retient la méthode stockée `stripe` ; `--verified-email-only` exclut une adresse de compte non vérifiée et la signale. Une ligne exclue provoque un code d'échec et impose de vérifier la situation avant de poursuivre au curseur suivant. Ces filtres ne prouvent pas par eux-mêmes le paiement chez Stripe ou l'identité du véritable porteur de carte. Conserver la liste des références rapprochées et contrôler les destinataires avant `--commit`. Un changement d'adresse entre simulation et envoi est recontrôlé juste avant SMTP, mais la base et SMTP ne forment pas une transaction atomique.

La simulation n'envoie aucun e-mail. Elle donne les paiements examinés, les reçus déjà présents, les montants ou remboursements incohérents, les adresses manquantes, les envois en cours et le dernier identifiant examiné. Résoudre chaque anomalie du lot, puis relancer exactement le même lot. Ne pas déduire un montant manquant d'un tarif de course.

Après rapprochement comptable des paiements du lot par l'utilisateur, un lot sans anomalie peut être envoyé :

```sh
php artisan payment-receipts:backfill --limit=20 --after-id=0 --commit
```

Après revue d'un lot Stripe filtré et sans anomalie, utiliser exactement les mêmes options et le même curseur, avec `--commit`. Ne pas envoyer un lot qui contient une adresse non vérifiée sans traitement explicite de son cas. Repasser depuis `--after-id=0` pour retrouver les lignes exclues, même si d'autres lots ont avancé au-delà de leur ID.

Ne pas lancer cette commande sur les 75 paiements hors Stripe avant le rapprochement personnel annoncé par l'utilisateur. Vérifier d'abord un reçu PDF et un envoi maîtrisé, puis le nombre envoyé et les échecs de chaque lot. Rejouer le même lot après correction d'un échec **pré-envoi certain** ; les reçus et les envois déjà marqués `sent` ne sont pas renvoyés. Passer au lot suivant avec le `--after-id` affiché seulement après traitement des exclus. Conserver le bilan agrégé et arrêter en cas d'anomalie. Un état `sending` exige un rapprochement manuel avec les journaux SMTP avant toute relance : une exception de transport ou un échec de l'écriture finale peut survenir après acceptation du message. Le code laisse cet état bloqué pour éviter un renvoi automatique potentiellement double.

## Contrôles de sortie

- Un pilote authentifié télécharge uniquement les reçus de ses inscriptions ; le staff autorisé peut consulter ceux des inscriptions qu'il gère.
- Vérifier les reçus Stripe, manuels et historiques sur la version livrée, sans déclencher de transaction réelle de test en production. Tester les remboursements partiels et totaux en local ou en préproduction ; aucun cas remboursé n'est présent dans les données de production contrôlées, donc ce parcours restera non vérifié en production jusqu'à un cas réel ou contrôlé.
- Contrôler PDF, numéro, association, SIRET, pilote concerné, montant initial, remboursement connu à la date d'émission, et message e-mail historique sans promesse de course future.
- Confirmer qu'aucun `pending`, `failed` ou `cancelled` n'a reçu de justificatif et que les e-mails historiques ne sont pas dupliqués.
- Revoir les points touchés et critiques de `AUDIT_2026-10-15.md`, puis vérifier la version réellement servie en production. Le prévol a contrôlé la cohérence interne des paiements historiques ; l'émission de leurs reçus et la délivrabilité SMTP restent non vérifiées tant que cette procédure n'a pas été exécutée.
