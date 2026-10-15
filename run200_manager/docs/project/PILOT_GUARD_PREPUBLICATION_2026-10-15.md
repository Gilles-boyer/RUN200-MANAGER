# Contrôle avant publication — pilote de reçu limité au paiement #27

Version de référence en production avant ce candidat : `bb2abbb67a6f920a04b8ce1c69ecdc0814eccbaa` (PR #21). Le serveur sert l'accueil et `/up` en 200. Au prévol, le compte #46 de l'inscription #29 possède **quatre** paiements Stripe payés de 100 € : #27, #69, #171 et #255. Le code PR #21 pouvait envoyer les quatre après vérification du compte. Aucun lien pilote n'a été envoyé. Le drapeau `PAYMENT_RECEIPTS_AUTO_SEND=false` est confirmé dans le cache de production ; la base contient un reçu `sent` et aucun `pending`/`sending`.

Le candidat `0c030f2` ajoute trois modes historiques : `off` par défaut, `pilot` pour un seul couple compte/paiement configuré, et `all` seulement après invitation de campagne `sent` liée par HMAC à l'adresse actuelle. Une invitation pilote est marquée `pilot_sent` et ne vaut jamais invitation générale. Le coupe-circuit `auto_send=false` borne également les commandes et le service historique. Le listener des nouveaux paiements refuse désormais un événement rejoué sur un ancien modèle rechargé ; les transitions réelles Stripe et manuelles restent couvertes.

Revue QA indépendante : GO pour le code local, **NO-GO pour tout envoi avant prévol de la configuration réelle**. Suite complète : **617 tests / 2 026 assertions** ; PHPStan sans erreur, Pint ciblé, cache des vues et `git diff --check` réussis. Les quatre producteurs synchrones de `PaymentConfirmed` ont été inspectés. Si cet événement est ultérieurement mis en file, son contrat devra transporter explicitement la preuve de transition.

## Audit `audit-sites` du candidat, 20 points

Ce tableau reprend les preuves publiques de la PR #21 et requalifie les parcours modifiés. Le contrôle du SHA et des flux réels sera refait après déploiement.

| N° | Point | Statut | Preuve et limite |
| ---: | --- | --- | --- |
| 1 | Données personnelles | À corriger | Durées 3/5/10 ans approuvées ; purge sûre non implémentée. |
| 2 | Conditions et mentions | À corriger | Association et directeur de publication affichés ; conditions des inscriptions payantes à qualifier. |
| 3 | Secrets et API | À corriger | Ancien identifiant Mailtrap encore actif dans l'historique Git public ; révocation non confirmée. |
| 4 | HTTPS | OK | HTTP redirige en 307 ; accueil HTTPS et `/up` répondent 200. |
| 5 | Cookies et traceurs | À corriger | Inventaire des scripts tiers et besoin de consentement non clos. |
| 6 | Titres et descriptions | À corriger | Description absente de `/register`. |
| 7 | Aperçu social | À corriger | Image actuelle 166 × 166 px. |
| 8 | Favicon | OK | Favicon servi en 200. |
| 9 | Sitemap et robots | À corriger | `robots.txt` 200 ; `/sitemap.xml` 404. |
| 10 | Alternatives textuelles | Non vérifié | Correction ciblée au guichet ; audit exhaustif avec lecteur d'écran absent. |
| 11 | Poids des images | À corriger | Logo SVG et plan paddock volumineux. |
| 12 | Vitesse | Non vérifié | Cache Vite et Chart.js différé en service ; mesure mobile représentative absente. |
| 13 | Contraste | Non vérifié | Pas de relevé systématique WCAG des états interactifs. |
| 14 | Affichage mobile | Non vérifié | Parcours authentifiés de production non exercés sur téléphone. |
| 15 | Page 404 | OK | Page d'aide 404 et liens de retour observés. |
| 16 | Liens | Non vérifié | Liens publics principaux vérifiés ; contrôle exhaustif absent. |
| 17 | Formulaires et flux | Non vérifié | Tests et revue locaux du pilote exact #46/#27 ; lien, PDF, destinataire et refus avec autre compte à observer sur la cible. Critique. |
| 18 | Anti-spam | Non vérifié | Renvoi profil limité en code ; IP réelle derrière le proxy et efficacité en production non contrôlées. |
| 19 | Mesure d'audience | Sans objet | Aucun outil ni besoin de mesure demandé ; à réévaluer sur demande de l'association. |
| 20 | Action principale | OK | Inscription visible sur accueil et calendrier publics. |

**Bilan : 4 OK, 8 À corriger, 7 Non vérifiés, 1 Sans objet. Le site reste sans verdict « prêt ».** Après fusion, déployer le candidat en conservant `auto_send=false`, vérifier le SHA et le mode historique `off`. Réactiver ensuite `auto_send=true` uniquement avec mode `pilot`, compte #46 et paiement #27 confirmés dans le cache et après prévol Stripe. Simuler une invitation unique, envoyer le message de vérification sans reçu, puis vérifier un seul reçu envoyé et les trois autres paiements inchangés. Le pilote dépend du clic du titulaire du compte. Les 75 paiements hors Stripe restent à rapprocher par l'utilisateur ; le paiement Stripe #226 reste retenu pour absence de métadonnées d'identification et le contrôle inverse Stripe → base reste ouvert.
