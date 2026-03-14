LeadGen + Mini CRM SaaS
Spécification Produit – Version Enrichie

Stack: Laravel + Inertia.js + Vue.js (Shadcn UI + Tailwind)

1. Vision du Projet
Créer une plateforme SaaS permettant aux entreprises (construction, esthétique, services, etc.) de :
- Générer des leads via un widget intelligent
- Centraliser et gérer ces leads dans un mini CRM
- Gérer une base de clients avec notes (Mini CRM intégré)
- Envoyer des soumissions (Devis) avec signature en ligne
- Générer des factures professionnelles avec impressions
- Optionnellement disposer d'une page publique SEO
- Être listées dans un mini-portail local

2. Architecture Générale
- Backend : Laravel
- Frontend : Vue.js via Inertia.js (Design Premium avec Shadcn UI)
- Base de données : SQLite (Dev) / MySQL (Prod)
- Multi-tenant : Isolation stricte par `company_id` via Global Scopes & Traits.
- Sécurité : Rate Limiting (Throttle) sur les APIs publiques et Honeypot anti-spam.

3. Module 1 – Onboarding SaaS (Réalisé ✅)
- Wizard d'onboarding en 3 étapes :
  1. Informations Entreprise (Nom, Tel, Email, Industrie, Adresse, Zones desservies).
  2. Identité Visuelle (Couleurs Primaire/Secondaire personnalisables).
  3. Facturation (Devises, Préfixes Devis/Factures, Gestion de 2 taxes ex: TPS/TVQ à 3 décimales).

4. Module 2 – Générateur de Widget Lead (Réalisé ✅)
- UI de création avancée (Shadcn) avec prévisualisation.
- Types de champs : Text, Email, Tel (avec inputmode/autocomplete), Select, Radio, Checkbox, Textarea.
- Sécurité : Honeypot invisible et Rate limit (10 requêtes/min par IP).
- Dashboard de gestion des widgets.

5. Module 3 – Gestion des Leads & Clients (Mini CRM) (Réalisé ✅)
- Base Clients (Réalisé ✅) : Table `clients`, gestion des informations clients, liaison avec led leads, factures et devis. Notes internes sur les clients (`client_notes`).
- Dashboard Lead ("Show") : Vue dashboard avec sidebar (coordonnées) et système d'onglets (Activité, Devis, Notes).
- Vue Double (Switch) :
  1. Kanban (Drag & Drop) : Visualisation par colonnes (Nouveau, Contacté, Qualifié, Gagné, Perdu).
  2. Liste (Responsive) : Tableau optimisé pour mobile avec changement de statut rapide.
- Timeline d'Activité (Réalisé ✅) : Agrégation centralisée et chronologique de la création du lead, de l'ajout de notes/appels/emails, et de la création de devis dans un onglet "Détails & Activité".
- Notes & Activités Internes (Réalisé ✅) : Ajout/Suppression de notes, d'appels, d'emails et de rendez-vous avec code couleur et icônes, traçabilité par utilisateur (Lead et Client).
- Isolation SaaS (Réalisé ✅) : Utilisation du trait `BelongsToCompany` pour garantir que chaque entreprise ne voit que ses propres leads, clients et notes.

6. Module 4 – Soumissions (Devis) (Réalisé ✅)

  6.1 Modèle de données
  - Table `quotes` : quote_number, status, public_uid (UUID), company_id, lead_record_id, client_id, subtotal, tax1_amount, tax2_amount, total, expire_at, notes (internes), terms (conditions).
  - Table `quote_items` : description, quantity, unit_price, total, sort_order.
  - Modèle `Quote` : Trait `BelongsToCompany`, UUID auto-généré à la création.

  6.2 Cycle de vie des statuts
  - draft → sent → accepted / rejected / expired

  6.3 Page Index des Devis (/quotes) (Réalisé ✅)
  - 5 cartes statistiques cliquables (Brouillon, Envoyé, Accepté, Refusé, Expiré).
  - Tableau complet optimisé.
  - Menu d'actions par ligne.

  6.4 Création de devis (Réalisé ✅)
  - Formulaire dynamique avec calculs en temps réel : sous-total, taxes, total.
  - Numéro de devis auto-suggéré.
  - Sélection directe du client associé.

  6.5 Modification de devis (Réalisé ✅)
  - Même interface que la création, synchronisation des articles via transaction.
  - Gestion des statuts et statut public.

  6.6 Vue publique du devis (Réalisé ✅)
  - Accessible SANS authentification via l'UUID.
  - Branding dynamique : couleur primaire, logo, coordonnées.
  - Boutons d'action : "Accepter" (couleur entreprise) et "Refuser" (rouge).
  - Confirmation visuelle et sécurité anti-spam.

7. Module 5 – Facturation (Réalisé ✅)
- Table `invoices` : invoice_number, status, public_uid (UUID), company_id, client_id, subtotal, tax1_amount, tax2_amount, total, issue_date, due_date, notes.
- Table `invoice_items` : Lignes associées.
- Création / Liste de factures : Même philosophie intuitive que les devis.
- Aperçu (Show) optimisé pour impression : Design document officiel avec bouton "Imprimer" et style CSS dédié pour masquer l'interface (`print:hidden`).
- Mentions légales multiples (JSON) stockées sur le profil de l'entreprise et affichées en pied de page.
- Traductions i18n (Français, Anglais) implémentées pour les factures (Messages, Boutons, Libellés).
- Statuts de paiement gérés (Brouillon, Envoyée, Payée, En retard, Annulée).

8. Module 6 – Mini Page SEO (⏳)
- Landing page publique pour chaque entreprise générée automatiquement.

9. Roadmap Technique (Mise à jour)
- [x] Onboarding Wizard (Premium UX)
- [x] Widget Builder (Sécurisé & Honeypot)
- [x] CRM Pipeline (Kanban + List + Isolation SaaS)
- [x] Gestion des Clients (CRUD, Notes, Liaison Factures/Devis)
- [x] Notes Internes
- [x] Module Devis (Quotes) : CRUD complet, Index, Vue publique, Signature client
- [x] Module Facturation (Invoices) : Impression, Mentions légales, i18n
- [x] Système de Tags polymorphique (Leads & Clients)
- [x] Notifications In-App (cloche) + Email (Immédiat ou Groupé/Heure)
- [x] CORS configuré (widget ouvert, app restreinte)
- [x] Scheduler cron-based (queue:work --stop-when-empty toutes les 5 min)
- [ ] Module SuperAdmin (Option A — voir section 12)
- [ ] Génération PDF natif (Back-end) ou Envoi par Email direct
- [ ] Portail SEO & Annuaire

10. Multi-tenant Isolation (Implémenté)
- `CompanyScope` : Filtrage global automatique sur toutes les requêtes Eloquent.
- `BelongsToCompany` : Trait gérant l'auto-injection du `company_id`.
- Appliqué sur : `LeadRecord`, `LeadForm`, `LeadNote`, `Quote`, `Client`, `ClientNote`, `Invoice`.
- Contournement sécurisé via `withoutGlobalScope()` pour les pages publiques.
- Le SuperAdmin contourne le CompanyScope pour voir toutes les données.

11. Fichiers Clés
- `app/Models/Invoice.php` : Modèle facture avec UUID et scope.
- `app/Http/Controllers/InvoiceController.php` : CRUD facturation.
- `resources/js/Pages/invoices/Show.vue` : Vue document facture optimisée impression.
- `resources/js/Pages/Settings/Company.vue` : Gestion des Mentions légales.
- `resources/js/i18n/messages.ts` : Traductions globales (Facturation incluse).
- `config/cors.php` : CORS ouvert uniquement sur les routes widget publiques.
- `routes/console.php` : Scheduler (queue worker 5 min, notifications groupées, pruning).

---

12. Module SuperAdmin — Plan de Développement (Option A — À Réaliser)
=======================================================================

VISION
------
Un espace d'administration masqué (/admin), accessible uniquement au(x) super
administrateur(s) identifiés par un flag `is_super_admin` en base de données.
Cet espace permet de piloter la plateforme SaaS dans sa globalité, sans
être limité à une entreprise spécifique (contournement du CompanyScope).

ÉTAPE 1 — Base de données & Modèles
-------------------------------------
Migrations à créer :
  a) Ajouter `is_super_admin` (boolean, default false) sur la table `users`.
  b) Ajouter `is_active` (boolean, default true) sur la table `users`.
  c) Ajouter `is_active` (boolean, default true) sur la table `companies`.

Modèle User :
  - Ajouter `is_super_admin` et `is_active` au $fillable.
  - Ajouter méthode helper `isSuperAdmin(): bool`.

Modèle Company :
  - Ajouter `is_active` au $fillable.

Seeder SuperAdmin :
  - Créer/mettre à jour l'utilisateur super admin via DatabaseSeeder ou
    un artisan command dédié (`php artisan leadflow:make-super-admin`).

ÉTAPE 2 — Middleware & Sécurité
---------------------------------
Créer `app/Http/Middleware/EnsureSuperAdmin.php` :
  - Vérifie que Auth::user()->is_super_admin === true.
  - Sinon : abort(403) ou redirection vers le dashboard normal.
  - Enregistrer le middleware dans bootstrap/app.php sous l'alias `super.admin`.

Vérification supplémentaire :
  - Bloquer la connexion d'utilisateurs avec `is_active = false`
    (via FortifyServiceProvider ou un LoginResponse personnalisé).

ÉTAPE 3 — Routes Admin
-------------------------
Groupe de routes dans routes/web.php :
  Route::prefix('admin')
       ->middleware(['auth', 'verified', 'super.admin'])
       ->name('admin.')
       ->group(function () {
           Route::get('/', AdminController@dashboard)          → admin.dashboard
           Route::resource('companies', AdminCompanyController) → admin.companies.*
           Route::resource('users',     AdminUserController)    → admin.users.*
           Route::post('impersonate/{user}', AdminController@impersonate) → admin.impersonate
           Route::post('stop-impersonating', AdminController@stopImpersonating) → admin.stop-impersonating
       });

ÉTAPE 4 — Contrôleurs
-----------------------
AdminController (app/Http/Controllers/Admin/) :
  - dashboard() : Stats globales (nb companies actives, nb users, nb leads ce mois,
                   revenus totaux facturés ce mois, nb widgets actifs).
  - impersonate(User $user) : Stocke l'ID admin en session, connecte en tant que $user.
  - stopImpersonating() : Reconnecte en tant que l'admin original.

AdminCompanyController :
  - index()   : Liste toutes les entreprises (actives + inactives) + stats par entreprise.
  - create()  : Formulaire création entreprise (+ utilisateur propriétaire).
  - store()   : Crée l'entreprise + le user owner dans une transaction.
  - show()    : Détail d'une entreprise (users, nb leads, nb factures, revenus).
  - edit()    : Modification infos entreprise.
  - update()  : Sauvegarde.
  - toggleActive() : Active/Désactive une entreprise (et ses users par cascade optionnelle).

AdminUserController :
  - index()         : Liste tous les users (toutes entreprises confondues).
  - create()        : Formulaire création user + assignation entreprise.
  - store()         : Création user avec hachage password.
  - edit()          : Modification user (nom, email, entreprise, rôle).
  - update()        : Sauvegarde.
  - toggleActive()  : Active/Désactive un user.
  - resetPassword() : Génère et envoie un lien de reset par email.

ÉTAPE 5 — Frontend Vue.js / Inertia
--------------------------------------
Layout dédié : `resources/js/layouts/AdminLayout.vue`
  - Sidebar différente avec navigation admin uniquement.
  - Bandeau rouge/orange en haut pour rappeler visuellement qu'on est en mode Admin.
  - Lien "Quitter l'admin" vers le dashboard normal.
  - Indicateur "Ghost Mode" visible si on est en train d'usurper un user.

Pages à créer dans `resources/js/pages/admin/` :
  - Dashboard.vue    : Cartes de stats globales + graphiques (leads/semaine, revenus/mois).
  - companies/
      Index.vue      : Tableau de toutes les entreprises avec statut, actions rapides.
      Create.vue     : Formulaire création entreprise + owner.
      Show.vue       : Détail entreprise + users de l'entreprise + stats.
      Edit.vue       : Modification.
  - users/
      Index.vue      : Tableau de tous les users avec filtre par entreprise.
      Create.vue     : Formulaire création user.
      Edit.vue       : Modification user.

Composant GhostModeBanner.vue :
  - Barre orange fixe en haut de l'écran montrant "Vous êtes connecté en tant que
    [Prénom NOM] (entreprise X)" avec un bouton "Quitter le mode Ghost".
  - Visible dans tous les layouts quand la session contient `impersonating_admin_id`.

ÉTAPE 6 — Ghost Mode (Impersonation)
---------------------------------------
Mécanisme technique :
  1. Admin clique "Se connecter en tant que [user]".
  2. On stocke `session(['impersonating_admin_id' => auth()->id()])`.
  3. On appelle `Auth::loginUsingId($user->id)`.
  4. L'app se comporte exactement comme si c'était ce user.
  5. Le GhostModeBanner est affiché partout (via Inertia shared props).
  6. Clic "Quitter le mode Ghost" :
     - Récupère l'ID admin depuis la session.
     - Reconnecte l'admin.
     - Supprime la clé session.

Partage Inertia (HandleInertiaRequests.php) :
  - Ajouter `ghost_mode` : ['active' => bool, 'original_admin_name' => string].

ÉTAPE 7 — Sécurité & Edge Cases
----------------------------------
- Un super admin ne peut pas se désactiver lui-même.
- Un super admin ne peut pas usurper un autre super admin.
- Toutes les actions admin sont loggées dans `storage/logs/admin_actions.log`
  (qui, quand, quelle action, sur quel user/company).
- Le middleware `EnsureSuperAdmin` bloque toute tentative d'accès à /admin
  si l'user n'est pas super_admin, même s'il connaît l'URL.
- Si une entreprise est désactivée, ses utilisateurs ne peuvent plus se connecter.

ORDRE D'IMPLÉMENTATION RECOMMANDÉ
------------------------------------
1. Migrations (is_super_admin, is_active users & companies)
2. Middleware EnsureSuperAdmin + blocage login inactifs
3. Routes admin (squelette vide)
4. AdminController + dashboard stats
5. AdminCompanyController + pages companies
6. AdminUserController + pages users
7. Ghost Mode (Impersonation)
8. AdminLayout.vue + GhostModeBanner.vue
9. Seeder/Command pour créer le premier super admin
10. Tests manuels de l'isolation (un user normal ne peut PAS accéder à /admin)

FICHIERS À CRÉER (liste complète)
------------------------------------
Backend :
  app/Http/Middleware/EnsureSuperAdmin.php
  app/Http/Controllers/Admin/AdminController.php
  app/Http/Controllers/Admin/AdminCompanyController.php
  app/Http/Controllers/Admin/AdminUserController.php
  app/Console/Commands/MakeSuperAdmin.php

Frontend :
  resources/js/layouts/AdminLayout.vue
  resources/js/components/GhostModeBanner.vue
  resources/js/pages/admin/Dashboard.vue
  resources/js/pages/admin/companies/Index.vue
  resources/js/pages/admin/companies/Create.vue
  resources/js/pages/admin/companies/Show.vue
  resources/js/pages/admin/companies/Edit.vue
  resources/js/pages/admin/users/Index.vue
  resources/js/pages/admin/users/Create.vue
  resources/js/pages/admin/users/Edit.vue
