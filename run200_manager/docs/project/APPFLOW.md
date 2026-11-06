# APPFLOW — REG-UNPAID-01

Version de cadrage du 28/10/2026, base locale `1aaa13c`.

1. **Pilote :** inscription à la course → `PENDING_PAYMENT` et montant dû. Il peut payer en ligne immédiatement ou attendre la décision staff.
2. **Staff autorisé :** liste des inscriptions → bouton « Accepter avant paiement » sur `PENDING_PAYMENT` → fenêtre indiquant « inscription acceptée, paiement encore dû ; règlement possible aux VA/VT » → confirmer ou annuler. En chargement, éviter le double clic.
3. **Succès :** `ACCEPTED` et historique de validation ; paiement inchangé. Le staff et le pilote voient le montant restant dû même sans ligne Payment. Notification d'acceptation avec paiement restant dû.
4. **Règlement ultérieur :** Stripe par le pilote tant que le statut est exactement `ACCEPTED`, ou encaissement manuel par rôle paiement même après une étape de contrôle engagée → paiement `paid`, confirmation/reçu ordinaires ; l'inscription conserve son statut sportif. Si Stripe est déjà `pending/processing`, le staff vérifie ou résout cet état avant l'encaissement manuel pour éviter un double paiement.
5. **États alternatifs :** si le paiement a été reçu entre l'ouverture de la fenêtre et la confirmation, validation ordinaire ; si l'inscription est déjà acceptée, refusée ou annulée, message d'état et aucune nouvelle notification. Un rôle sans permission obtient 403. Le refus d'inscription reste possible depuis `PENDING_VALIDATION` selon la règle existante.

Liens : REQ-AP-01 à 05 dans [PRD](PRD.md), interface dans [UI-UX-BRIEF](UI-UX-BRIEF.md), données dans [BACKEND-SCHEMA](BACKEND-SCHEMA.md), vérifications dans [IMPLEMENTATION-PLAN](IMPLEMENTATION-PLAN.md).

## PAY-VOID-01 — Parcours staff (06/11/2026, cadrage v0.4)

La confirmation transmet la version affichée du paiement et du pilote ; si le montant, la méthode, le statut ou le pilote a changé, le staff recharge le dossier au lieu d'annuler l'ancienne version.

Administrateur autorisé → dossier d'inscription → ligne du paiement local `paid` → « Annuler ce paiement saisi par erreur » → fenêtre montrant pilote, course, montant, méthode et avertissement sur reçu déjà émis/envoyé → motif obligatoire → confirmation. Pendant le traitement, bloquer le double clic. Succès : ligne `Annulé`, ancien montant exclu du total encaissé, solde restant dû visible, trace d'audit ; date d'enregistrement antérieure marquée erronée ; reçu `pending` invalidé sans e-mail. Si reçu déjà `sent` : afficher durablement « Reçu envoyé : régularisation à faire » dans le dossier, sans annoncer que le pilote ne l'a plus. Si reçu `sending`/`failed`, si l'état, le titulaire ou la méthode change, ou si Stripe est lié : expliquer simplement le refus sans mutation et orienter vers un responsable. Absence de permission : 403. Une inscription encore `PENDING_VALIDATION` sans autre encaissement revient à `PENDING_PAYMENT` ; `ACCEPTED` et les checkpoints restent inchangés. Le paiement réel futur suit le flux normal.

Retour vers « Toutes les inscriptions » : le staff voit sur bureau et mobile « Non payé · saisie annulée » si aucune somme n'est encaissée. Le montant erroné reste accessible dans l'historique du paiement, sans figurer dans le badge de synthèse. Un paiement réel ultérieur rétablit le statut de paiement calculé selon les règles ordinaires.
