# BACKEND SCHEMA — REG-UNPAID-01

Version de cadrage du 28/10/2026, base locale `1aaa13c`. Modèle physique inchangé ; sources canoniques : migrations `2026_01_23_160020_create_race_registrations_table.php`, `2026_01_23_160025_add_validation_fields_to_race_registrations_table.php`, modèle `RaceRegistration.php` et table `payments` existante.

- `race_registrations` relie course, pilote et voiture ; `status` est un état sportif. `validated_at` et `validated_by` enregistrent la décision staff. `reason` reste la raison du refus : ne pas le réutiliser comme preuve de paiement.
- `payments` conserve son propre `status`, `amount_cents`, `paid_at` et méthode. L'acceptation `PENDING_PAYMENT → ACCEPTED` ne touche aucune ligne paiement ; un reçu est rattaché exclusivement à un paiement confirmé par les flux existants.
- `activity_log` trace l'acceptation avec statut précédent, agent, et indication que le paiement était ou non confirmé lors de l'action. Cette trace n'est pas un encaissement.
- Contrat d'action : `ValidateRegistration::accept` autorise `PENDING_VALIDATION`; un chemin explicite autorise `PENDING_PAYMENT`. Refus, répétition, rôle sans `race_registration.validate` et état concurrent invalide ne modifient rien. Verrou transactionnel sur la ligne d'inscription ; paiement tardif conserve le statut sportif avancé.
- E-mail : lire l'état réel de paiement au rendu et choisir l'une des deux formulations. API/route supplémentaire : **sans objet**, l'interface passe par l'action Livewire existante. Index/migration : **sans objet** pour cette petite transition ; vérifier les requêtes existantes.

La structure des montants de paiement et l'unicité des références Stripe restent des chantiers distincts de l'[audit du 26 octobre](AUDIT_PRIORITES_2026-10-26.md).

## PAY-VOID-01 — État et historique (06/11/2026, cadrage v0.5)

Étendre le snapshot attendu à `receipt_id`, `receipt_email_status`, `email_sent_at` (null si absent), en plus des champs paiement/inscription. Comparer sous verrou la présence et l'état du reçu pour éviter de confirmer « non envoyé » après début d'un envoi ou émission concurrente.

Transition liée sur `race_registrations.status` : `PENDING_VALIDATION → PENDING_PAYMENT` seulement si le paiement annulé laisse zéro encaissement actif ; `ACCEPTED` et les états de checkpoints restent inchangés. Relire les paiements dans la même transaction avant cette transition.

Snapshot de décision attendu : `payment_id`, `registration_id`, `pilot_id`, `status`, `method`, `amount_cents`. Comparer sous verrou aux lignes `race_registrations` et `payments` avant `paid → cancelled` ; une réaffectation de pilote est un conflit, pas une correction silencieuse.

Entités existantes : `payments` (inscription, méthode, statut, montant, `paid_at`, identifiants Stripe, `metadata`), `payment_receipts` (snapshot et état d'envoi), `activity_log`. Transition spécifique proposée : `paid → cancelled` uniquement pour une saisie locale déclarée non encaissée, autorisée et motivée. Le paiement demeure identifié ; le montant, la méthode et `paid_at` originaux restent pour expliquer l'erreur historique, sans compter comme encaissement actif ni être affichés « Payé le ». Conserver l'acteur, l'horodatage et le motif de correction dans la trace explicite ; sous verrou, un reçu non envoyé `pending`/`not_requested` devient `voided`, hors liste d'envoi. Un reçu `sending` ou `failed` rend l'effet d'un e-mail incertain et bloque l'annulation automatique. Ne pas remplacer une trace `sent` par `pending` : le PDF déjà reçu reste matériellement existant même si l'application refuse un nouveau téléchargement après annulation ; inscrire alors dans `payments.metadata` un indicateur `void_receipt_followup_required=true`, affiché durablement au staff.

Contrat d'action : entrée `registration_id`, `payment_id`, motif et acteur authentifié ; sortie statut corrigé et total encaissé recalculé ; erreurs 403 (permission), 404 (paiement hors dossier), validation métier (Stripe lié, état non `paid`, motif invalide). Transaction avec verrou et contrôle de la ligne, aucun appel tiers. Attribuer `payment.void` à `ADMIN` via migration Spatie idempotente et ajouter cette permission au seeder ; ne pas l'attribuer à `STAFF_ADMINISTRATIF` par sa permission `payment.manage`. Migration de schéma de paiement sans objet car statut, metadata et activité existent.
