# Module Job Board pour Drupal

_Note : Ce module a été inspiré par nos discussions concernant le groupe Atos lors de notre dernière réunion Google Meet. Il a également été conçu comme un terrain d'expérimentation pour explorer et implémenter différents types de champs avancés offerts par la Form API de Drupal._

Ce module est un Produit Minimum Viable (MVP) conçu pour Drupal 10/11 permettant de gérer un système de candidature en ligne simplifié. Il expose un formulaire public de candidature et un tableau de bord privé d'administration pour les équipes RH.

## 🚀 Fonctionnalités

- **Formulaire Public :** Permet aux candidats de soumettre :
  - Nom complet, email, numéro de téléphone (validation stricte 06/07).
  - Date de disponibilité.
  - Fichiers PDF (CV et Lettre de motivation) utilisant l'API `managed_file`.
- **Tableau de Bord RH :** Liste détaillée de toutes les candidatures, triées par date.
- **Actions Rapides (Acceptation / Rejet) :**
  - Changement de statut avec masquage immédiat des boutons (JavaScript/AJAX).
  - Envoi automatique d'email au candidat via l'API Mail de Drupal (`hook_mail`).
  - Persistance de la décision (statut) en base de données avec l'Entity API.

## ⚙️ Prérequis et Installation

1. Placez le dossier `job_board` dans `web/modules/custom/`.
2. Activez le module via Drush ou l'interface d'administration :
   ```bash
   drush en job_board -y
   ```
3. Placez le bloc **Job Application Form Block** sur la page de votre choix via `Structure > Mise en page des blocs`.

## 📧 Configuration des tests d'emails locaux (MailHog)

Étant donné que Drupal n'envoie pas d'emails "réels" en environnement de développement local par défaut, nous vous recommandons d'utiliser **MailHog** pour intercepter et visualiser les courriels transactionnels (Acceptation, Rejet).

### 1. Lancer MailHog via Docker

```bash
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

- L'interface Web de MailHog sera accessible sur : `http://localhost:8025`
- Le serveur SMTP écoutera sur le port `1025`.

### 2. Configurer Symfony Mailer dans Drupal

Si vous utilisez le module **Symfony Mailer** (recommandé) :

1. Allez dans `Configuration > Système > Mailer > Transports`.
2. Ajoutez un transport **SMTP**.
3. Renseignez la configuration suivante :
   - **Host** : `localhost` (ou `127.0.0.1`)
   - **Port** : `1025`
   - **Authentication** : Aucun (Laissez vide)
   - **Encryption** : None
4. Définissez ce transport comme le transport par défaut.
5. Effectuez une réponse depuis `/admin/config/content/job_board` pour voir les emails arriver sur `http://localhost:8025`.

## 🛠 Architecture Technique

- **ContentEntityBase** : Modélisation des données avec `target_id` pour la rétention persistante des fichiers (`setPermanent()`).
- **Domain-Driven Design (DDD)** : Séparation claire entre les requêtes HTTP (Contrôleurs) et la logique métier d'envoi d'emails (Service).
