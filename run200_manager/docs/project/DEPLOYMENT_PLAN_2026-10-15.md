# Plan de déploiement contrôlé — reçus de paiement RUN200

**Cible :** `https://run200cfg.clicksolutions.re/`, Laravel dans `/home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager`, document root sur son `public/`. **État du plan :** préparé depuis le dépôt et le [prévol de production du 15 octobre](PRODUCTION_PREFLIGHT_2026-10-15.md) ; aucune commande de mutation ci-dessous n'a été exécutée. La PR #17 est encore brouillon au moment de la rédaction. Le serveur est au SHA `2dc62f2fde2ea48c11137952b95b75cec4cb1699` sur `main` ; PHP CLI 8.3.35, Laravel 12.48.1, MySQL, `QUEUE_CONNECTION=sync`. Confirmer à nouveau ces faits immédiatement avant toute intervention.

## Risques et conditions d'entrée

1. **À intégrer et tester avant publication :** un drapeau `PAYMENT_RECEIPTS_AUTO_SEND` désactivé par défaut, contrôlé au début du listener `SendPaymentConfirmation`, avant émission et envoi automatiques d'un reçu pour un nouveau paiement. Le rattrapage `payment-receipts:backfill --commit` reste une commande distincte, explicite et non débloquée par ce drapeau. Tester ces deux chemins et le passage du drapeau via `config:cache` ; inscrire le contrat dans `config/payment_receipts.php`. La version initiale de la PR #17 n'avait **pas** ce drapeau et aurait tenté l'envoi dès sa mise en ligne ; vérifier son intégration dans le SHA final. Les paiements confirmés pendant sa désactivation seront ensuite repris par le rattrapage, y compris si leur ligne existait déjà avant le basculement.
2. Faire revoir, fusionner et laisser terminer **la CI du commit exact à déployer**, y compris PHP 8.3/Vite et Pint. La CI documentée de la PR au SHA `679610e` a réussi, mais son résultat ne prouve ni la CI du futur commit fusionné ni le déploiement. Figer ce SHA `TARGET` ; n'utiliser aucune branche flottante pendant la coupure.
3. Lever les points critiques du [rapport audit-sites](AUDIT_2026-10-15.md) avant d'annoncer le site prêt : ancien secret SMTP présent dans l'historique Git (rotation si actif), règles de conservation et mentions/conditions, parcours pilote et e-mail en production. Confirmer PHP **web** et extensions requises séparément du PHP CLI. **SMTP technique vérifié le 15 octobre :** le terminal a confirmé l'acceptation d'un message sans donnée personnelle, et l'utilisateur a confirmé sa réception à l'adresse de contrôle. Ne pas refaire ce test par routine. Cela ne prouve pas encore l'envoi d'un PDF joint ni le déclenchement depuis une requête web ; vérifier ces points séparément, ainsi que l'expéditeur et les en-têtes de délivrabilité.
4. L'utilisateur effectuera le rapprochement des **75 paiements hors Stripe** avec le registre de caisse et les relevés ; contrôler aussi s'il existe des encaissements Stripe sans ligne locale. Le contrôle du 15 octobre a seulement rapproché les 177 lignes Stripe locales vers leurs PaymentIntents. Recontrôler les adresses actuelles des pilotes juste avant le rattrapage ; la lecture du 15 octobre portait sur 252 paiements et 101 adresses. La correction PAY-04 rend `DownloadReceiptController` strictement en lecture : GET renvoie 404 sans reçu existant, et l'interface n'affiche le lien que pour un reçu présent. **Le code peut donc être déployé avec le drapeau OFF avant le rapprochement des 75 lignes**, sous réserve que PAY-04 et ses tests soient inclus dans le SHA livré. L'émission et l'envoi historiques des paiements non rapprochés restent différés ; ne lancer aucun `backfill --commit` global jusque-là.
5. Confirmer un point de sauvegarde **récent** de la base active `mepa3308_run200cfg`, son téléchargement/rétention et réaliser un essai de restauration sur une cible isolée avant le basculement. JetBackup affichait le 15 octobre à 01 h 08 un point de 5,26 Mo et 46 points au total ; **aucune restauration n'a été testée**. Si aucune cible isolée ni méthode de restauration vérifiable n'est disponible, conserver ce prérequis comme non rempli et différer la publication. Vérifier l'espace disponible pour code, `vendor`, sauvegarde et PDF.
6. Confirmer que le serveur n'a pas changé depuis le prévol. Son Git était sale : `.env.production.example` modifié, `public/storage/.gitignore` supprimé, `error_log`, un asset JS et `public/storage` non suivis. Leur contenu n'a pas été examiné ; les préserver. Ne pas employer `git reset --hard`, `git clean`, `git stash --all`, `rsync --delete` ni une restauration globale de fichiers sans inventaire. Si un chemin sale recoupe le diff de release, arrêter et résoudre ce conflit précisément.

**Comparaison des garde-fous :** la maintenance courte empêche les parcours web pendant la coupure, mais un événement de paiement peut arriver après `artisan up` et déclencher immédiatement le listener (`app/Events/PaymentConfirmed.php`, `app/Listeners/SendPaymentConfirmation.php`, file `sync`). Le drapeau désactivé suspend l'émission et l'envoi automatiques durant ce créneau d'observation. PAY-04 empêche une émission indirecte par la route GET ; vérifier les deux corrections dans le SHA livré. Recommander maintenance pendant fichiers/migrations, réouverture avec drapeau OFF, contrôles HTTP et PDF ciblé, puis activation contrôlée de l'automatisme pour les **nouveaux** paiements. Le rattrapage historique global attend le rapprochement comptable. Tant que le drapeau ou PAY-04 ne sont pas intégrés et testés, ce plan **interdit le basculement**.

## Diff à qualifier et préparation reproductible

Entre le SHA de production contrôlé et la tête actuelle de PR, `git diff` montre **une seule nouvelle migration**, `2026_10_15_000001_create_payment_receipts_table.php` : création additive de `payment_receipts`, clé étrangère restrictive vers `payments`, aucun `ALTER` de table existante. `composer.lock`, `package-lock.json` et les fichiers suivis de `public/build/` ne changent pas. Le manifest suivi référence `app-BkubRR6Y.js`, `app-DYY_RKWX.css` et le module différé `charts-BkXXYWQH.js`. **Refaire le diff contre `TARGET`** : toute modification ultérieure invalide cette hypothèse.

La CI installe les dépendances verrouillées (`composer install`, `npm ci`) avec Node 22 et construit Vite. Pour cette révision sans changement de sources JS/CSS ni du manifest, ne pas reconstruire les assets dans l'arbre de production : vérifier que le manifest et chacun de ses fichiers sont présents, garder les anciens fichiers hashés durant le basculement pour les pages déjà ouvertes. Si `TARGET` modifie des sources ou le manifest, préparer un build reproductible depuis le lockfile avec Node 22, comparer les fichiers au manifest et les transférer comme un ensemble avant d'ouvrir le site. La build CI seule ne constitue pas un artefact déployé.

Préparation en **lecture seule** dans le terminal cPanel (les valeurs `BASE` et `TARGET` sont des SHA publics, aucun secret n'est à afficher). Les exemples utilisent `exit 1` pour arrêter la séquence : les exécuter ligne par ligne avec contrôle de chaque résultat ou dans un **sous-shell**/script revu, car un `exit` collé directement à l'invite interactive fermerait la session Terminal :

```sh
(
APP=/home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager
BASE=2dc62f2fde2ea48c11137952b95b75cec4cb1699
REPO="$(git -C "$APP" rev-parse --show-toplevel)" || exit 1
git -C "$REPO" rev-parse HEAD
git -C "$REPO" branch --show-current
git -C "$REPO" status --short
php -v
cd "$APP" || exit 1
php artisan about
php artisan migrate:status
)
```

Après fusion/CI, `git -C "$REPO" fetch origin main`, puis fixer `TARGET` au SHA complet de `FETCH_HEAD` **et le comparer au SHA validé sur GitHub**. Exécuter `git -C "$REPO" diff --name-status "$BASE" "$TARGET"` et `git -C "$REPO" diff --name-only "$BASE" "$TARGET"` ; confirmer que rien ne recoupe les chemins sales. Vérifier `git diff "$BASE" "$TARGET" -- run200_manager/composer.lock run200_manager/package-lock.json run200_manager/public/build` depuis la racine Git locale ; sur le serveur, adapter le préfixe à `REPO` obtenu. Vérifier les migrations pendantes avant de lancer `migrate --force` : si d'autres migrations apparaissent, arrêter et revoir leurs effets.

## Fenêtre de basculement

Attribuer un opérateur pour les commandes et un autre pour vérifier le résultat. Noter heure, SHA, version PHP CLI et web, état Git, manifest, migration et référence du point JetBackup dans le journal de release **sans adresse, identifiant de paiement ni secret**. Garder l'adresse de contrôle SMTP séparée de ce journal. Pendant la maintenance, surveiller les webhooks Stripe ; une réponse non 2xx doit être examinée et laissée à la politique de reprise Stripe, sans rejouer manuellement un événement à l'aveugle.

1. Préparer un répertoire de sauvegarde **hors du document root**, privé à l'opérateur. Sauvegarder la configuration `.env` et le manifest sans publier leur contenu. Préserver les chemins sales listés et le lien/répertoire `public/storage`. Ne pas modifier les permissions globales du site.
2. Mettre Laravel en maintenance, relever la dernière sauvegarde cohérente de la base active et créer/confirmer un point immédiatement avant migration avec JetBackup ou un export transactionnel contrôlé. Vérifier la présence et la capacité de restauration de ce point avant de continuer. Si aucune sauvegarde récupérable n'est disponible, remettre le site en ligne et reporter.
3. Avancer le checkout par **fast-forward vers le SHA figé**, sans écraser les modifications locales. Installer les dépendances PHP du lockfile. Vérifier les besoins de plateforme, vider les caches de routes/configuration, puis vérifier et appliquer la seule migration attendue. Confirmer que le nouveau drapeau existe et vaut **désactivé** dans la configuration Laravel active, y compris après `config:cache`. Éviter `composer update`, `npm update`, `key:generate` et toute nouvelle valeur `.env` improvisée.
4. Contrôler le manifest Vite et les trois assets référencés. Compiler les vues et la configuration ; ne pas présumer que `route:cache` est sûr avec les routes à fermeture présentes dans `routes/web.php`. Vérifier les routes, la connexion base et l'état Laravel **avant** de rouvrir ; le test HTTP 200 de `/up` attend `artisan up`, car le mode maintenance peut répondre 503. Si un contrôle échoue, rester en maintenance et appliquer le retour arrière.
5. Sortir de maintenance **avec le drapeau désactivé** ; contrôler l'application sur **l'URL exacte** et le SHA du serveur. Pour le test PDF, choisir **un paiement Stripe payé déjà rapproché et dont le compte pilote actuel est vérifié**. Créer son unique reçu par un appel CLI explicite à `IssuePaymentReceipt::issue(..., historical: true)`, **sans appeler `SendPaymentReceipt`**, puis consulter le PDF par GET authentifié. La route GET reste en lecture seule et ne doit pas changer le numéro ni l'état d'e-mail ; tester 403 et 404. Après ces contrôles, décider d'un **premier envoi historique individuel au véritable pilote** avec la commande explicite, puis seulement activer l'automatisme des nouveaux paiements si l'envoi du PDF joint est confirmé. La réponse de `/up` ne prouve que le boot HTTP ; les vérifications métier sont séparées. Si des paiements ont été confirmés avec le drapeau coupé, les inclure dans le rattrapage après rapprochement ; ne pas les supposer couverts par les seuls nouveaux ID. Le transport `sync` peut ralentir la confirmation d'un paiement : relever cette latence et les erreurs après activation.

Commandes de basculement proposées, à lancer **séquentiellement** seulement après les conditions d'entrée et les vérifications entre blocs :

```sh
(
APP=/home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager
REPO="$(git -C "$APP" rev-parse --show-toplevel)" || exit 1
BASE=2dc62f2fde2ea48c11137952b95b75cec4cb1699
TARGET='REMPLACER_PAR_SHA_COMPLET_DU_MAIN_FUSIONNE_ET_VERT'
umask 077
ROLLBACK="/home/mepa3308/run200-release-$(date +%Y%m%d-%H%M%S)"
mkdir -m 700 "$ROLLBACK" || exit 1
git -C "$REPO" status --short > "$ROLLBACK/git-status-before.txt"
git -C "$REPO" rev-parse HEAD > "$ROLLBACK/base-sha.txt"
cp -p "$APP/.env" "$ROLLBACK/env.before" || exit 1
chmod 600 "$ROLLBACK/env.before" || exit 1
cp -a "$APP/public/build" "$ROLLBACK/build.before" || exit 1
cd "$APP" || exit 1
php artisan down || exit 1
)
```

**Arrêt obligatoire :** relever/confirmer maintenant un point JetBackup ou export cohérent de la base en maintenance et sa restauration vérifiée sur cible isolée. Si ce contrôle manque, exécuter `php artisan up` et reporter. Le commentaire dans un bloc de commandes ne constitue pas une validation. Noter la référence du point hors du document public.

Après cette validation seulement, reprendre le code :

```sh
(
APP=/home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager
REPO="$(git -C "$APP" rev-parse --show-toplevel)" || exit 1
BASE=2dc62f2fde2ea48c11137952b95b75cec4cb1699
TARGET='REMPLACER_PAR_SHA_COMPLET_DU_MAIN_FUSIONNE_ET_VERT'
cd "$APP" || exit 1
git -C "$REPO" fetch origin main || exit 1
test "$(git -C "$REPO" rev-parse FETCH_HEAD)" = "$TARGET" || exit 1
test "$(git -C "$REPO" rev-parse HEAD)" = "$BASE" || exit 1
test "$(git -C "$REPO" branch --show-current)" = main || exit 1
git -C "$REPO" merge-base --is-ancestor "$BASE" "$TARGET" || exit 1
git -C "$REPO" merge --ff-only "$TARGET" || exit 1
test "$(git -C "$REPO" rev-parse HEAD)" = "$TARGET" || exit 1
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader || exit 1
composer check-platform-reqs --no-dev || exit 1
php artisan config:clear || exit 1
php artisan route:clear || exit 1
php artisan migrate:status || exit 1
)
```

**Second arrêt obligatoire :** lire la sortie de `migrate:status`. La seule migration nouvelle attendue est `2026_10_15_000001_create_payment_receipts_table.php`. Si d'autres migrations sont pendantes, les examiner et corriger le plan avant toute migration. Après validation :

```sh
(
cd /home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager || exit 1
php artisan migrate --force || exit 1
php artisan migrate:status || exit 1
php artisan view:cache || exit 1
php artisan config:cache || exit 1
php artisan tinker --execute='if (! is_array(config("payment_receipts")) || ! array_key_exists("auto_send", config("payment_receipts")) || config("payment_receipts.auto_send") !== false) { throw new \RuntimeException("Activation automatique des reçus non désactivée"); } echo "Reçus automatiques désactivés\n";' || exit 1
php artisan route:list --name=payments.receipt || exit 1
test -f public/build/manifest.json || exit 1
test -f public/build/assets/app-BkubRR6Y.js || exit 1
test -f public/build/assets/app-DYY_RKWX.css || exit 1
test -f public/build/assets/charts-BkXXYWQH.js || exit 1
)
```

Chaque bloc s'arrête en cas d'erreur **sans sortir de maintenance** et, grâce au sous-shell, sans fermer la session cPanel ; vérifier aussitôt son code de retour (`echo $?`) **avant le bloc suivant**. Lire aussi la sortie textuelle du contrôle Tinker et vérifier que le drapeau est présent et désactivé avant `php artisan up`. Remplacer la valeur de `TARGET` par le SHA réel, jamais par une branche. Si la structure Git du serveur place `run200_manager` sous `REPO`, les commandes Git restent valides avec `-C "$REPO"` et les commandes Laravel avec `cd "$APP"`.

Après les contrôles techniques et la validation du test SMTP déjà reçu, sortir de maintenance avec `php artisan up`. Garder le drapeau OFF pendant le test PDF et le premier envoi historique individuel décrits ci-dessous. Pour activer ensuite l'envoi des **nouveaux** paiements, définir `PAYMENT_RECEIPTS_AUTO_SEND=true` dans le `.env` privé via l'éditeur protégé de cPanel, sans afficher ni copier le reste du fichier, puis exécuter :

```sh
(
cd /home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager || exit 1
php artisan config:clear || exit 1
php artisan config:cache || exit 1
php artisan tinker --execute='if (config("payment_receipts.auto_send") !== true) { throw new \RuntimeException("Activation automatique des reçus non confirmée"); } echo "Reçus automatiques activés\n";' || exit 1
)
```

Le contrôle des envois historiques reste entièrement distinct : la commande `backfill --commit` doit être exécutée explicitement, lot par lot, après rapprochement, hormis l'éventuel premier envoi individuel du paiement Stripe déjà vérifié. GET du PDF n'émet rien depuis PAY-04. En incident de délivrabilité, remettre `PAYMENT_RECEIPTS_AUTO_SEND=false` et renouveler `config:cache` avant d'envisager un retour arrière du code ; cette coupure n'annule pas les messages déjà acceptés par SMTP.

## Vérification sur la cible exacte

Après `php artisan up`, enregistrer les codes HTTP et les en-têtes sans exporter de session ni de données de pilote :

```sh
(
REPO="$(git -C /home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager rev-parse --show-toplevel)" || exit 1
git -C "$REPO" rev-parse HEAD
curl -fsS -o /dev/null -w '%{http_code}\n' https://run200cfg.clicksolutions.re/up
curl -fsS -o /dev/null -w '%{http_code}\n' https://run200cfg.clicksolutions.re/
curl -fsS -o /dev/null -w '%{http_code}\n' https://run200cfg.clicksolutions.re/public/calendrier
curl -fsSI https://run200cfg.clicksolutions.re/build/assets/app-BkubRR6Y.js
curl -fsSI https://run200cfg.clicksolutions.re/build/assets/app-DYY_RKWX.css
)
```

Attendus : SHA serveur égal à `TARGET`, `/up` et pages publiques en 200, assets présents et `Cache-Control: public, max-age=31536000, immutable` pour les fichiers Vite hashés. Comparer le manifest **servi depuis le disque** avec les noms effectivement chargés dans le HTML, et faire un essai HTTP depuis l'extérieur du serveur ; `curl` local seul ne valide pas CDN/cache navigateur. En navigation authentifiée contrôlée, choisir un paiement Stripe payé déjà rapproché et connu de l'opérateur, vérifier l'adresse actuelle du **véritable pilote**, puis émettre **explicitement** un seul reçu par CLI comme indiqué ci-dessous. Garder le drapeau OFF. Ouvrir `/payments/{id}/receipt` sous le compte du pilote ou un compte staff possédant `payment.view` ; ce GET est en lecture seule. Contrôler le PDF `application/pdf`, identité de l'association, SIRET, montant, numéro et date ; rouvrir et vérifier que le numéro et l'état d'e-mail ne changent pas. Vérifier qu'un autre pilote n'y accède pas (403), qu'un paiement confirmé **sans reçu** obtient 404 et qu'un paiement non confirmé n'obtient pas de reçu, sans exposer leurs données. Si les comptes nécessaires à ces contrôles ne sont pas disponibles, indiquer `Non vérifié` et différer l'activation automatique. Ne pas afficher le PDF ni les données personnelles dans les logs de release. Faire un parcours mobile et vérifier les points critiques/touchés de l'audit-sites sur la version livrée. Observer `storage/logs` et le suivi des e-mails par types d'erreur/compteurs, sans copier de messages ni PII dans les rapports.

L'action suivante **crée une seule ligne de reçu en production et n'envoie pas d'e-mail**. L'opérateur doit d'abord choisir en privé l'ID exact parmi les paiements Stripe déjà rapprochés, vérifier son compte pilote/destinataire, puis saisir l'ID à l'invite ; n'inscrire ni nom ni adresse dans le journal partagé. En cas de reçu déjà présent, la commande s'arrête sans changement. Ne pas substituer `backfill --commit` à cette action de préparation, car il enverrait immédiatement un e-mail.

```sh
(
cd /home/mepa3308/run200cfg.clicksolutions.re/cgi-bin/run200_manager || exit 1
printf 'ID du paiement Stripe déjà rapproché : '
IFS= read -r RECEIPT_TEST_ID
case "$RECEIPT_TEST_ID" in ''|*[!0-9]*) echo 'ID invalide' >&2; exit 1 ;; esac
test "$RECEIPT_TEST_ID" -gt 0 || exit 1
RUN200_RECEIPT_TEST_ID="$RECEIPT_TEST_ID" php artisan tinker --execute='$payment = \App\Models\Payment::query()->findOrFail((int) getenv("RUN200_RECEIPT_TEST_ID")); if ($payment->method !== \App\Domain\Payment\Enums\PaymentMethod::STRIPE || $payment->status !== \App\Domain\Payment\Enums\PaymentStatus::PAID || $payment->receipt()->exists()) { throw new \RuntimeException("Paiement non éligible ou reçu déjà présent"); } $receipt = app(\App\Application\Payments\UseCases\IssuePaymentReceipt::class)->issue($payment, historical: true); echo "Reçu #".$receipt->id." ; statut email ".$receipt->email_status."\n";' || exit 1
)
```

Confirmer `email_status=not_requested`, l'existence d'un seul reçu pour cet ID et **zéro envoi** avant le GET PDF. L'accès au PDF et les tests de refus sont ensuite des lectures. Si cette émission contrôlée échoue, ne pas activer l'automatisme.

Le test SMTP technique du 15 octobre est reçu, mais **aucun PDF joint n'a été livré**. Après la vérification du PDF privé ci-dessus, le premier envoi réel peut être **un seul** reçu historique destiné au pilote du paiement Stripe choisi, avec son adresse actuelle vérifiée et le statut de paiement rapproché. La commande `backfill` n'a pas d'option `--payment-id` : fixer l'ID du paiement choisi hors du journal partagé, lancer `--limit=1 --after-id=ID-1` **sans** `--commit`, puis vérifier que le « dernier paiement examiné » affiché est exactement l'ID prévu et qu'aucune anomalie n'apparaît. Si c'est le cas seulement, relancer avec les mêmes options et `--commit`. Il ne faut jamais remplacer cette vérification par l'hypothèse que le premier paiement trouvé est le bon. Vérifier les compteurs `sent`/`failed`/`sending`, la remise SMTP et si possible la réception par le pilote ; si cette dernière n'est pas confirmée, noter la limite. Ce premier e-mail part réellement et ne peut pas être rappelé. **Aucune de ces actions n'a été exécutée lors de la rédaction du plan.**

Après ce contrôle, activer éventuellement l'automatisme des nouveaux paiements selon la procédure ci-dessus, puis exécuter la **simulation** générale `php artisan payment-receipts:backfill --limit=20 --after-id=0`. La commande sans `--commit` est en lecture seule. Après rapprochement des paiements du lot et contrôle des destinataires, exécuter les lots avec `--commit` selon [la procédure des reçus](RECEIPTS_RELEASE.md) ; les 75 paiements hors Stripe attendent la vérification du registre par l'utilisateur. Le mode `sync` n'exige pas de worker pour ces envois ; aucun cron Run200 `schedule:run` n'a été trouvé, ce qui reste à traiter séparément pour les autres tâches. Un état `sending` impose un rapprochement SMTP avant relance. Les messages déjà remis ne peuvent pas être rappelés.

## Retour arrière et incident

Si un échec survient **avant** sortie de maintenance, arrêter les commandes et conserver les preuves (SHA, heure, étape, type d'erreur sans données sensibles). Si le checkout a avancé, revenir au SHA `BASE` seulement si les chemins sales restent préservables : `git -C "$REPO" switch --detach "$BASE"`, puis dans `APP` relancer `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader`, `php artisan config:clear`, `php artisan route:clear`, `php artisan view:clear`. Vérifier `/up` et la route publique avant `php artisan up`. **Ne pas forcer** si `git switch` refuse : garder la maintenance et restaurer le code depuis une copie contrôlée ou faire intervenir l'opérateur Git ; ne jamais utiliser `reset --hard`/`clean` sur cet arbre sale. Le SHA de `main` reste sur la nouvelle version : documenter le HEAD détaché et corriger ensuite le mécanisme de release.

La migration crée seulement `payment_receipts` ; le code ancien peut laisser cette table inutilisée. Par défaut, **ne pas lancer `migrate:rollback`** : son `down()` supprime toute la table et les reçus déjà émis. Si la table est encore vide et qu'une restauration du schéma est réellement nécessaire, décider sur preuve ; si des reçus, e-mails ou paiements sont apparus, préserver la base et préparer une correction en avant. Une restauration JetBackup complète ferait aussi perdre les transactions survenues après son point : recenser les transactions et sauvegarder l'état actuel avant toute restauration, puis rapprocher Stripe/caisse. Aucun rollback ne révoque les e-mails déjà délivrés. Si un envoi a échoué après acceptation SMTP, ne pas le réessayer sans rapprocher `sending`/`sent` aux journaux du fournisseur.

**Critère de clôture :** noter SHA fusionné, SHA réellement servi, résultat CI, sauvegarde et essai de restauration (ou `Non vérifié`), migration, manifest/HTTP, SMTP livré, PDF et autorisations, état du premier lot, audit-sites des points touchés/critiques et limites. Tant qu'un contrôle critique reste `À corriger` ou `Non vérifié`, rapporter une mise en service partielle ou différée, jamais « prêt ».
