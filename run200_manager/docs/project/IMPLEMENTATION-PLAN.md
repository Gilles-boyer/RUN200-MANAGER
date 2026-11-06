# IMPLEMENTATION PLAN — REG-UNPAID-01

Version de cadrage du 28/10/2026, base locale `1aaa13c`. Carte Kanban : `REG-UNPAID-01`.

| Étape | Propriétaire / fichiers | Sortie vérifiable |
| --- | --- | --- |
| 1. Contrats | Coordinateur : six documents de cette itération, `DOCUMENT-VALIDATION.md`, `board.json` | Revue QA et cohérence REQ-AP-01 à 05 avant développement dépendant. |
| 2. Domaine et interface | Backend : `ValidateRegistration.php`, `Staff/Registrations/Index.php` ; frontend : `staff/registrations/index.blade.php`, les quatre e-mails `created`, `accepted`, `tech-inspection-completed`, `engagement-signed` ; contrôle de la vue pilote existante | Acceptation manuelle impayée, trace et textes exacts ; « À régler » sans ligne Payment ; aucun `paid` artificiel. |
| 3. Tests et revue | Auteur backend : tests use case/Livewire/e-mail/paiement et e-mail de création différé avec tarif de course distinct ; QA indépendante : revue diff et négatifs | Tests ciblés, permission et non-rétrogradation démontrées ; absence de reçu avant paiement ; e-mail exact. |
| 4. Prépublication | Coordinateur/DevOps : `audit-sites` points touchés et critiques, CI, comparaison SHA et sauvegarde | Décision de publication fondée sur état réel ; P0 antérieurs traités ou explicitement bloquants. |

Migration de schéma : **sans objet**, car statuts, `validated_at/by`, paiements et `activity_log` existent déjà. Retour arrière applicatif : rétablir le commit précédent en conservant les inscriptions validées ; ne jamais annuler automatiquement une acceptation ou un paiement. Régression à examiner sur les inscriptions acceptées avant règlement pendant un rollback.

## PAY-VOID-01 — Correction de saisie (06/11/2026, cadrage v0.5)

QA teste aussi le changement d'état de `payment_receipts` entre ouverture du modal et confirmation : création du reçu, `pending→sending` et horodatage d'envoi. Chaque conflit conserve le paiement et le reçu et demande de recharger le dossier.

Inclure dans les travaux backend le snapshot de confirmation et le garde du listener `SendECardAfterPayment` en queue ; QA teste la modification de montant/méthode/pilote pendant le modal et l'événement de paiement exécuté après annulation.

Carte canonique à créer par le coordinateur dans `board.json`. Séquence : (1) relever en lecture seule le dossier réel, sa concordance avec la déclaration utilisateur d'absence d'encaissement et l'état du reçu ; (2) revue indépendante des six contrats ; (3) backend : cas d'usage transactionnel, migration Spatie `payment.void` attribuée à ADMIN, seeder canonique, garde Stripe, verrou du reçu, indicateur de suivi et tests ; (4) frontend : bouton, fenêtre, erreurs, date historique et état de reçu ; (5) QA indépendante sur scénario enregistré à tort, autre dossier, reçu en cours/envoyé, double clic et permission ; (6) audit `audit-sites`, CI, version serveur, sauvegarde, contrôle de la permission effective et vérification en production. La correction du dossier réel doit se baser sur l'état actuellement vérifié et sa propre trace, pas sur un test de création d'un nouveau paiement.

Migration de schéma de paiement : **sans objet**, champs et statut existants. Migration de données de permission : **nécessaire**, suivant `2026_01_25_100001_add_payment_permissions.php` et le seeder ; vérifier l'attribution `ADMIN` après exécution. Retour arrière du code : conserver l'écriture `cancelled` et son audit ; ne jamais restaurer automatiquement une fausse déclaration de paiement. Aucun débit réel pour les tests.

Complément REQ-PV-05 : frontend propriétaire de la liste bureau/mobile des inscriptions ; calculer le badge depuis les paiements déjà chargés pour la liste, sans requête par ligne. QA vérifie qu'une écriture annulée n'apparaît plus comme montant encaissé dans les deux variantes et qu'un paiement réel ultérieur retrouve son affichage normal. Revoir le point audit-sites de lisibilité/erreur financière avant publication.

QA vérifie également l'exception de statut REQ-PV-01 : `PENDING_VALIDATION` sans autre somme encaissée revient à `PENDING_PAYMENT`, tandis qu'une inscription `ACCEPTED` conserve ses checkpoints après annulation de l'écriture erronée.
