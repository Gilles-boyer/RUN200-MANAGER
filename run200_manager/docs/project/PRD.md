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
