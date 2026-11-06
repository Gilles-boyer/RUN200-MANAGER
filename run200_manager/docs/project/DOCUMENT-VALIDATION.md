# Registre de validation — REG-UNPAID-01

Périmètre : acceptation manuelle d'une inscription avant paiement, demande utilisateur du 28/10/2026. Les six documents ci-dessous sont nouveaux et limités à cette itération ; leur création ne valide aucun ancien dossier du projet. Base de lecture `1aaa13c`.

| Livrable/source | Version examinée | Auteur | Relecteur | Date/environnement | Verdict | Preuves et points ouverts |
| --- | --- | --- | --- | --- | --- | --- |
| [PRD](PRD.md) | 2026-10-28 v1.3 | Coordinateur PO | registration_qa | 28/10, local | Validé pour ce périmètre | REQ-AP-01..05 ; e-mail de création : tarif de course et statut non périmé. |
| [TRD](TRD.md) | 2026-10-28 v1.3 | Coordinateur/architecture | registration_qa | 28/10, local | Validé pour ce périmètre | Quatre e-mails ; tarif course et envoi différé pris en compte. |
| [APPFLOW](APPFLOW.md) | 2026-10-28 v1.1 | Coordinateur UX | registration_qa | 28/10, local | Validé pour ce périmètre | Cas succès/erreur/concurrence ; Stripe borné à ACCEPTED, manuel après vérification. |
| [UI/UX DESIGN BRIEF](UI-UX-BRIEF.md) | 2026-10-28 v1.4 | Coordinateur UX | registration_qa et unpaid_ux_review | 28/10, local | Validé pour ce périmètre | Solde après paiement partiel relu ; QA 9/9 tests ciblés, 35 assertions. Remboursement partiel ou paiement plus récent explicitement hors périmètre. |
| [IMPLEMENTATION PLAN](IMPLEMENTATION-PLAN.md) | 2026-10-28 v1.3 | Coordinateur chef de projet | registration_qa | 28/10, local | Validé pour ce périmètre | Test ciblé e-mail de création différé et tarif course ajouté. |
| [BACKEND SCHEMA](BACKEND-SCHEMA.md) | 2026-10-28 v1.1 | Coordinateur/backend | registration_qa | 28/10, local | Validé pour ce périmètre | Schéma inchangé, transition et audit ; absence de migration justifiée. |

**Décision humaine :** l'utilisateur demande explicitement l'acceptation au cas par cas avant paiement, avec confirmation manuelle ultérieure lors des VA/VT. Les détails ordinaires de confirmation UI et de traçabilité sont proposés sous ce mandat. **Décision externe :** aucune publication n'est validée par ce registre. La revue IA doit être datée et détaillée avant le code dépendant.

**Revue documentaire IA du 28/10 :** `registration_qa` a comparé les six documents v1.1 au code `1aaa13c` et aux critères REQ-AP-01..05. `unpaid_ux_review` a relevé trois effets de chaîne intégrés : autres e-mails, état « À régler », paiement Stripe en cours. Le contrôle de l'e-mail de création a entraîné la version v1.2 et invalidé la revue des quatre rubriques concernées jusqu'à une nouvelle lecture. Aucune revue documentaire ne prouve le code à écrire ni une autorisation de publication.

**Revue complémentaire IA du 28/10 :** `registration_qa` a relu et validé les quatre rubriques v1.3 modifiées après découverte du tarif de course et du message différé. APPFLOW et BACKEND SCHEMA v1.1 restent inchangés et validés. Les critères testables couvrent les quatre e-mails, les rôles, la transition, les paiements tardifs et l'absence de faux reçu. Cette validation autorise la préparation du code local, pas une publication.

**Revue UI v1.4 du 28/10 :** après un test de paiement partiel, `registration_qa` a relu le calcul du solde dans le message de confirmation et les deux vues staff. Le brief borné au dernier paiement `paid` est cohérent avec ce code. Le cas `PARTIALLY_REFUNDED` ou d'un statut plus récent reste une limite à traiter séparément. La revue reste locale ; elle ne prouve ni le navigateur ni le serveur publié.

## PAY-VOID-01 — Registre de cadrage du 06/11/2026

La demande humaine autorise la correction d'un paiement attribué à tort et la préparation d'une action staff pour les erreurs de saisie. Le relevé de production communiqué par le coordinateur indique une inscription `ACCEPTED`, une écriture en espèces `paid` sans référence Stripe et un reçu `pending` sans date d'envoi. Le coordinateur reste responsable de la vérification et de la mutation exactes en production. La revue documentaire IA ci-dessous ne valide ni code, ni interface, ni déploiement, ni les itérations précédentes.

| Livrable/source | Version examinée | Auteur | Relecteur | Date/environnement | Verdict | Preuves et points ouverts |
| --- | --- | --- | --- | --- | --- | --- |
| [PRD](PRD.md) | PAY-VOID-01 v0.4 | payment_contracts (PO) | payment_qa | 06/11, code local + relevé prod anonymisé | Validé pour ce périmètre | REQ-PV-05, liste staff et exception de statut ; revue IA documentaire favorable. |
| [TRD](TRD.md) | PAY-VOID-01 v0.5 | payment_contracts (PO) | payment_qa | 06/11, code local | Validé pour ce périmètre | Snapshot du reçu, comparaison sous verrou et exception de statut. |
| [APPFLOW](APPFLOW.md) | PAY-VOID-01 v0.4 | payment_contracts (PO) | payment_qa | 06/11, local | Validé pour ce périmètre | Retour liste bureau/mobile et transition conditionnelle de statut. |
| [UI/UX DESIGN BRIEF](UI-UX-BRIEF.md) | PAY-VOID-01 v0.5 | payment_contracts (PO) | payment_qa | 06/11, local | Validé pour ce périmètre | Message de conflit sur reçu + badge liste ; rendu réel encore à vérifier. |
| [IMPLEMENTATION PLAN](IMPLEMENTATION-PLAN.md) | PAY-VOID-01 v0.5 | payment_contracts (PO) | payment_qa | 06/11, local | Validé pour ce périmètre | Tests du reçu changé et liste bureau/mobile prévus ; exécution séparée. |
| [BACKEND SCHEMA](BACKEND-SCHEMA.md) | PAY-VOID-01 v0.5 | payment_contracts (PO) | payment_qa | 06/11, code local | Validé pour ce périmètre | Snapshot du reçu et transition conditionnelle cohérents. |

Matrice : REQ-PV-01 → APPFLOW succès → TRD transition verrouillée → carte PAY-VOID-01 → test solde/statut ; REQ-PV-02 → APPFLOW refus → BACKEND SCHEMA gardes → tests Stripe/permission/concurrence ; REQ-PV-03 → UI reçu → TRD gel d'envoi et journal → tests reçu pending/sending/sent ; REQ-PV-04 → UI solde dû → plan QA parcours pilote/staff. Une revue IA, une modification de la base et une publication sont trois événements distincts.

**Verdict documentaire IA du 06/11 :** payment_qa a relu v0.3 des six contrats après intégration des points v0.1/v0.2 ; cohérence favorable au développement ciblé, sans contradiction bloquante. Ce verdict ne prouve aucun test de code, rendu réel ou effet en production. La décision humaine explicite porte sur la correction de l'écriture signalée et l'ajout d'un bouton ; les détails de sécurité sont cadrés par le coordinateur dans ce mandat.

**Complément v0.4 :** le retour vers « Toutes les inscriptions » a révélé un badge de synthèse pouvant afficher le montant de l'écriture annulée. REQ-PV-05 borne le libellé bureau/mobile. Les six contrats précisent l'exception étroite `PENDING_VALIDATION → PENDING_PAYMENT` lorsque plus aucun encaissement ne subsiste ; `ACCEPTED` et ses checkpoints restent inchangés. Le code et le rendu réel demandent une revue distincte de cette relecture documentaire.

**Réserve QA v0.4 levée en v0.5 :** le modal nomme l'état du reçu, mais son snapshot ne le figeait pas. TRD, BACKEND SCHEMA, UI/UX et plan exigent désormais la comparaison du reçu sous verrou et un message de conflit. PRD et APPFLOW v0.4 restent inchangés ; QA a relu les quatre rubriques modifiées.

**Verdict documentaire IA final du 06/11 :** payment_qa a relu PRD/APPFLOW v0.4 et TRD/UI/PLAN/SCHEMA v0.5 après correction de ses réserves. Cohérence favorable au développement ciblé ; aucun défaut documentaire bloquant relevé. Ce verdict ne certifie ni code, ni tests exécutés, ni rendu navigateur, ni production.
