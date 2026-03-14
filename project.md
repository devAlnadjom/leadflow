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
- Système de Templates de Widgets (Réalisé ✅) :
  - Table `widget_templates` : templates système (globaux) + templates custom par company.
  - 6 templates système pré-remplis : Contact rapide, Construction, Esthétique, Immobilier, Auto, Général.
  - Modal de sélection (avec recherche + filtres par catégorie) sur Create & Edit.
  - Modal de sauvegarde (nom, icône, catégorie) pour créer ses propres templates.
  - Chargement dynamique : pré-remplit les champs, le layout_mode et le bouton submit.

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

8. Module 6 – SuperAdmin Panel (Réalisé ✅)
- Espace d'administration protégé (`/admin`) avec middleware `EnsureSuperAdmin`.
- Dashboard global (stats : companies actives, users, leads du mois, revenus, widgets).
- Gestion des entreprises (CRUD, activation/désactivation) via `AdminCompanyController`.
- Gestion des utilisateurs (CRUD, activation/désactivation, reset password) via `AdminUserController`.
- Ghost Mode (impersonation) : connexion en tant qu'un user, bannière visible, retour admin.
- Partage Inertia `ghost_mode` dans `HandleInertiaRequests.php`.
- Blocage login des utilisateurs inactifs via middleware `EnsureUserIsActive`.
- Logging de toutes les actions admin (création, modification, toggle, ghost mode).
- Layout dédié `AdminLayout.vue` avec bandeau admin + indicateur Ghost Mode.
- Migrations : `is_super_admin` et `is_active` sur `users`, `is_active` sur `companies`.

9. Module 7 – Mini Page SEO (⏳)
- Landing page publique pour chaque entreprise générée automatiquement.

10. Roadmap Technique (Mise à jour)
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
- [x] Module SuperAdmin (Panel /admin, Ghost Mode, Stats globales, CRUD companies & users)
- [x] Système de Templates de Widgets (6 templates système + templates custom par company)
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

12. Module SuperAdmin — Réalisé ✅
===================================

IMPLÉMENTATION COMPLÈTE
------------------------
Espace d'administration sécurisé (`/admin`), accessible uniquement aux super
administrateurs via le flag `is_super_admin`. Contournement du CompanyScope
pour voir toutes les données de la plateforme.

FICHIERS BACKEND CRÉÉS
------------------------
  app/Http/Middleware/EnsureSuperAdmin.php       ✅
  app/Http/Middleware/EnsureUserIsActive.php      ✅
  app/Http/Controllers/Admin/AdminController.php  ✅
  app/Http/Controllers/Admin/AdminCompanyController.php ✅
  app/Http/Controllers/Admin/AdminUserController.php    ✅

FICHIERS FRONTEND CRÉÉS
------------------------
  resources/js/layouts/AdminLayout.vue            ✅
  resources/js/pages/admin/Dashboard.vue          ✅
  resources/js/pages/admin/companies/Index.vue    ✅
  resources/js/pages/admin/companies/Create.vue   ✅
  resources/js/pages/admin/companies/Show.vue     ✅
  resources/js/pages/admin/companies/Edit.vue     ✅
  resources/js/pages/admin/users/Index.vue        ✅
  resources/js/pages/admin/users/Create.vue       ✅
  resources/js/pages/admin/users/Edit.vue         ✅

Ghost Mode intégré dans AdminLayout.vue (pas de composant séparé).

FONCTIONNALITÉS IMPLÉMENTÉES
------------------------------
  ✅ Migrations : `is_super_admin` + `is_active` sur `users`, `is_active` sur `companies`
  ✅ Middleware `EnsureSuperAdmin` (alias `super.admin` dans bootstrap/app.php)
  ✅ Middleware `EnsureUserIsActive` : bloque les users inactifs à la connexion
  ✅ Routes admin groupées sous `/admin` avec middleware `['auth','verified','super.admin']`
  ✅ AdminController : dashboard() avec stats globales, impersonate(), stopImpersonating()
  ✅ AdminCompanyController : index, create, store, show, edit, update, toggleActive
  ✅ AdminUserController : index, create, store, edit, update, toggleActive, resetPassword
  ✅ Ghost Mode : stockage session `impersonating_admin_id`, Auth::loginUsingId()
  ✅ Partage Inertia `ghost_mode` dans HandleInertiaRequests.php
  ✅ Logging de toutes les actions admin via Log::channel('single')
  ✅ Modèle User : `is_super_admin` + `is_active` en $fillable, méthode `isSuperAdmin()`
  ✅ Modèle Company : `is_active` en $fillable

NOTE : La commande Artisan `leadflow:make-super-admin` n'a pas été créée.
       Le super admin est à créer directement en base de données ou via Tinker.
