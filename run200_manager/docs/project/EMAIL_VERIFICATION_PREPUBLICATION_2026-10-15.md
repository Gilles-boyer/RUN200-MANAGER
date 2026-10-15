# Contrôle avant publication — confirmation d'adresse et reçus historiques

État examiné : branche `codex/run200-email-release-evidence`, base de production `bddafce34f5732bfee2f09d94406c99478815f91` (PR #20), correctif `0b88ca7`. Ce document décrit le candidat **avant** sa publication. La preuve de déploiement et l'audit de la cible seront consignés séparément.

Le correctif limite à trois renvois de lien par compte et à 60 par IP sur dix minutes. La confirmation par le lien signé peut lancer le reçu historique même quand le lien a été demandé depuis le profil avant la campagne ; un appel administratif reste soumis à l'invitation de campagne enregistrée. Le paiement est revérifié auprès de Stripe avant émission et les états `sent` et `sending` empêchent un second envoi automatique.

La revue QA indépendante conclut **GO pour le code local**, **NO-GO pour une campagne générale** avant le pilote réel. Tests ciblés : 32/32, 150 assertions ; PHPStan sans erreur, Pint ciblé et compilation des vues réussis. Une exécution locale de la suite entière a obtenu 602 succès et un échec d'accès au cache Blade sous Windows pendant `view:cache` exécuté en parallèle ; le test concerné repasse seul (7/7). La CI doit refaire la suite sans concurrence avant publication.

## Audit `audit-sites` du candidat, 20 points

Les preuves publiques correspondent encore à la version PR #20. Le statut `Non vérifié` exige un contrôle sur la version qui sera effectivement publiée.

| N° | Point | Statut | Preuve et limite |
| ---: | --- | --- | --- |
| 1 | Données personnelles | À corriger | Durées 3/5/10 ans approuvées, purge sûre non implémentée. |
| 2 | Conditions et mentions | À corriger | Association et directeur de publication affichés ; conditions des inscriptions payantes à qualifier juridiquement. |
| 3 | Secrets et API | À corriger | Ancien secret Mailtrap toujours actif dans l'historique Git public ; révocation à obtenir auprès du propriétaire. |
| 4 | HTTPS | OK | HTTP `/` et webhook redirigent en 307 ; HTTPS `/` et `/up` répondent 200 sur la version PR #20. |
| 5 | Cookies et traceurs | À corriger | Inventaire des scripts tiers et besoin de consentement non clos. |
| 6 | Titres et descriptions | À corriger | Description absente de `/register`. |
| 7 | Aperçu social | À corriger | Image sociale actuelle limitée à 166 × 166 px. |
| 8 | Favicon | OK | Favicon servi en 200 sur la cible. |
| 9 | Sitemap et robots | À corriger | `robots.txt` 200 ; `/sitemap.xml` 404. |
| 10 | Alternatives textuelles | Non vérifié | Correction ciblée faite au guichet ; audit exhaustif et lecteur d'écran non exercés. |
| 11 | Poids des images | À corriger | Logo SVG avec image embarquée lourde et plan paddock volumineux. |
| 12 | Vitesse | Non vérifié | Cache Vite et import Chart.js différé en service ; mesure mobile représentative absente. |
| 13 | Contraste | Non vérifié | Pas de relevé systématique des états interactifs selon WCAG. |
| 14 | Affichage mobile | Non vérifié | Accueil public contrôlé localement ; parcours authentifiés de production non exercés. |
| 15 | Page 404 | OK | Page d'aide 404 et liens de retour vérifiés sur la cible. |
| 16 | Liens | Non vérifié | Liens principaux vérifiés ; contrôle exhaustif absent. |
| 17 | Formulaires et flux | Non vérifié | Tests du lien signé et des droits locaux ; pilote réel #29, remise de son e-mail et accès au PDF non encore observés sur le candidat déployé. Critique. |
| 18 | Anti-spam | Non vérifié | Renvoi profil limité et testé localement ; IP réelle vue derrière le proxy o2switch et comportement en production non contrôlés. |
| 19 | Mesure d'audience | Sans objet | Aucun outil ni besoin de mesure d'audience exprimé ; à réévaluer si l'association le demande. |
| 20 | Action principale | OK | Inscription visible sur l'accueil et le calendrier publics. |

**Bilan candidat : 4 OK, 8 À corriger, 7 Non vérifiés, 1 Sans objet. Aucun verdict « prêt ».** Les défauts déjà connus hors de ce correctif restent ouverts. Les 177 lignes Stripe locales représentent 78 comptes distincts ; 173 lignes concernent 74 comptes sans adresse vérifiée. La vérification base → Stripe du 15 octobre a retenu le paiement interne #226, dont les métadonnées d'identification attendues manquent, malgré un montant payé concordant. Le contrôle inverse Stripe → base, les 75 paiements hors Stripe que l'utilisateur rapprochera et la réception du reçu pilote restent à faire avant une campagne générale.
