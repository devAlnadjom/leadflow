LeadGen + Mini CRM SaaS
Spécification Produit – Version Enrichie

Stack: Laravel + Inertia.js + Vue.js (Shadcn UI + Tailwind)

1. Vision du Projet
Créer une plateforme SaaS permettant aux entreprises (construction, esthétique, services, etc.) de :
- Générer des leads via un widget intelligent
- Centraliser et gérer ces leads dans un mini CRM
- Envoyer des soumissions (Devis) avec signature en ligne
- Générer des factures
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

5. Module 3 – Gestion des Leads (Mini CRM) (Réalisé ✅)
- Dashboard Lead ("Show") : Vue dashboard avec sidebar (coordonnées) et système d'onglets (Activité, Devis, Notes).
- Vue Double (Switch) :
  1. Kanban (Drag & Drop) : Visualisation par colonnes (Nouveau, Contacté, Qualifié, Gagné, Perdu).
  2. Liste (Responsive) : Tableau optimisé pour mobile avec changement de statut rapide.
- Timeline d'Activité (Réalisé ✅) : Agrégation centralisée et chronologique de la création du lead, de l'ajout de notes/appels/emails, et de la création de devis dans un onglet "Détails & Activité".
- Notes & Activités Internes (Réalisé ✅) : Ajout/Suppression de notes, d'appels, d'emails et de rendez-vous avec code couleur et icônes (Note générique, Appel effectif, Email envoyé, Rendez-vous), traçabilité par utilisateur.
- Isolation SaaS (Réalisé ✅) : Utilisation du trait `BelongsToCompany` pour garantir que chaque entreprise ne voit que ses propres leads et notes.

6. Module 4 – Soumissions (Devis) (Réalisé ✅)

  6.1 Modèle de données
  - Table `quotes` : quote_number, status, public_uid (UUID), company_id, lead_record_id, subtotal, tax1_amount, tax2_amount, total, expire_at, notes (internes), terms (conditions).
  - Table `quote_items` : description, quantity, unit_price, total, sort_order.
  - Modèle `Quote` : Trait `BelongsToCompany`, UUID auto-généré à la création via `boot()`.
  - Modèle `QuoteItem` : Relation `belongsTo(Quote)`.

  6.2 Cycle de vie des statuts
  - draft → sent → accepted / rejected / expired
  - Seuls les devis en statut "sent" peuvent être acceptés/refusés par le client.

  6.3 Page Index des Devis (/quotes) (Réalisé ✅)
  - Entrée "Devis" dans le menu de navigation (sidebar).
  - 5 cartes statistiques cliquables (Brouillon, Envoyé, Accepté, Refusé, Expiré) filtrant directement la liste.
  - Tableau complet : Numéro, Client, Statut (badge coloré avec icône), Montant, Expiration, Date création.
  - Barre de filtres : Recherche par numéro/nom/email + filtre par statut.
  - Menu d'actions par ligne (Modifier, Voir le lead, Supprimer).
  - Pagination + montant total affiché en bas.
  - Design identique à la page Leads (même composants Shadcn, même structure).

  6.4 Création de devis (/leads/{id}/quotes/create) (Réalisé ✅)
  - Formulaire dynamique avec ajout/suppression de lignes d'articles.
  - Calculs en temps réel : sous-total, taxes (basées sur les CompanySettings), total.
  - Numéro de devis auto-suggéré (préfixe entreprise + incrément).
  - Sidebar : Paramètres (numéro, date d'expiration, statut), Carte Total animée, Notes internes.
  - Validation côté serveur + Transaction SQL pour l'intégrité des données.

  6.5 Modification de devis (/quotes/{id}/edit) (Réalisé ✅)
  - Même interface que la création, pré-remplie avec les données existantes.
  - Badge de statut visuel dans l'en-tête.
  - Bouton "Supprimer" avec confirmation.
  - Gestion complète des statuts (Brouillon, Envoyé, Accepté, Refusé, Expiré).
  - Carte "Lien Public" avec URL complète cliquable, bouton copier, et lien "Aperçu public".
  - Synchronisation des articles via transaction (delete + recreate).

  6.6 Vue publique du devis (/devis/{uid}) (Réalisé ✅)
  - Accessible SANS authentification via l'UUID unique du devis.
  - Contourne le `CompanyScope` via `withoutGlobalScope()`.
  - Branding dynamique : couleur primaire de l'entreprise, logo, coordonnées.
  - Ruban coloré en haut de page aux couleurs de l'entreprise.
  - Bannière de statut adaptative (En attente → bleu, Accepté → vert, Refusé → rouge, Expiré → ambre, Brouillon → gris).
  - Tableau détaillé des prestations avec sous-totaux et taxes.
  - Conditions générales affichées si renseignées.
  - Boutons d'action : "Accepter le Devis" (couleur entreprise) et "Refuser" (rouge) — uniquement si statut = "sent".
  - Confirmation visuelle après réponse (message de remerciement ou de refus).
  - Route POST rate limitée (throttle:10,1) pour la réponse client.

  6.7 Contrôleurs
  - `QuoteController` : index, create, store, edit, update, destroy.
  - `PublicQuoteController` : show (public), respond (accept/reject).

  6.8 Routes
  - Authentifiées : GET /quotes, GET /leads/{id}/quotes/create, POST /leads/{id}/quotes, GET /quotes/{id}/edit, PUT /quotes/{id}, DELETE /quotes/{id}.
  - Publiques : GET /devis/{uid}, POST /devis/{uid}/respond (throttled).

7. Module 5 – Facturation (À VENIR ⏳)
- Conversion Devis -> Facture.
- Statuts de paiement.

8. Module 6 – Mini Page SEO (⏳)
- Landing page publique pour chaque entreprise générée automatiquement.

9. Roadmap Technique (Mise à jour)
- [x] Onboarding Wizard (Premium UX)
- [x] Widget Builder (Sécurisé & Honeypot)
- [x] CRM Pipeline (Kanban + List + Isolation SaaS)
- [x] Notes Internes
- [x] Module Devis (Quotes) : CRUD complet, Index, Vue publique, Signature client
- [ ] Module Devis : Génération PDF
- [ ] Module Devis : Envoi par email
- [ ] Module Facturation (Invoices)
- [ ] Portail SEO & Annuaire

10. Multi-tenant Isolation (Implémenté)
- `CompanyScope` : Filtrage global automatique sur toutes les requêtes Eloquent.
- `BelongsToCompany` : Trait gérant l'auto-injection du `company_id` à la création des records.
- Appliqué sur : `LeadRecord`, `LeadForm`, `LeadNote`, `Quote`.
- Contournement sécurisé via `withoutGlobalScope()` pour les pages publiques (devis client).

11. Fichiers Clés
- `app/Models/Quote.php` : Modèle devis (BelongsToCompany, UUID, relations).
- `app/Models/QuoteItem.php` : Ligne d'article de devis.
- `app/Http/Controllers/QuoteController.php` : CRUD complet devis (index, create, store, edit, update, destroy).
- `app/Http/Controllers/PublicQuoteController.php` : Vue publique et réponse client.
- `resources/js/Pages/quotes/Index.vue` : Liste des devis avec stats et filtres.
- `resources/js/Pages/quotes/Create.vue` : Formulaire de création de devis.
- `resources/js/Pages/quotes/Edit.vue` : Formulaire de modification de devis.
- `resources/js/Pages/quotes/Public.vue` : Page publique de signature client.
- `resources/js/Pages/leads/Show.vue` : Fiche lead avec onglet Devis intégré.
- `resources/js/components/AppSidebar.vue` : Navigation avec entrée Devis.
