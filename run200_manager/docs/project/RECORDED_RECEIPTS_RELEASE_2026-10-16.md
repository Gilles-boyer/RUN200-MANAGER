# Mise en service et campagne de reçus — 16 octobre 2026

## Décision et version

L'association a demandé l'envoi immédiat des reçus pour **tous les paiements marqués encaissés**, y compris les 75 paiements hors Stripe, aux adresses présentes dans les dossiers. Elle accepte plusieurs messages par pilote et indique avoir prévenu les utilisateurs. L'envoi n'a pas modifié `email_verified_at` ni ouvert le mode historique automatique `all`.

La PR [#24](https://github.com/Gilles-boyer/RUN200-MANAGER/pull/24) a été fusionnée dans `main` et déployée sur o2switch au SHA `165bd5a3543f2f9bd50411f4e3386aae0bde4965`. Les quatre contrôles CI étaient verts. Avant fusion, la suite locale passait **630 tests / 2 111 assertions**, les 69 tests ciblés **426 assertions**, ainsi que Pint et PHPStan ciblé. La QA indépendante a conclu GO sur le code après deux corrections de l'empreinte de revue et du montant. Les commandes de déploiement ont préservé les modifications déjà présentes dans le checkout serveur.

La sauvegarde SQL privée `/home/mepa3308/run200-receipts-pre-20261016.sql` a été créée avant envoi : `mysqldump` code 0, **11 815 460 octets**, permissions `0600`, hors racine web. Elle n'a pas fait l'objet d'un essai de restauration isolée ; la sauvegarde de la veille, oui. Aucun schéma de base n'a été migré pour cette PR. Le site est sorti du mode maintenance ; `GET /` et `GET /up` répondent **200** après la campagne. Le SHA Git relu sur le serveur est identique au SHA fusionné.

## Résultat en production

Un inventaire avant la campagne comptait **252 paiements `paid`**, dont **222 paiements liés à un compte dont l'adresse n'était pas vérifiée**, un reçu déjà `sent` et aucun état `sending`. La commande a d'abord envoyé deux reçus réels de petite taille : Stripe #27 et espèces #59, chacun à 100 €. Pour chacun, le SMTP a accepté un message, la base a enregistré `sent`, le destinataire correspondait au compte du pilote et le PDF généré avait une signature `%PDF-`. La réception en boîte de ces deux messages n'a pas été confirmée par les titulaires.

Une liste figée de **248 autres identifiants**, excluant les paiements déjà servis et le dossier retenu #226, a été traitée en 13 lots de 20 au maximum. Chaque sous-lot a exigé une simulation sans effet, **100 % éligible**, une empreinte HMAC de 64 caractères puis l'envoi avec la même empreinte. Les sorties ont confirmé le nombre de messages acceptés. La simulation du lot 8 à 20 identifiants s'est arrêtée avec le code 137 **avant tout envoi** ; la base indiquait alors zéro reçu pour ce lot et zéro état incertain. Le lot 8 et les suivants ont été traités par groupes de cinq, avec arrêt automatique sur anomalie. Toutes les sorties d'envoi suivantes ont eu le code 0 et le compte attendu.

| Méthode | Paiements `paid` | Reçus `sent` |
| --- | ---: | ---: |
| Stripe | 177 | 176 |
| CB sur place | 28 | 28 |
| Espèces | 37 | 37 |
| Manuel | 9 | 9 |
| Virement | 1 | 1 |
| **Total** | **252** | **251** |

Le rapprochement final en base trouve **251 reçus `sent`**, dont **250 nouveaux pendant cette campagne** et un déjà envoyé ; **zéro** `sending` ou `failed`. Les **251 lignes de reçus** ont une adresse de livraison concordante avec le compte pilote ; aucune date `email_sent_at` ni adresse de livraison n'est vide. La liste figée compte bien 248 identifiants, sans nouvel encaissement intégré pendant l'exécution. `sent` signifie que le transport SMTP a accepté le message : la remise effective dans chaque boîte et l'ouverture de chaque PDF ne sont pas démontrées.

## Dossier retenu et limites

Le paiement Stripe **#226** reste le seul `paid` sans reçu. Le PaymentIntent live est `succeeded`, le montant et la devise concordent, sa charge est payée et capturée sans litige. Ses métadonnées ne relient toutefois ni l'inscription ni le pilote ; la session Checkout enregistrée dans la base n'est ni celle de cet intent, ni terminée, ni payée. Une recherche Stripe par PaymentIntent n'a retourné aucune session Checkout liée. La preuve d'affectation de cet encaissement au bon dossier manque : **aucun reçu n'a été émis ni envoyé pour #226**. Le rapprocher manuellement avec Stripe et le dossier d'inscription avant toute correction ou envoi ; ne pas contourner le contrôle par simple changement de statut.

Les 75 lignes hors Stripe ont été traitées sur la foi du statut saisi dans l'application et des contrôles de cohérence des dossiers, conformément à l'instruction de l'association. Elles n'ont pas été rapprochées d'un registre de caisse ou d'un relevé bancaire. Les 222 paiements liés à un compte dont l'adresse n'était pas vérifiée au prévol ont pu donner lieu à un PDF envoyé à cette adresse, à la demande de l'association ; aucun compte n'a été marqué vérifié artificiellement. L'ancien identifiant Mailtrap exposé dans l'historique public n'est toujours pas confirmé révoqué. La purge 3/5/10 ans approuvée n'est pas encore mise en œuvre. L'audit des accès authentifiés au téléchargement des PDF et de la réception individuelle reste ouvert. **Le site n'est pas déclaré prêt au sens de l'audit `audit-sites`.**

La configuration active relue après envoi est `production`, `payment_receipts.auto_send=true`, mode historique automatique **`pilot`**. La commande de campagne exige toujours une liste explicite et une empreinte ; aucun lot automatique général n'a été activé. Le checkout serveur contient encore ses anciens fichiers modifiés/non suivis, préservés volontairement.
