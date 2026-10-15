# Pilote des reçus historiques — état livré le 15 octobre 2026

## Version et portée

La PR [#22](https://github.com/Gilles-boyer/RUN200-MANAGER/pull/22) est fusionnée et déployée sur `https://run200cfg.clicksolutions.re/` au SHA `150aa63b801b0223810a5dc42d96c86da54b9545`. Les quatre contrôles CI de la PR sont passés. La suite locale a passé 617 tests et 2 026 assertions ; PHPStan, Pint ciblé et compilation des vues ont réussi. La revue QA indépendante a donné GO pour le code, sous réserve du prévol et du pilote réels.

Le déploiement o2switch a avancé Git sans écraser les fichiers locaux préexistants, renouvelé les caches de configuration et de vues et quitté le mode maintenance. Le SHA serveur a été vérifié ; `/` et `/up` ont répondu 200 en HTTPS. Les PR #21 et #22 n'ajoutent aucune migration. La sauvegarde SQL privée et l'essai de restauration sont documentés dans `RELEASE_2026-10-15.md` ; aucune restauration de production n'a été nécessaire.

Après avoir corrigé une absence de saut de ligne final dans `.env`, le cache de production a été contrôlé avec les **types** des valeurs : `PAYMENT_RECEIPTS_AUTO_SEND=true` (booléen), `PAYMENT_RECEIPTS_HISTORICAL_MODE=pilot`, compte pilote `46`, paiement pilote `27`. Ce paiement correspond à l'inscription #29 et à 100 €. Le rapprochement en lecture seule avec Stripe donne `holdReason=null`. Le compte #46 possède aussi trois autres paiements Stripe payés (#69, #171 et #255) qui sont **hors portée** du pilote.

La simulation `payment-receipts:request-verification --limit=1 --after-user-id=45` a trouvé un seul compte éligible, zéro envoi. La commande avec `--commit` a ensuite examiné ce seul compte et le serveur SMTP a accepté **une** notification de vérification sans PDF. La ligne de suivi est `pilot_sent`, avec `sent_at` renseigné. Au contrôle immédiatement suivant, `email_verified_at` était vide et il n'existait aucun reçu pour les paiements #27, #69, #171 et #255. L'utilisateur s'occupe du clic du titulaire et préviendra ensuite. La réception en boîte, le clic signé, l'émission et la réception du PDF réel #27 restent **non vérifiés**. Ne pas relancer automatiquement une invitation déjà acceptée par SMTP.

Le mode `pilot` reste en production : il ne peut délivrer historiquement que le paiement #27 du compte #46. Les nouveaux paiements confirmés restent sous `auto_send=true` ; leur parcours réel de bout en bout n'a pas encore été observé. Aucune campagne générale n'a été lancée. Le paiement Stripe #226 est retenu faute de métadonnées d'inscription/pilote suffisantes ; le contrôle inverse Stripe → base reste à faire. L'utilisateur rapprochera personnellement les 75 paiements hors Stripe avant leur envoi.

## Audit `audit-sites` des 20 points sur cet état livré

Les points non touchés par les PR #21/#22 conservent les preuves de l'audit de production antérieur. Les points touchés et critiques ont été revus sur le code déployé, le cache actif et les données du pilote. `Non vérifié` n'est pas assimilé à un succès.

| N° | Point | Statut | Preuve et limite |
| ---: | --- | --- | --- |
| 1 | Données personnelles | À corriger | Durées 3/5/10 ans validées, mais purge sûre non implémentée. Le lien pilote ne contient pas de reçu ; son cycle complet reste à observer. |
| 2 | Conditions et mentions | À corriger | Association et directeur de publication affichés ; conditions des inscriptions payantes à qualifier. |
| 3 | Secrets et API | À corriger | Ancien identifiant Mailtrap actif lors du dernier contrôle et présent dans un ancien commit public ; révocation non confirmée. Aucune valeur copiée dans ce rapport. |
| 4 | HTTPS | OK | HTTP redirige vers HTTPS ; `/` et `/up` répondent 200 après déploiement du SHA `150aa63`. |
| 5 | Cookies et traceurs | À corriger | Inventaire des scripts tiers et besoin de consentement non clos. |
| 6 | Titres et descriptions | À corriger | Description absente de `/register` dans l'audit public. |
| 7 | Aperçu social | À corriger | Image de partage actuelle 166 × 166 px. |
| 8 | Favicon | OK | Favicon servi en 200 lors du contrôle public précédent ; asset non touché. |
| 9 | Sitemap et robots | À corriger | `robots.txt` 200 ; `/sitemap.xml` 404 dans l'audit public. |
| 10 | Alternatives textuelles | Non vérifié | Correction ciblée au guichet ; audit exhaustif avec lecteur d'écran absent. |
| 11 | Poids des images | À corriger | Logo SVG et plan paddock volumineux dans l'audit public ; assets non touchés. |
| 12 | Vitesse | Non vérifié | Cache Vite et Chart.js différé ; pas de mesure mobile représentative du parcours interne. |
| 13 | Contraste | Non vérifié | Pas de contrôle systématique WCAG des états interactifs. |
| 14 | Affichage mobile | Non vérifié | Parcours authentifiés de production non exercés sur téléphone. |
| 15 | Page 404 | OK | Page d'aide 404 et liens de retour observés avant les PR #21/#22 ; route non touchée. |
| 16 | Liens | Non vérifié | Liens publics principaux vérifiés ; contrôle exhaustif absent. |
| 17 | Formulaires et flux | Non vérifié | **Critique** : une invitation pilote acceptée et zéro reçu inattendu en base ; clic signé, PDF #27, réception, accès pilote et refus avec autre compte non encore observés. Nouveau paiement réel aussi à tester. |
| 18 | Anti-spam | Non vérifié | Limite de renvoi profil testée localement ; efficacité avec IP/proxy o2switch non contrôlée en production. |
| 19 | Mesure d'audience | Sans objet | Aucun outil ni besoin de mesure d'audience demandé ; à réévaluer si l'association souhaite le mesurer. |
| 20 | Action principale | OK | Inscription visible sur accueil et calendrier publics ; pages non touchées. |

**Bilan : 4 OK, 8 À corriger, 7 Non vérifiés, 1 Sans objet. Aucun verdict « prêt à publier »** tant que les risques critiques et les parcours réels ne sont pas clos. Le déploiement de la correction limitée a été vérifié ; la campagne historique reste suspendue.

## Prochain contrôle après le clic du titulaire

Vérifier dans cet ordre : compte #46 marqué vérifié, reçu unique du paiement #27 avec `email_status=sent` et 100 €, absence de reçus pour #69/#171/#255, accès du titulaire au PDF, réception et ouverture du PDF, refus d'accès d'un autre compte. Si l'envoi reste en `sending` ou si SMTP est incertain, rapprocher manuellement avant toute relance. Conserver le mode `pilot` et ne pas passer à `all` sans ces preuves et sans le contrôle inverse Stripe → base.
