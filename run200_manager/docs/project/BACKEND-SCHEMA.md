# BACKEND SCHEMA — REG-UNPAID-01

Version de cadrage du 28/10/2026, base locale `1aaa13c`. Modèle physique inchangé ; sources canoniques : migrations `2026_01_23_160020_create_race_registrations_table.php`, `2026_01_23_160025_add_validation_fields_to_race_registrations_table.php`, modèle `RaceRegistration.php` et table `payments` existante.

- `race_registrations` relie course, pilote et voiture ; `status` est un état sportif. `validated_at` et `validated_by` enregistrent la décision staff. `reason` reste la raison du refus : ne pas le réutiliser comme preuve de paiement.
- `payments` conserve son propre `status`, `amount_cents`, `paid_at` et méthode. L'acceptation `PENDING_PAYMENT → ACCEPTED` ne touche aucune ligne paiement ; un reçu est rattaché exclusivement à un paiement confirmé par les flux existants.
- `activity_log` trace l'acceptation avec statut précédent, agent, et indication que le paiement était ou non confirmé lors de l'action. Cette trace n'est pas un encaissement.
- Contrat d'action : `ValidateRegistration::accept` autorise `PENDING_VALIDATION`; un chemin explicite autorise `PENDING_PAYMENT`. Refus, répétition, rôle sans `race_registration.validate` et état concurrent invalide ne modifient rien. Verrou transactionnel sur la ligne d'inscription ; paiement tardif conserve le statut sportif avancé.
- E-mail : lire l'état réel de paiement au rendu et choisir l'une des deux formulations. API/route supplémentaire : **sans objet**, l'interface passe par l'action Livewire existante. Index/migration : **sans objet** pour cette petite transition ; vérifier les requêtes existantes.

La structure des montants de paiement et l'unicité des références Stripe restent des chantiers distincts de l'[audit du 26 octobre](AUDIT_PRIORITES_2026-10-26.md).
