# IMPLEMENTATION PLAN — REG-UNPAID-01

Version de cadrage du 28/10/2026, base locale `1aaa13c`. Carte Kanban : `REG-UNPAID-01`.

| Étape | Propriétaire / fichiers | Sortie vérifiable |
| --- | --- | --- |
| 1. Contrats | Coordinateur : six documents de cette itération, `DOCUMENT-VALIDATION.md`, `board.json` | Revue QA et cohérence REQ-AP-01 à 05 avant développement dépendant. |
| 2. Domaine et interface | Backend : `ValidateRegistration.php`, `Staff/Registrations/Index.php` ; frontend : `staff/registrations/index.blade.php`, les quatre e-mails `created`, `accepted`, `tech-inspection-completed`, `engagement-signed` ; contrôle de la vue pilote existante | Acceptation manuelle impayée, trace et textes exacts ; « À régler » sans ligne Payment ; aucun `paid` artificiel. |
| 3. Tests et revue | Auteur backend : tests use case/Livewire/e-mail/paiement et e-mail de création différé avec tarif de course distinct ; QA indépendante : revue diff et négatifs | Tests ciblés, permission et non-rétrogradation démontrées ; absence de reçu avant paiement ; e-mail exact. |
| 4. Prépublication | Coordinateur/DevOps : `audit-sites` points touchés et critiques, CI, comparaison SHA et sauvegarde | Décision de publication fondée sur état réel ; P0 antérieurs traités ou explicitement bloquants. |

Migration de schéma : **sans objet**, car statuts, `validated_at/by`, paiements et `activity_log` existent déjà. Retour arrière applicatif : rétablir le commit précédent en conservant les inscriptions validées ; ne jamais annuler automatiquement une acceptation ou un paiement. Régression à examiner sur les inscriptions acceptées avant règlement pendant un rollback.
