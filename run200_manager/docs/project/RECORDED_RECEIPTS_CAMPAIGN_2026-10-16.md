# Campagne des reçus aux adresses enregistrées — plan opérationnel

## Décision de l'association

Le 16 octobre 2026, l'utilisateur a demandé l'envoi des reçus de **tous les paiements enregistrés comme encaissés**, y compris les 75 hors Stripe, aux adresses actuellement présentes dans les dossiers. Il accepte plusieurs e-mails par pilote et indique avoir prévenu les utilisateurs. Cette instruction remplace son projet antérieur de rapprocher lui-même les paiements hors Stripe avant leur envoi. L'adresse enregistrée ne prouve toutefois pas, à elle seule, le contrôle de la boîte par le destinataire. La campagne doit exposer ce risque et ne pas renseigner artificiellement `email_verified_at`.

## Périmètre technique prévu

- Une commande dédiée sélectionne des identifiants de paiements explicitement listés, 20 au maximum par lot. La simulation est sans effet et produit une empreinte du lot ; l'envoi exige une empreinte identique pour les valeurs relues.
- Chaque paiement doit avoir un statut encaissé cohérent, une date de paiement, un montant et un remboursement cohérents, une inscription, un pilote et un compte destinataire valides. Le destinataire est `registration->pilot->user->email`, jamais `payments.user_id` pour les paiements manuels : ce dernier peut être l'agent qui a saisi l'encaissement.
- Pour Stripe, un contrôle live du PaymentIntent, de la capture, des métadonnées, du remboursement et du litige précède l'émission. Le paiement #226 reste retenu tant que son identification Stripe n'est pas rapprochée.
- Pour espèces, CB sur place, manuel et virement, l'application connaît une saisie de paiement et éventuellement l'agent auteur ; elle ne détient pas de preuve bancaire indépendante. L'envoi sur ces lignes repose sur l'instruction de l'association et les contrôles de cohérence du dossier. Les anomalies restent retenues.
- Un reçu déjà `sent` ne repart pas. Un état `sending` ou une issue SMTP incertaine n'est jamais relancé automatiquement. L'adresse et les valeurs utiles sont revérifiées juste avant l'envoi. Plusieurs paiements réellement distincts peuvent produire plusieurs reçus et plusieurs e-mails.

## Porte de diffusion

1. Reprendre l'accès cPanel, relever le SHA et la configuration actifs, faire une sauvegarde privée fraîche, puis inventorier les paiements par méthode/statut et les reçus par état, sans sortir les adresses dans les journaux.
2. Finaliser le changement sur une branche, revue QA indépendante, tests ciblés et suite/CI pertinents. Appliquer `audit-sites` aux 20 points sur la version candidate ; les points critiques non prouvés restent `Non vérifié` et aucun verdict « prêt » n'est donné.
3. Déployer le code en conservant les protections de la campagne de vérification existante et tester la simulation sur des IDs connus. Faire d'abord un très petit lot et comparer paiements, reçus, états SMTP et PDF. Poursuivre seulement si les comptes sont cohérents.
4. Après chaque lot, enregistrer IDs examinés, retenus, déjà envoyés, nouveaux `sent`, `sending` et `failed`. `sent` prouve l'acceptation SMTP, pas la réception en boîte. Les exceptions sont reprises par identifiant après résolution ; aucune suppression ou remise à zéro de reçu.

Ce document est un **plan**. Il ne vaut ni autorisation supplémentaire ni preuve d'envoi. L'état réalisé doit être consigné séparément avec les résultats serveur et la version effectivement servie.

## Prévol de production du 16 octobre 2026

Le serveur sert encore le SHA `150aa63b801b0223810a5dc42d96c86da54b9545` au moment du prévol. La configuration active reste `auto_send=true`, mode historique `pilot`, compte #46 et paiement #27. Ce pilote n'a pas confirmé son adresse ; aucun reçu #27 n'existe encore. Ces constatations précèdent le déploiement de la nouvelle commande et **aucun e-mail de cette campagne n'a été envoyé à ce stade**.

| Méthode | Paiements `paid` | Adresses non vérifiées | Anomalies agrégées relevées |
| --- | ---: | ---: | ---: |
| Stripe | 177 | 173 | 0 |
| CB sur place | 28 | 17 | 0 |
| Espèces | 37 | 27 | 0 |
| Manuel | 9 | 5 | 0 |
| Virement | 1 | 0 | 0 |
| **Total** | **252** | **222** | **0** |

Le contrôle agrégé recherchait date d'encaissement absente, montant absent ou divergent entre euros et centimes, lien inscription/pilote/compte rompu et adresse nulle. Aucun dossier n'avait plusieurs paiements `paid`. Un seul reçu était déjà `sent` et aucun `sending` n'était présent avant la campagne. Cela ne prouve ni la réalité des 75 encaissements hors Stripe au-delà de la saisie de l'application, ni la propriété des 222 adresses non vérifiées.

Sauvegarde privée créée avant tout envoi : `/home/mepa3308/run200-receipts-pre-20261016.sql`, `mysqldump` terminé avec code 0, 11 815 460 octets, permissions `0600`. L'essai de restauration isolée du 15 octobre concerne la sauvegarde précédente ; celle-ci n'a pas encore été restaurée isolément.

## Contrôles du candidat

La commande dédiée est `php artisan payment-receipts:send-recorded-addresses --payment-ids=27` en simulation, puis `--commit --review-hash=<empreinte affichée>` sur exactement le même lot. Elle accepte 1 à 20 identifiants distincts, exige `PAYMENT_RECEIPTS_AUTO_SEND=true` pour envoyer et bloque le lot entier si un paiement est retenu. Le mode historique `pilot` protège le parcours automatique de confirmation d'adresse, **pas cette commande manuelle** ; seuls les identifiants explicitement sélectionnés, l'empreinte et le coupe-circuit global bornent celle-ci. Une simulation ou un envoi déjà marqué `sent` n'expédie aucun message supplémentaire.

La revue QA indépendante du code a demandé deux corrections : l'empreinte devait inclure toutes les données imprimées sur le PDF et les montants en euros/centimes devaient être strictement positifs et concordants. Ces corrections sont intégrées ; les 13 tests de la commande passent (85 assertions), ainsi que les 69 tests ciblés de reçus (426 assertions), Pint et PHPStan ciblé. La suite complète relancée après stabilisation passe **630 tests et 2 111 assertions**. La QA conclut GO **sur le code uniquement**, sous réserve des contrôles de release et de l'observation des lots réels. Une modification concurrente du nom ou de la course dans la très courte fenêtre entre l'empreinte sous verrou de paiement et la création du snapshot reste une limite de concurrence de priorité moindre.
