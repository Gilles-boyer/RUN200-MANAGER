# Système de Notifications Email - Run200 Manager

## 📧 Vue d'ensemble

Le système de notifications automatiques par email a été entièrement mis en place avec :
- **Notifications automatiques** à chaque étape du processus d'inscription
- **Notifications personnalisées** par course envoyées par les administrateurs
- **Vérification email** obligatoire lors de l'inscription
- **Double authentification (2FA)** optionnelle pour la sécurité

## 🔧 Configuration Mailtrap (Tests)

Les emails sont actuellement configurés pour utiliser Mailtrap en développement :

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=76098eb8281d77
MAIL_PASSWORD=9743c2a8a56961
MAIL_FROM_ADDRESS="noreply@run200.com"
MAIL_FROM_NAME="Run200 Manager"
```

### Pour la production
Modifiez `.env` avec vos identifiants SMTP réels (Gmail, SendGrid, AWS SES, etc.)

## 📬 Emails Automatiques

### 1. **Inscription créée** (`RegistrationCreated`)
**Quand** : Immédiatement après création d'une inscription
**Contenu** :
- Confirmation de l'inscription
- Détails de la course (date, lieu, véhicule)
- Lien pour payer l'engagement (si paiement requis)
- Rappel du rendez-vous VA/VT (samedi à 14h)
- Liste des prochaines étapes

### 2. **Paiement confirmé** (`PaymentConfirmed`)
**Quand** : Après validation du paiement (Stripe ou manuel)
**Contenu** :
- Confirmation du paiement
- Détails de la transaction
- Rappel IMPORTANT du rendez-vous VA/VT
- Documents obligatoires à apporter
- Prochaines étapes

### 3. **E-Carte d'accès** (`ECardMail`) ⭐ NOUVEAU
**Quand** : Immédiatement après confirmation du paiement
**Contenu** :
- QR Code unique pour le pointage à l'arrivée
- Informations de la course
- Détails du pilote et véhicule
- Instructions pour les vérifications VA/VT
- Conseils pour conserver l'email

### 4. **Inscription acceptée** (`RegistrationAccepted`)
**Quand** : Après validation par l'équipe administrative
**Contenu** :
- Félicitations, inscription validée
- Attribution du paddock (si disponible)
- **Rendez-vous OBLIGATOIRE VA/VT** (samedi à 14h)
- Documents obligatoires (permis, carte grise, assurance, casque)
- Déroulement des vérifications

### 5. **Rappel vérifications techniques** (`TechInspectionReminder`)
**Quand** : Envoyé automatiquement la veille du rendez-vous VA/VT
**Contenu** :
- Rappel du rendez-vous DEMAIN à 14h
- Lieu et durée
- Documents OBLIGATOIRES
- Programme du week-end
- **Email automatique via commande planifiée**

### 6. **Contrôle technique terminé** (`TechInspectionCompleted`)
**Quand** : Après le contrôle technique (réussi ou échoué)
**Contenu** :
- Résultat du contrôle (✅ validé ou ❌ refusé)
- Observations de l'inspecteur
- Si validé : félicitations + programme de la course
- Si refusé : raisons + marche à suivre

### 7. **Feuille d'engagement signée** (`EngagementFormSigned`)
**Quand** : Après signature de la feuille d'engagement
**Contenu** :
- Confirmation de l'engagement
- Toutes les étapes complétées ✅
- Lien vers l'E-Card avec QR code
- Programme de la course
- Rappels importants

### 8. **Inscription refusée** (`RegistrationRefused`)
**Quand** : Si l'inscription est refusée par l'administration
**Contenu** :
- Notification du refus
- Raison du refus
- Information sur le remboursement automatique
- Lien vers les autres courses disponibles

### 9. **Course ouverte aux inscriptions** (`RaceOpenedMail`) ⭐ NOUVEAU
**Quand** : Lorsqu'une course passe en statut "OPEN"
**Destinataires** : Tous les pilotes actifs avec email vérifié
**Contenu** :
- Annonce de l'ouverture des inscriptions
- Détails de la course (date, lieu, frais)
- Avantages de s'inscrire tôt
- Documents requis
- Bouton d'inscription direct

### 10. **Rappel J-3 avant course** (`RaceReminderMail`) ⭐ NOUVEAU
**Quand** : 3 jours avant la course (envoi automatique planifié)
**Destinataires** : Pilotes avec inscription acceptée
**Contenu** :
- Rappel de la course dans 3 jours
- Récapitulatif de l'inscription
- Checklist de préparation
- Rappel VA/VT du samedi
- Documents et équipements requis

### 11. **Course annulée** (`RaceCancelledMail`) ⭐ NOUVEAU
**Quand** : Lorsqu'une course passe en statut "CANCELLED"
**Destinataires** : Tous les pilotes inscrits (sauf annulés/refusés)
**Contenu** :
- Notification de l'annulation
- Motif (si fourni par l'admin)
- Informations sur le remboursement
- Lien vers les autres courses

### 12. **Résultats publiés** (`ResultsPublishedMail`) ⭐ NOUVEAU
**Quand** : Lorsque les résultats d'une course sont publiés
**Destinataires** : Tous les pilotes avec inscription acceptée
**Contenu** :
- Annonce de la publication des résultats
- Position du pilote (générale et catégorie)
- Meilleur temps au tour
- Points gagnés pour le championnat
- Félicitations spéciales pour le podium
- Lien vers les résultats complets

### 13. **Bienvenue pilote** (`WelcomePilotMail`) ⭐ NOUVEAU
**Quand** : À la création du profil pilote (inscription)
**Contenu** :
- Message de bienvenue
- Récapitulatif du profil créé
- Prochaines étapes recommandées
- Documents à préparer
- Liens utiles (ajouter véhicule, voir courses)

## 🎯 Notifications Personnalisées par Course

Les administrateurs peuvent envoyer des notifications personnalisées aux pilotes inscrits sur une course.

### Accès
`/admin/races/{race}/notifications`

### Cas d'usage
- Lien pour le chronométrage en direct
- Informations de dernière minute
- Changement de programme
- Conditions météo
- Invitation à un événement
- Rappel du programme
- Informations parking/accès

### Fonctionnalités
- **3 types** : Info ℹ️ / Avertissement ⚠️ / Succès ✅
- **Envoi immédiat** ou **planification** (date + heure)
- **Destinataires** : tous les inscrits ou sélection
- **Historique** avec possibilité de renvoyer
- **Compteur** de destinataires

### Exemple d'utilisation
```php
// Via l'interface web Admin
// Sujet: Lien du chronométrage en direct
// Message: Le chronométrage de la course est accessible en direct sur : https://chrono.run200.com
// Type: Info
// Envoi: Immédiat
```

## ⚙️ Commandes Artisan

### Envoi des rappels VA/VT (J-1)
```bash
php artisan send:tech-reminders
```
**Action** : Envoie un rappel automatique à tous les pilotes ayant un rendez-vous VA/VT le lendemain

### Envoi des rappels course (J-3) ⭐ NOUVEAU
```bash
php artisan send:race-reminders
php artisan send:race-reminders --days=3   # Par défaut
php artisan send:race-reminders --days=7   # Rappel J-7
```
**Action** : Envoie un rappel aux pilotes inscrits à une course dans X jours

### Tâches planifiées
Les commandes sont automatiquement planifiées dans `routes/console.php` :
```php
// Rappels J-3 chaque jour à 9h
Schedule::command('send:race-reminders --days=3')->dailyAt('09:00');

// Rappels VA/VT chaque jour à 10h
Schedule::command('send:tech-reminders')->dailyAt('10:00');
```

**Configuration serveur** (crontab) :
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Test d'envoi d'email
```bash
# Envoyer un email de test
php artisan tinker
>>> Mail::to('votre@email.com')->send(new App\Mail\RegistrationCreated(App\Models\RaceRegistration::first()));
```

## 🔐 Vérification Email & 2FA

### Vérification Email
**Activation** : Déjà activée via Fortify
```php
// config/fortify.php
Features::emailVerification()
```

**Comportement** :
- Email de vérification envoyé automatiquement à l'inscription
- L'utilisateur doit cliquer sur le lien pour activer son compte
- Accès restreint tant que l'email n'est pas vérifié

### Double Authentification (2FA)
**Activation** : Déjà activée via Fortify
```php
// config/fortify.php
Features::twoFactorAuthentication([
    'confirm' => true,
    'confirmPassword' => true,
])
```

**Fonctionnalités** :
- Activation optionnelle par l'utilisateur dans son profil
- Support TOTP (Google Authenticator, Authy, etc.)
- Codes de récupération générés automatiquement
- Confirmation par mot de passe requise

**Accès utilisateur** :
`/user/two-factor-authentication`

## 🏗️ Architecture Technique

### Events & Listeners

| Event | Listener | Email | Trigger |
|-------|----------|-------|---------|
| `RegistrationCreated` | `SendRegistrationCreatedNotification` | `RegistrationCreated` | Use Case `SubmitRegistration` |
| `RegistrationAccepted` | `SendRegistrationAcceptedNotification` | `RegistrationAccepted` | Use Case `ValidateRegistration` |
| `RegistrationRefused` | `SendRegistrationRefusedNotification` | `RegistrationRefused` | Use Case `ValidateRegistration` |
| `PaymentConfirmed` | `SendPaymentConfirmation` | `PaymentConfirmed` | Use Cases `HandleStripeWebhook`, `RecordManualPayment` |
| `PaymentConfirmed` | `SendECardAfterPayment` | `ECardMail` | Use Cases `HandleStripeWebhook`, `RecordManualPayment` |
| `TechInspectionCompleted` | `SendTechInspectionNotification` | `TechInspectionCompleted` | Use Case `RecordTechInspection` |
| `EngagementFormSigned` | `SendEngagementSignedNotification` | `EngagementFormSigned` | Model `EngagementForm::sign()` |
| `RaceOpened` | `SendRaceOpenedNotification` | `RaceOpenedMail` | Livewire `Admin\Races\Index::updateStatus()` |
| `RaceCancelled` | `SendRaceCancelledNotification` | `RaceCancelledMail` | Livewire `Admin\Races\Index::updateStatus()` |
| `ResultsPublished` | `SendResultsPublishedNotification` | `ResultsPublishedMail` | Use Case `PublishRaceResults` |
| `PilotRegistered` | `SendWelcomePilotNotification` | `WelcomePilotMail` | Action `CreateNewUser` |

### Queues
Tous les Listeners implémentent `ShouldQueue` pour un traitement asynchrone optimal.

#### Mode SYNC (Recommandé pour hébergement mutualisé comme O2Switch)
```env
QUEUE_CONNECTION=sync
```
- ✅ Fonctionne sur tous les hébergements
- ✅ Pas besoin de worker ou Supervisor
- ✅ Emails envoyés immédiatement
- ⚠️ Légère latence lors des inscriptions (2-3 secondes)

**Configuration** : Utiliser `QUEUE_CONNECTION=sync` sur O2Switch

#### Mode DATABASE (Pour VPS avec Supervisor)
```env
QUEUE_CONNECTION=database
```
- ✅ Traitement asynchrone, pas de latence
- ✅ Meilleure performance
- ❌ Nécessite un worker permanent : `php artisan queue:work`
- ❌ Nécessite Supervisor en production

**Traitement** :
```bash
# En développement
php artisan queue:work

# En production VPS (via Supervisor)
php artisan queue:work --tries=3 --timeout=90
```

#### Mode REDIS (Pour infrastructure avancée)
```env
QUEUE_CONNECTION=redis
```
- ✅ Performance maximale
- ✅ Gestion avancée des jobs
- ❌ Nécessite Redis installé
- ❌ Non disponible sur hébergement mutualisé

### Layout Email
Template HTML responsive avec design professionnel :
- **Header** : Logo Run200 avec dégradé violet
- **Body** : Contenu structuré avec boxes colorées
- **Footer** : Informations de contact
- **Responsive** : Adapté mobile et desktop
- **Dark mode** : Support du mode sombre

Fichier : `resources/views/emails/layout.blade.php`

## 📊 Modèle de Données

### Table `race_notifications`
```sql
- id
- race_id (foreign key)
- created_by (foreign key users)
- subject (string)
- message (text)
- type (enum: info, warning, success)
- recipients (json) - null = tous
- scheduled_at (datetime nullable)
- sent_at (datetime nullable)
- sent_count (integer)
- timestamps
```

## 🧪 Tests

### Test manuel complet
1. Créer une inscription → Vérifier email "Inscription créée"
2. Payer l'inscription → Vérifier email "Paiement confirmé"
3. Valider l'inscription (staff) → Vérifier email "Inscription acceptée"
4. J-1 → Lancer `php artisan send:tech-reminders` → Vérifier email "Rappel VA/VT"
5. Faire le contrôle technique → Vérifier email "Contrôle technique terminé"
6. Signer la feuille d'engagement → Vérifier email "Feuille d'engagement signée"

### Vérifier Mailtrap
1. Aller sur https://mailtrap.io
2. Se connecter avec les identifiants
3. Vérifier la boîte de réception Sandbox
4. Tous les emails de test apparaîtront ici

## 🚀 Mise en Production

### Configuration selon l'hébergement

#### O2Switch (Hébergement Mutualisé) - RECOMMANDÉ
```env
QUEUE_CONNECTION=sync
MAIL_MAILER=smtp
MAIL_HOST=smtp.votrefournisseur.com
MAIL_PORT=587
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls
```

**Avantages** :
- ✅ Aucune configuration complexe
- ✅ Fonctionne immédiatement
- ✅ Pas besoin de Supervisor ou worker

**Inconvénients** : - UNIQUEMENT VPS
⚠️ **Pas nécessaire sur O2Switch** (utiliser `QUEUE_CONNECTION=sync`)

Pour VPS uniquement :
- ⚠️ Latence de 2-3 secondes lors des inscriptions (temps d'envoi email)

**Cron pour les rappels VA/VT** :
```bash
# Dans le cPanel O2Switch, ajouter un cron job quotidien à 10h
0 10 * * * cd /home/votrecompte/public_html && php artisan send:tech-reminders >> /dev/null 2>&1
```

#### VPS/Serveur Dédié (Pour gros volumes)
```env
QUEUE_CONNECTION=redis  # ou database
MAIL_MAILER=smtp
```

### 1. Configuration Email Production
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.votrefournisseur.com
MAIL_PORT=587
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@run200.com"
MAIL_FROM_NAME="Run200 Manager"
```

### 2. Configuration Queue
Utiliser Redis ou SQS en production :
```env
QUEUE_CONNECTION=redis
```

### 3. Supervisor (Worker Permanent)
```ini
[program:run200-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600

#### Sur O2Switch (cPanel)
1. Aller dans "Tâches Cron" du cPanel
2. Ajouter cette commande (exécution quotidienne à 10h) :
```bash
0 10 * * * cd /home/votrecompte/public_html && php artisan send:tech-reminders
```

#### Sur VPS
```

### 4. Planification Cron
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Laravel Scheduler
Dans `app/Console/Kernel.php` :
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('send:tech-reminders')->dailyAt('10:00');
}
```

## 📝 Personnalisation

### Ajouter un nouvel email
1. Créer le Mailable : `app/Mail/VotreEmail.php`
2. Créer la vue : `resources/views/emails/votre-email.blade.php`
3. Créer l'Event : `app/Events/VotreEvent.php`
4. Créer le Listener : `app/Listeners/VotreListener.php`
5. Enregistrer dans `EventServiceProvider`
6. Dispatcher l'event au bon endroit

### Modifier un email existant
Éditer uniquement la vue Blade dans `resources/views/emails/`

## ⚠️ Troubleshooting

### Les emails ne partent pas
1. Vérifier les identifiants Mailtrap dans `.env`
2. Vérifier que le queue worker tourne : `php artisan queue:work`
3. Vérifier les logs : `storage/logs/laravel.log`

### Les events ne se déclenchent pas
1. Vérifier que `EventServiceProvider` est bien enregistré dans `bootstrap/providers.php`
2. Vérifier que les events sont bien dispatchés dans les Use Cases
3. Clear cache : `php artisan config:clear && php artisan cache:clear`

### Les emails sont en spam
1. Configurer SPF/DKIM/DMARC sur votre domaine
2. Utiliser un service email professionnel (SendGrid, Mailgun, AWS SES)
3. Éviter les mots "spam" dans les sujets

## 📞 Support

Pour toute question : contact@run200.com

---

**Documentation générée le 26/01/2026** 🏁
