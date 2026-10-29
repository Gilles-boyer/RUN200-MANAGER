# PRD — REG-UNPAID-01, acceptation avant paiement

Version de cadrage du 28/10/2026, base locale `1aaa13c`. Périmètre limité à la demande de l'organisateur : accepter au cas par cas une inscription dont le paiement sera encaissé plus tard, notamment lors des VA/VT. Le reste du [brief](BRIEF.md) demeure source historique, sans validation rétrospective.

| Exigence | Critère observable |
| --- | --- |
| REQ-AP-01 | Un agent doté de `race_registration.validate` peut accepter une inscription `PENDING_PAYMENT` depuis la liste staff, après confirmation explicite du paiement restant dû. |
| REQ-AP-02 | L'inscription devient `ACCEPTED` avec `validated_at`, `validated_by` et journal d'activité ; aucune ligne de paiement n'est créée ou marquée `paid` par cette décision et aucun reçu n'est émis. |
| REQ-AP-03 | Le pilote et le staff voient séparément « acceptée » et le paiement restant dû, y compris en l'absence de ligne `payments` ; les e-mails de création, d'acceptation, de contrôle technique et de signature expliquent le parcours et ne prétendent pas qu'un paiement non encaissé est effectué. L'E-Card est annoncée disponible dès l'acceptation ; l'e-mail de création n'affiche ni statut périmé ni tarif autre que celui de la course. |
| REQ-AP-04 | Un paiement manuel ou Stripe enregistré ensuite conserve le statut d'inscription avancé et suit son flux normal de confirmation et de reçu. |
| REQ-AP-05 | Les rôles sans permission, statuts déjà validés/refusés/annulés et confirmations répétées ne produisent pas d'acceptation. |

L'acceptation donne les accès normalement liés à `ACCEPTED` (E-Card, paddock, VA/VT), avec indication « NON PAYÉ » au scanner jusqu'à encaissement. La demande de l'utilisateur couvre cette exception de validation ; aucun changement de tarif, de droit à remboursement ou de politique d'encaissement n'est demandé. Les dates et montants de paiement restent établis uniquement par les flux de paiement existants.

Un paiement Stripe déjà en cours doit être vérifié/résolu avant un encaissement manuel aux VA/VT afin d'éviter un double paiement. Le paiement en ligne est disponible tant que l'inscription reste exactement `ACCEPTED` ; la possibilité de l'étendre aux statuts de contrôle ultérieurs est hors de cette itération.

## PAY-EXPIRED-01 — Reprise d'un paiement Stripe (29/10/2026, v1.3)

Constat vérifié sur un dossier de novembre : une tentative locale restait `pending` alors que Stripe indiquait `expired`, `unpaid` et aucun PaymentIntent. Après ce contrôle, la tentative locale a été clôturée ; aucun nouveau paiement n'a été créé. Les identités et URL Stripe ne sont pas conservées dans ce contrat.

| Exigence | Critère observable |
| --- | --- |
| REQ-PE-01 | Une session Stripe encore `open` se reprend via son URL actuelle. |
| REQ-PE-02 | Une session `expired` et `unpaid`, sans intention de paiement, est clôturée localement après contrôle serveur ; le pilote peut alors démarrer une nouvelle tentative. |
| REQ-PE-03 | Une session `complete` et `paid` bloque tout second paiement et affiche qu'un rapprochement par l'organisation est en cours. Le rapprochement historique doit préserver la date réelle d'encaissement et sera traité séparément. Une session en traitement, ambiguë ou inaccessible reste bloquée sans mutation financière. |
| REQ-PE-04 | Le pilote reçoit un message français utile, voit « En attente de paiement » à la place du code `PENDING_PAYMENT`, et le montant de la tentative Stripe encore ouverte plutôt qu'un tarif de course éventuellement modifié. |

Le correctif ne modifie ni le tarif ni la possibilité d'accepter l'inscription avant paiement. Le nouveau paiement est proposé seulement si l'inscription reste `PENDING_PAYMENT` ou `ACCEPTED`. Il ne lance jamais un nouveau Checkout automatiquement sans action du pilote.
