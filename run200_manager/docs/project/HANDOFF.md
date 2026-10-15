# Reprise du projet — 15 octobre 2026

## Source de vérité

`board.json` ; `KANBAN.md` est une vue dérivée. Branche `codex/payment-receipts-audit` créée depuis `main` à `2dc62f2` ; implémentation et rapport commités à `4a0399f`, revue enregistrée à `7afdf90`. PR brouillon : https://github.com/Gilles-boyer/RUN200-MANAGER/pull/17. Deux documents utilisateur non suivis dans `docs/` sont préservés et exclus des commits.

## Livrables locaux

- Audit daté dans `AUDIT_2026-10-15.md` : architecture, données, parcours, erreurs, sécurité, design, responsive, performances, priorités et les 20 contrôles `audit-sites` sur la référence, puis état de la branche après corrections.
- Reçus ordinaires numérotés et PDF privés pour paiements confirmés, identité de l'association fournie, téléchargement pilote/staff autorisé, e-mail nouveau paiement et rattrapage historique par lots après simulation. Procédure dans `RECEIPTS_RELEASE.md`.
- Correctifs sécurité sur comptes guichet, rapprochement Stripe/webhooks, autorisations staff, remboursement et calcul du solde après remboursement partiel. Ancien exemple SMTP masqué dans l'arbre de travail, encore présent dans l'historique Git.

## Preuves

Revue indépendante QA : 68 tests ciblés, 309 assertions, succès ; défauts de reprise et de webhook remboursé détectés puis corrigés. Après SEC-02, WEB-01, LEG-01, PAY-03 et PAY-04, la suite complète du 15 octobre a passé **565 tests et 1 733 assertions**. PHPStan ciblé, Pint sur les fichiers modifiés, `git diff --check`, `php artisan view:cache` et build Vite local : succès. PDF A4 inspecté visuellement lors de la première implémentation. Pint global échoue sur des fichiers antérieurs hors périmètre. Les nouveaux correctifs ont une revue QA locale ; production non vérifiée.

## État production et blocages

Aucune migration, envoi historique ou déploiement n'a été exécuté sur o2switch. Après reconnexion par l'utilisateur, le terminal a permis le [prévol de production](PRODUCTION_PREFLIGHT_2026-10-15.md) : SHA en service `2dc62f2`, PHP CLI 8.3.35, sélecteur PHP web 8.3 pour le domaine, MySQL, 252 paiements confirmés pour 101 adresses, 177 références Stripe rapprochées sans écart par API, sauvegarde JetBackup de la base active affichée le 15 octobre à 01 h 08. Un message SMTP technique simple envoyé à `contact@cfg.re` a été reçu selon l'utilisateur ; PDF joint et parcours web non vérifiés. Le serveur contient des modifications locales à préserver. La restauration et le rapprochement des 75 paiements hors Stripe ne sont pas vérifiés.

Le dépôt GitHub est public. L'ancien mot de passe Mailtrap documenté doit être vérifié/révoqué s'il est actif et l'historique Git traité avec coordination ; l'utilisateur indique ne pas avoir accès au compte Mailtrap, tandis que la production utilise un autre SMTP. Les conditions d'inscription, la conservation effective des données (LEG-02) et les parcours publics/authentifiés critiques restent à qualifier avant de déclarer le site prêt. L'audit agrégé interne des 252 paiements payés et le rapprochement base vers Stripe des 177 paiements concernés sont terminés ; avant tout envoi rétroactif hors Stripe, il reste à rapprocher les 75 autres paiements et contrôler les destinataires actuels.

L'envoi automatique des nouveaux reçus est désactivé par défaut (`PAYMENT_RECEIPTS_AUTO_SEND=false`). Le GET du PDF ne crée plus de reçu ; les liens sont absents avant émission contrôlée. La commande historique reste explicite et son envoi exige `--commit`. Le [plan de déploiement](DEPLOYMENT_PLAN_2026-10-15.md) a été revu par QA, mais sa mise en œuvre dépend encore des portes d'entrée indiquées.

`gh api .../actions/workflows` renvoyait zéro workflow et la PR n'avait aucun check au commit `7afdf90` : les fichiers YAML étaient sous `run200_manager/.github/workflows`. Au commit `96cd957`, leur déplacement à la racine et leur adaptation au sous-dossier sont vérifiés sur GitHub. Au commit `679610e`, les quatre contrôles de la PR #17 passent : Pint et tests PHP 8.2, 8.3 et 8.4 avec Vite.

## Prochaines étapes

1. Pousser la version candidate et vérifier la CI du SHA exact sur la PR ; conserver la PR en brouillon jusqu'à levée des risques de production.
2. Préserver l'arbre serveur, confirmer une sauvegarde récente et un essai de restauration isolée, traiter les points critiques du rapport puis déployer de façon contrôlée avec l'envoi automatique désactivé. La simulation de rattrapage sur des lots de 20 devra être exécutée après migration, sans envoi.
3. Résoudre toute anomalie de paiement relevée et les points critiques du site ; vérifier PDF joint et parcours SMTP, puis effectuer les lots d'envoi autorisés par l'utilisateur après son rapprochement hors Stripe, avec bilan et reprise.
4. Vérifier la version servie et revérifier les contrôles `audit-sites` touchés/critiques en production. Ne pas annoncer « prêt » avant les preuves requises.
