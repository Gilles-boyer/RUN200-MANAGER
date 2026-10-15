# Prévol de production des reçus — 15 octobre 2026

## Portée et méthode

Inspection **en lecture seule** dans le terminal cPanel et JetBackup de l'hébergement o2switch. Les requêtes sur la base ne renvoient que des agrégats ; aucune adresse de pilote, aucun identifiant Stripe et aucun secret SMTP n'a été affiché ou copié dans ce rapport. Aucun code, schéma ou paiement de production n'a été modifié ; aucun reçu n'a été envoyé. Ces observations précèdent le déploiement de la PR brouillon #17.

## Application et déploiement

| Contrôle | Constat |
| --- | --- |
| Révision en service | `2dc62f2fede2ea48c11137952b95b75cec4cb1699` sur `main`, donc antérieure aux reçus ; table `payment_receipts` absente. |
| Exécution CLI | PHP 8.3.35, Laravel 12.48.1, environnement `production`, mode debug désactivé, maintenance désactivée, MySQL. La version PHP du processus web reste à confirmer séparément. |
| Liens applicatifs | `APP_URL` et la route de l'espace pilote génèrent des URL en HTTPS dans le contexte CLI contrôlé. |
| Arbre Git serveur | Modifications locales sur `.env.production.example` et `public/storage/.gitignore` ; `error_log`, un asset JS et `public/storage` non suivis. Préserver ces fichiers lors du déploiement. Leur contenu n'a pas été lu. |
| Courriel | Transport `smtp` configuré avec mot de passe présent, file `sync`. L'expéditeur utilise le domaine `run200cfg.clicksolutions.re`, alors que `contact@cfg.re` est l'adresse de contact à faire figurer sur le reçu. Le serveur SMTP configuré n'est pas Mailtrap. Aucune livraison réelle n'a été testée. |
| Planification | Aucune entrée `schedule:run` de Run200 repérée dans `crontab -l`. La file `sync` n'en dépend pas pour les nouveaux reçus ; revoir les autres tâches planifiées si la configuration de file change. |

## Paiements historiques

| Mesure sur la base active | Résultat |
| --- | ---: |
| Lignes `payments` | 319 |
| `paid` éligibles à examiner pour un reçu | 252 |
| `cancelled` / `pending` / `failed` | 42 / 21 / 4 |
| Méthodes des 252 confirmés : Stripe / espèces / carte sur place / manuel / virement | 177 / 37 / 28 / 9 / 1 |
| Adresses de pilote distinctes pour les 252 paiements | 101 |
| Maximum de reçus pour une même adresse | 5 |
| Dates de paiement extrêmes | 5 février au 5 octobre 2026 |
| Montant en centimes absent ou non positif ; montant décimal non positif ; écart entre les deux | 0 ; 0 ; 0 |
| Date `paid_at` absente ; montant remboursé non nul ; devise autre que EUR | 0 ; 0 ; 0 |
| Inscription, pilote, utilisateur ou course manquant ; e-mail absent ou invalide ; nom pilote/course vide | 0 pour chacun |
| Parmi les 177 Stripe : intention ou session absente ; intention dupliquée | 0 ; 0 |

Aucun paiement n'a actuellement le statut `refunded` ou `partially_refunded`. Les requêtes vérifient la cohérence **interne** des enregistrements et les champs nécessaires au reçu. Les adresses contrôlées sont celles des comptes pilotes au moment de la lecture ; elles peuvent changer avant l'envoi.

## Rapprochement Stripe en lecture seule

Le 15 octobre 2026 vers **12 h 05 UTC**, la clé Stripe réelle configurée sur le serveur a permis de récupérer les **177 PaymentIntents** référencés par les 177 lignes locales `paid`/`stripe`. Les deux passes de lecture API ont abouti pour les 177 références, sans erreur. Les 177 PaymentIntents sont en mode réel avec statut `succeeded`, montant reçu et devise identiques aux données locales. Leur dernière charge développée est présente, liée au même PaymentIntent, payée et capturée ; aucune dernière charge n'était signalée comme litigieuse au moment du contrôle, et aucun écart de remboursement avec `refund_amount_cents` local n'a été relevé. Aucun identifiant de paiement, objet API complet, `client_secret` ou autre donnée personnelle n'a été exporté dans ce rapport.

Ce rapprochement va **de la base vers Stripe**. Il ne démontre pas l'absence d'encaissements Stripe sans ligne locale et ne couvre pas les **75 paiements hors Stripe** (espèces, carte sur place, manuel, virement), les remises bancaires ou les litiges apparus après ce contrôle. Le registre de caisse et les relevés bancaires restent à rapprocher par une personne habilitée avant l'envoi historique de ces reçus.

## Sauvegarde observée

JetBackup 5 affiche pour la base MySQL active `mepa3308_run200cfg` une sauvegarde du **15 octobre 2026 à 01 h 08**, 46 points de sauvegarde disponibles et une taille affichée de 5,26 Mo. Le nom de cette base a été comparé à la configuration Laravel active. La restauration n'a pas été essayée ; avant une migration, produire ou confirmer un point de sauvegarde récent, identifier le retour arrière et vérifier la récupération sur une cible isolée si possible.

## Décision de mise en service

Le volume, les contrôles internes et le rapprochement des 177 références Stripe rendent un rattrapage par petits lots **préparable**. Les 75 paiements hors Stripe et le contrôle inverse Stripe vers la base restent à rapprocher ; la délivrabilité des e-mails n'est pas prouvée. Déployer seulement après la levée des points critiques du rapport `AUDIT_2026-10-15.md`, la préservation des fichiers locaux du serveur et un plan de retour arrière. Après migration, exécuter la simulation `payment-receipts:backfill` sur des lots de **20**, contrôler un PDF et un envoi maîtrisé, puis traiter les lots suivants en vérifiant la remise et les compteurs `sent`/`failed`/`sending`. Aucun envoi massif ne doit précéder ces contrôles.
