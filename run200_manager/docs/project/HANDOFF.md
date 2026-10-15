# Reprise du projet — 15 octobre 2026

## Source de vérité

`board.json` ; `KANBAN.md` est une vue dérivée. Branche `codex/payment-receipts-audit` créée depuis `main` à `2dc62f2`. Deux documents utilisateur non suivis dans `docs/` sont préservés et exclus des commits.

## Livrables locaux

- Audit daté dans `AUDIT_2026-10-15.md` : architecture, données, parcours, erreurs, sécurité, design, responsive, performances, priorités et les 20 contrôles `audit-sites` sur la référence, puis état de la branche après corrections.
- Reçus ordinaires numérotés et PDF privés pour paiements confirmés, identité de l'association fournie, téléchargement pilote/staff autorisé, e-mail nouveau paiement et rattrapage historique par lots après simulation. Procédure dans `RECEIPTS_RELEASE.md`.
- Correctifs sécurité sur comptes guichet, rapprochement Stripe/webhooks, autorisations staff, remboursement et calcul du solde après remboursement partiel. Ancien exemple SMTP masqué dans l'arbre de travail, encore présent dans l'historique Git.

## Preuves

Revue indépendante QA : 68 tests ciblés, 309 assertions, succès ; défauts de reprise et de webhook remboursé détectés puis corrigés. Suite complète : 557 tests, 1 644 assertions, succès. Pint sur les fichiers modifiés et `git diff --check` : succès. `php artisan view:cache` et route privée de téléchargement : succès. PDF A4 inspecté visuellement. Pint global échoue sur des fichiers antérieurs hors périmètre.

## État production et blocages

Aucune migration, envoi historique ou déploiement n'a été exécuté sur o2switch. La version servie n'est pas rapprochée d'un SHA. La session cPanel a montré l'accueil après reconnexion, mais le clic sur Terminal a immédiatement renvoyé 401 « jeton de sécurité non valide » ; nouvelle connexion directe demandée à l'utilisateur. La base historique, l'expéditeur SMTP, les sauvegardes/retour arrière et la livraison réelle d'e-mails ne sont pas vérifiés.

Le dépôt GitHub est public. L'ancien mot de passe SMTP documenté doit être vérifié/révoqué s'il est actif et l'historique Git traité avec coordination. Les conditions d'inscription, la conservation des données et les parcours publics/authentifiés critiques restent à qualifier avant de déclarer le site prêt. Aucun envoi rétroactif ne doit partir avant l'audit agrégé de la base et la vérification des destinataires.

## Prochaines étapes

1. Finaliser le commit/PR de la branche et lire les vérifications CI.
2. Dès accès cPanel stable : inspecter en lecture seule le SHA, la configuration d'exécution non secrète, la sauvegarde, la version PHP, les migrations, le cron et les agrégats historiques via la simulation de rattrapage après déploiement contrôlé.
3. Résoudre les anomalies de paiement et les points critiques du site ; vérifier SMTP et effectuer les lots d'envoi autorisés par l'utilisateur, avec bilan et reprise.
4. Vérifier la version servie et revérifier les contrôles `audit-sites` touchés/critiques en production. Ne pas annoncer « prêt » avant les preuves requises.
