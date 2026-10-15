# Brief

## Résultat attendu et utilisateurs

Audit daté du code et du site RUN200 Manager pour l'organisateur ; reçus de paiement d'inscription pour les pilotes et le personnel, y compris paiements historiques.

## Périmètre et priorités

P0 : intégrité des paiements, droits de remboursement, comptes créés au guichet et identifiant SMTP exposé à qualifier. P1 : reçu individuel stable, PDF et accès sécurisé, envoi automatique pour les nouveaux paiements et envoi rétroactif des reçus historiques après contrôle des données. Audit des 20 points du site avant publication.

## Contraintes, échéance et budget

Laravel 12 / Livewire 4, hébergement o2switch, dépôt GitHub existant. Aucune échéance ni budget indiqué. Préserver les documents utilisateur non suivis dans `docs/`.

## Critères de réussite

Chaque paiement réellement encaissé est associé à un reçu identifiable et téléchargeable par le pilote concerné et le personnel autorisé ; les autres paiements restent exclus. Les anciens reçus sont envoyés automatiquement après inventaire des anomalies, et les échecs d'envoi sont traçables et relançables. Les cas Stripe, manuels, historiques et rejoués sont testés. Audit sourcé, priorisé et distinct des vérifications de production. Aucun point critique non corrigé ou non vérifié ne donne lieu à un verdict « prêt ».

## Hypothèses et questions ouvertes

Décisions confirmées par l'utilisateur le 15/10/2026 : reçu de paiement d'inscription sans valeur de reçu fiscal de don ; envoi rétroactif automatique aux pilotes pour les paiements déjà effectués. Le régime TVA/facturation n'est pas déterminé par le SIREN seul et aucune mention fiscale ne sera inventée.
