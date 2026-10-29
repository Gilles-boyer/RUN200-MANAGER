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

## PAY-EXPIRED-01 — Registre complémentaire du 29/10/2026

Les validations REG-UNPAID-01 ci-dessus sont historiques et ne couvrent pas cette itération. La décision produit humaine est la demande de vérifier le paiement bloqué et de proposer une correction ; le dossier de novembre a été débloqué après contrôle direct Stripe (`expired`, `unpaid`, aucun PaymentIntent), sans créer de nouvelle session. La publication du correctif général suivra sa propre vérification.

| Livrable/source | Version examinée | Auteur | Relecteur | Date/environnement | Verdict | Preuves et points ouverts |
| --- | --- | --- | --- | --- | --- | --- |
| [PRD](PRD.md) | PAY-EXPIRED-01 v1.3 | Coordinateur PO | stripe_ux_qa | 29/10, code local et statut Stripe prod | Validé pour ce périmètre | REQ-PE-01..04, action pilote, pas de nouvelle session automatique. |
| [TRD](TRD.md) | PAY-EXPIRED-01 v1.3 | Coordinateur architecture | stripe_ux_qa | 29/10, local | Validé pour ce périmètre | Appel Stripe hors verrou, unicité et contrôles de concordance. |
| [APPFLOW](APPFLOW.md) | PAY-EXPIRED-01 v1.3 | Coordinateur UX | stripe_ux_qa | 29/10, local | Validé pour ce périmètre | Open, expired, paid, incertain, statut sportif et autre pilote. |
| [UI/UX DESIGN BRIEF](UI-UX-BRIEF.md) | PAY-EXPIRED-01 v1.3 | Coordinateur UX | stripe_ux_qa | 29/10, local | Validé pour ce périmètre | Rerender et message français ; écran réel après code à vérifier. |
| [IMPLEMENTATION PLAN](IMPLEMENTATION-PLAN.md) | PAY-EXPIRED-01 v1.3 | Coordinateur chef de projet | stripe_ux_qa | 29/10, local | Validé pour ce périmètre | Tests Stripe simulés et QA indépendante requis. |
| [BACKEND SCHEMA](BACKEND-SCHEMA.md) | PAY-EXPIRED-01 v1.3 | Coordinateur/backend | stripe_ux_qa | 29/10, local | Validé pour ce périmètre | Aucune migration ; absence d'unicité SQL compensée par garde à tester. |

Matrice : REQ-PE-01 → APPFLOW `open` → TRD reprise URL fraîche → carte PAY-EXPIRED-01 → test session ouverte ; REQ-PE-02 → APPFLOW expiration → BACKEND SCHEMA `pending→cancelled` → test expiration et double action ; REQ-PE-03 → APPFLOW payé/incertain → TRD contrôles et verrou → tests concordance, webhook et erreur API ; REQ-PE-04 → UI/UX DESIGN BRIEF → test rendu mobile et badge. Relecture IA ≠ validation de code, de déploiement ou de paiement réel.

**Revue complémentaire IA du 29/10 :** après le contrôle de 9 anciennes sessions locales pending (8 expirées/impayées et 1 complete/paid), la branche complete/paid a été limitée à un blocage sûr. Le rapprochement historique PAY-RECONCILE-01 préservera la date Stripe réelle. stripe_ux_qa a relu les six sections v1.2, y compris le plan corrigé, et les a validées pour le développement ciblé. Cette revue ne valide ni le code ni les webhooks de production.

**Revue finale IA du 29/10 :** stripe_ux_qa a relu les six sections v1.3 et le diff du correctif, y compris le webhook d'expiration ; 29 tests ciblés / 124 assertions passent en local avec Stripe simulé. git diff --check passe. L'abonnement du webhook Stripe de production a été corrigé séparément puis relu via l'API (un endpoint actif, événements checkout.session.completed et checkout.session.expired présents) ; aucune livraison de nouvel événement réel n'a encore été observée. QA valide la préparation d'une PR, pas le fonctionnement complet en production ni les anciennes URL Stripe ouvertes.
