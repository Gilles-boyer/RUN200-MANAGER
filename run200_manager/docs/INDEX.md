# 📚 RUN200 MANAGER - INDEX DOCUMENTATION

Bienvenue dans la documentation du projet **Run200 Manager** ! Ce fichier vous guidera vers les ressources appropriées selon votre profil et vos besoins.

---

## 🎯 PAR PROFIL

### 👨‍💼 Chef de Projet / Product Owner
Vous voulez comprendre les fonctionnalités et l'état du projet ?
- **[📋 Information Projet](information_projet.md)** - Spécifications métier complètes, règles de gestion
- **[📈 État & Plan de Développement](etat_et_plan_developpement.md)** - Phases complétées, métriques, historique
- **[🚀 Évolutions & Roadmap](evolutions_et_roadmap.md)** - Fonctionnalités futures, priorisation, planning

### 👨‍💻 Développeur Backend
Vous allez coder sur le projet ?
- **[🔧 Guide Technique Développeur](guide_technique_developpeur.md)** - Setup, architecture, use cases, debugging
- **[✨ Bonnes Pratiques](bonne_pratique.md)** - Standards de code Laravel, patterns à suivre
- **[📖 Documentation Complète](documentation_complete.md)** - Référence technique exhaustive

### 🎨 Développeur Frontend / UI
Vous travaillez sur l'interface Livewire ?
- **[🔧 Guide Technique Développeur](guide_technique_developpeur.md)** - Section Composants Livewire
- **[📖 Documentation Complète](documentation_complete.md)** - Section Interfaces Utilisateur
- **[✨ Bonnes Pratiques](bonne_pratique.md)** - Standards UI/UX

### 🧪 Testeur / QA
Vous testez l'application ?
- **[📖 Documentation Complète](documentation_complete.md)** - Section Workflow Métier (scénarios de test)
- **[📈 État & Plan de Développement](etat_et_plan_developpement.md)** - Fonctionnalités à tester
- Comptes de test dans [README.md](../README.md)

### 🚀 DevOps / SysAdmin
Vous déployez l'application ?
- **[� Déploiement o2switch](DEPLOYMENT_O2SWITCH.md)** - Guide complet déploiement production
- **[📖 Documentation Complète](documentation_complete.md)** - Section Déploiement
- **[🔧 Guide Technique Développeur](guide_technique_developpeur.md)** - Configuration environnement

---

## 📂 PAR SUJET

### 🏗️ Architecture
- **Architecture Clean** → [etat_et_plan_developpement.md](etat_et_plan_developpement.md#architecture-logicielle-actuelle)
- **Modèle de données** → [documentation_complete.md](documentation_complete.md#modèle-de-données)
- **Stack technique** → [documentation_complete.md](documentation_complete.md#architecture-technique)

### 💼 Fonctionnalités Métier
- **Workflow complet** → [documentation_complete.md](documentation_complete.md#workflow-métier)
- **Règles de gestion** → [information_projet.md](information_projet.md#contraintes-métier--règles)
- **Use Cases** → [guide_technique_developpeur.md](guide_technique_developpeur.md#use-cases-métier)

### 🔐 Sécurité & Permissions
- **RBAC (Rôles/Permissions)** → [documentation_complete.md](documentation_complete.md#système-de-permissions-rbac)
- **Audit Trail** → [information_projet.md](information_projet.md#audit-trail)
- **QR Codes sécurisés** → [documentation_complete.md](documentation_complete.md#sécurité)

### 🧪 Tests
- **Organisation tests** → [documentation_complete.md](documentation_complete.md#tests)
- **Écrire des tests** → [guide_technique_developpeur.md](guide_technique_developpeur.md#testing)
- **Coverage actuel** → [etat_et_plan_developpement.md](etat_et_plan_developpement.md#métriques-actuelles)

### 🚀 Déploiement
- **Déploiement o2switch** → [DEPLOYMENT_O2SWITCH.md](DEPLOYMENT_O2SWITCH.md) ⭐ **NOUVEAU**
- **Installation locale** → [guide_technique_developpeur.md](guide_technique_developpeur.md#configuration-environnement)
- **Déploiement production** → [documentation_complete.md](documentation_complete.md#déploiement)
- **Monitoring** → [documentation_complete.md](documentation_complete.md#monitoring--maintenance)

### 🔮 Évolutions
- **Fonctionnalités futures** → [evolutions_et_roadmap.md](evolutions_et_roadmap.md#phase-8--optimisations--améliorations-à-venir)
- **Bugs connus** → [evolutions_et_roadmap.md](evolutions_et_roadmap.md#backlog-bugs-connus)
- **Idées innovantes** → [evolutions_et_roadmap.md](evolutions_et_roadmap.md#idées-innovantes)

---

## 🔍 PAR CAS D'USAGE

### "Je débute sur le projet"
1. Lire [README.md](../README.md) - Vue d'ensemble
2. Lire [information_projet.md](information_projet.md) - Comprendre le métier
3. Lire [guide_technique_developpeur.md](guide_technique_developpeur.md) - Setup local
4. Consulter [bonne_pratique.md](bonne_pratique.md) - Standards à respecter

### "Je dois implémenter une nouvelle fonctionnalité"
1. Vérifier si elle existe dans [evolutions_et_roadmap.md](evolutions_et_roadmap.md)
2. Comprendre l'architecture dans [documentation_complete.md](documentation_complete.md#architecture-technique)
3. Suivre les patterns dans [guide_technique_developpeur.md](guide_technique_developpeur.md#use-cases-métier)
4. Respecter [bonne_pratique.md](bonne_pratique.md)

### "Je dois corriger un bug"
1. Vérifier [evolutions_et_roadmap.md](evolutions_et_roadmap.md#backlog-bugs-connus)
2. Utiliser [guide_technique_developpeur.md](guide_technique_developpeur.md#debugging)
3. Écrire un test de régression

### "Je dois écrire des tests"
1. Comprendre l'organisation dans [documentation_complete.md](documentation_complete.md#tests)
2. Suivre les exemples dans [guide_technique_developpeur.md](guide_technique_developpeur.md#testing)
3. Viser 90%+ coverage

### "Je dois déployer en production"
1. Lire **[DEPLOYMENT_O2SWITCH.md](DEPLOYMENT_O2SWITCH.md)** - Guide complet o2switch
2. Vérifier la checklist de déploiement
3. Configurer les variables d'environnement
4. Tester après déploiement
3. Suivre checklist déploiement
4. Configurer monitoring

---

## 📄 FICHIERS DE DOCUMENTATION

| Fichier | Description | Audience |
|---------|-------------|----------|
| **[README.md](../README.md)** | Vue d'ensemble, installation rapide | Tous |
| **[information_projet.md](information_projet.md)** | Spécifications métier complètes | PO, Dev |
| **[etat_et_plan_developpement.md](etat_et_plan_developpement.md)** | État projet, phases, métriques | PO, PM |
| **[documentation_complete.md](documentation_complete.md)** | Référence technique exhaustive | Dev, DevOps |
| **[guide_technique_developpeur.md](guide_technique_developpeur.md)** | Guide pratique développeur | Dev |
| **[evolutions_et_roadmap.md](evolutions_et_roadmap.md)** | Fonctionnalités futures, roadmap | PO, Dev |
| **[bonne_pratique.md](bonne_pratique.md)** | Standards de code | Dev |
| **[AUDIT_COMPLET_2026.md](AUDIT_COMPLET_2026.md)** | Audit technique complet | Dev, Arch |

### Documentation système
| Fichier | Description |
|---------|-------------|
| **[systeme_gestion_paddock.md](systeme_gestion_paddock.md)** | Gestion des emplacements paddock |
| **[systeme_historique_controle_technique.md](systeme_historique_controle_technique.md)** | Historique contrôle technique |
| **[systeme_notifications_email.md](systeme_notifications_email.md)** | Système de notifications email |
| **[systeme_tableau_affichage_numerique.md](systeme_tableau_affichage_numerique.md)** | Tableau d'affichage numérique public |

### Archives
- Rapports de sprint : [archives/](archives/)

---

## 📊 STATISTIQUES PROJET

### Métriques (27 janvier 2026)
- **Tests** : 455 tests / 1180 assertions ✅
- **Modèles** : 21 modèles Eloquent
- **Use Cases** : 15 use cases métier
- **Composants Livewire** : 45 composants
- **Migrations** : 43 migrations
- **Permissions** : 34 permissions granulaires
- **Rôles** : 6 rôles
- **Phases complètes** : 9/9 (100%)

### Couverture fonctionnelle
- ✅ Gestion pilotes & véhicules
- ✅ Inscriptions & paiements (Stripe + Manuel)
- ✅ Checkpoints terrain (5 points de contrôle)
- ✅ Contrôle technique avec historique
- ✅ Fiche d'engagement PDF avec signature
- ✅ Import & publication résultats CSV
- ✅ Championnat (général + catégories)
- ✅ Gestion paddock avec plan interactif
- ✅ Dashboard analytique avec graphiques
- ✅ Notifications email automatiques
- ✅ Sécurité renforcée (rate limiting, exceptions métier)

---

## 🔗 LIENS RAPIDES

### Ressources externes
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Livewire 4 Documentation](https://livewire.laravel.com/docs/)
- [Pest Testing Framework](https://pestphp.com/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission/v6)
- [Stripe API](https://stripe.com/docs/api)

### Outils
- [Laravel Pint](https://laravel.com/docs/12.x/pint) - Formatage code
- [Laravel Telescope](https://laravel.com/docs/12.x/telescope) - Debugging
- [Ray](https://myray.app) - Debugging avancé
- [PHPStan](https://phpstan.org) - Analyse statique

---

## ❓ AIDE

### Vous ne trouvez pas l'information ?

1. **Utilisez la recherche** :
   - Ctrl+F dans ce fichier pour trouver un mot-clé
   - Grep dans les fichiers : `grep -r "votre_recherche" docs/`

2. **Consultez les sections** :
   - Architecture ? → [documentation_complete.md](documentation_complete.md)
   - Fonctionnalités ? → [information_projet.md](information_projet.md)
   - Code ? → [guide_technique_developpeur.md](guide_technique_developpeur.md)

3. **Contactez l'équipe** :
   - Email : dev@run200.example.com
   - GitHub Issues : Pour bugs et questions techniques
   - Slack : #run200-dev

---

## 🔄 MISES À JOUR

Ce document est maintenu à jour à chaque évolution majeure du projet.

**Dernière mise à jour** : 27 janvier 2026  
**Version documentation** : 4.0

---

*Bonne lecture ! 📖*
