# APPFLOW — REG-UNPAID-01

Version de cadrage du 28/10/2026, base locale `1aaa13c`.

1. **Pilote :** inscription à la course → `PENDING_PAYMENT` et montant dû. Il peut payer en ligne immédiatement ou attendre la décision staff.
2. **Staff autorisé :** liste des inscriptions → bouton « Accepter avant paiement » sur `PENDING_PAYMENT` → fenêtre indiquant « inscription acceptée, paiement encore dû ; règlement possible aux VA/VT » → confirmer ou annuler. En chargement, éviter le double clic.
3. **Succès :** `ACCEPTED` et historique de validation ; paiement inchangé. Le staff et le pilote voient le montant restant dû même sans ligne Payment. Notification d'acceptation avec paiement restant dû.
4. **Règlement ultérieur :** Stripe par le pilote tant que le statut est exactement `ACCEPTED`, ou encaissement manuel par rôle paiement même après une étape de contrôle engagée → paiement `paid`, confirmation/reçu ordinaires ; l'inscription conserve son statut sportif. Si Stripe est déjà `pending/processing`, le staff vérifie ou résout cet état avant l'encaissement manuel pour éviter un double paiement.
5. **États alternatifs :** si le paiement a été reçu entre l'ouverture de la fenêtre et la confirmation, validation ordinaire ; si l'inscription est déjà acceptée, refusée ou annulée, message d'état et aucune nouvelle notification. Un rôle sans permission obtient 403. Le refus d'inscription reste possible depuis `PENDING_VALIDATION` selon la règle existante.

Liens : REQ-AP-01 à 05 dans [PRD](PRD.md), interface dans [UI-UX-BRIEF](UI-UX-BRIEF.md), données dans [BACKEND-SCHEMA](BACKEND-SCHEMA.md), vérifications dans [IMPLEMENTATION-PLAN](IMPLEMENTATION-PLAN.md).
