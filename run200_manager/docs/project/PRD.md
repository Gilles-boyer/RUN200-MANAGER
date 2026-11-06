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

## PAY-VOID-01 — Annulation d'un paiement saisi par erreur (06/11/2026, cadrage v0.4)

Précision de concurrence : pilote, méthode et montant affichés à l'ouverture doivent encore correspondre à la confirmation. Une E-Card différée déclenchée par le paiement annulé ne doit plus partir.

Demande humaine : corriger un paiement attribué à tort à un pilote, puis donner au responsable autorisé un bouton pour corriger les mêmes erreurs de saisie. Le dossier et son paiement exact seront identifiés et contrôlés en production avant toute mutation. Le terme « annuler » désigne ici l'invalidation d'une écriture locale **sans encaissement réel** ; un paiement Stripe reçu ou un paiement réellement encaissé relève d'un autre parcours de rapprochement/remboursement.

| Exigence | Critère observable |
| --- | --- |
| REQ-PV-01 | Un administrateur autorisé peut invalider un paiement local `paid` saisi à tort pour la bonne inscription, avec motif obligatoire et confirmation explicite ; le paiement reste dans l'historique comme `cancelled` et ne compte plus dans le solde encaissé. Si l'inscription est encore `PENDING_VALIDATION` et qu'aucun autre paiement n'est encaissé, elle revient à `PENDING_PAYMENT` pour permettre le règlement. Une inscription déjà `ACCEPTED` et ses checkpoints ne sont pas rétrogradés. |
| REQ-PV-02 | Un paiement Stripe, une référence Stripe présente, un paiement remboursé/partiellement remboursé, un dossier étranger, une seconde action et un état modifié entre ouverture et confirmation sont refusés sans effet. Aucun remboursement ni nouvel encaissement n'est lancé. |
| REQ-PV-03 | L'acteur, l'heure, le paiement exact et le motif sont conservés dans une trace d'audit durable. Montant, méthode, date originale et ancien reçu éventuel restent consultables dans l'historique autorisé. Un reçu encore `pending` est invalidé avant tout envoi ; un reçu déjà `sent` n'est jamais présenté comme effacé ou repris chez le destinataire. Un état d'envoi incertain bloque l'annulation automatique jusqu'au rapprochement. |
| REQ-PV-04 | Le staff et le pilote voient ensuite le règlement restant dû. La date `paid_at` d'une écriture annulée est libellée comme saisie historique erronée, jamais « Payé le ». Si un reçu avait été envoyé, l'action de régularisation reste visible dans le dossier jusqu'à traitement ; aucun nouveau reçu n'est émis pour l'annulation. |
| REQ-PV-05 | Dans « Toutes les inscriptions », si aucune somme n'est encaissée mais qu'une saisie de paiement a été annulée, les vues bureau et mobile affichent « Non payé · saisie annulée » sans accoler le montant de l'écriture annulée. L'historique détaillé conserve ce montant et la trace de correction. |

Le cas réel relevé en production concerne une écriture en espèces `paid` sans référence Stripe et un reçu `pending` non envoyé au moment du contrôle. Cette observation ne prouve pas à elle seule l'absence de paiement physique : l'utilisateur la confirme expressément. La permission dédiée et le traitement des reçus déjà envoyés ont été inclus dans la revue de cohérence de cette itération. Aucune règle fiscale n'est déduite de cette demande.
