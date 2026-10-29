# IMPLEMENTATION PLAN — REG-UNPAID-01

Version de cadrage du 28/10/2026, base locale `1aaa13c`. Carte Kanban : `REG-UNPAID-01`.

| Étape | Propriétaire / fichiers | Sortie vérifiable |
| --- | --- | --- |
| 1. Contrats | Coordinateur : six documents de cette itération, `DOCUMENT-VALIDATION.md`, `board.json` | Revue QA et cohérence REQ-AP-01 à 05 avant développement dépendant. |
| 2. Domaine et interface | Backend : `ValidateRegistration.php`, `Staff/Registrations/Index.php` ; frontend : `staff/registrations/index.blade.php`, les quatre e-mails `created`, `accepted`, `tech-inspection-completed`, `engagement-signed` ; contrôle de la vue pilote existante | Acceptation manuelle impayée, trace et textes exacts ; « À régler » sans ligne Payment ; aucun `paid` artificiel. |
| 3. Tests et revue | Auteur backend : tests use case/Livewire/e-mail/paiement et e-mail de création différé avec tarif de course distinct ; QA indépendante : revue diff et négatifs | Tests ciblés, permission et non-rétrogradation démontrées ; absence de reçu avant paiement ; e-mail exact. |
| 4. Prépublication | Coordinateur/DevOps : `audit-sites` points touchés et critiques, CI, comparaison SHA et sauvegarde | Décision de publication fondée sur état réel ; P0 antérieurs traités ou explicitement bloquants. |

Migration de schéma : **sans objet**, car statuts, `validated_at/by`, paiements et `activity_log` existent déjà. Retour arrière applicatif : rétablir le commit précédent en conservant les inscriptions validées ; ne jamais annuler automatiquement une acceptation ou un paiement. Régression à examiner sur les inscriptions acceptées avant règlement pendant un rollback.

## PAY-EXPIRED-01 — Itération ciblée (29/10/2026, v1.3)

Carte canonique : `PAY-EXPIRED-01` dans `board.json`. Après revue des six contrats : backend propriétaire de la reprise, de la clôture sûre des sessions expirées et du blocage des paiements Stripe confirmés non rapprochés, ainsi que des tests ; frontend propriétaire de la vue pilote ; QA distinct contrôle les états Stripe et la non-duplication ; coordinateur intègre et vérifie l'audit `audit-sites` des points touchés/critique avant toute publication. `PAY-RECONCILE-01` suit séparément le rapprochement d'un paiement historique avec sa date Stripe réelle et l'examen des webhooks. Tester avec Stripe simulé, sans débit réel. Migration : sans objet pour PAY-EXPIRED-01, colonnes et statuts existants. Retour arrière applicatif : revenir au code précédent tout en conservant les paiements clôturés, jamais réouvrir une session Stripe expirée. La correction opérationnelle du dossier de novembre est distincte de cette release.

La configuration de l'endpoint Stripe (événements Checkout complété/expiré) est une opération de production séparée du code local ; relire les événements abonnés et vérifier une livraison réelle avant de conclure. La revue de code couvre aussi le webhook d'expiration, la concurrence avec un autre paiement `paid`, le statut sportif changé et le tarif affiché. `PAY-REVOKE-01` suit les anciens liens Stripe encore ouverts après refus, annulation ou autre paiement.
