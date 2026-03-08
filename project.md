LeadGen + Mini CRM SaaS
Spécification Produit – Version Enrichie

Stack: Laravel + Inertia.js + Vue.js (Shadcn UI + Tailwind)

1. Vision du Projet
Créer une plateforme SaaS permettant aux entreprises (construction, esthétique, services, etc.) de :
- Générer des leads via un widget intelligent
- Centraliser et gérer ces leads dans un mini CRM
- Envoyer des soumissions (Devis)
- Générer des factures
- Optionnellement disposer d’une page publique SEO
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
- Notes Internes (Réalisé ✅) : Ajout/Suppression de commentaires confidentiels par lead, traçabilité par utilisateur.
- Isolation SaaS (Réalisé ✅) : Utilisation du trait `BelongsToCompany` pour garantir que chaque entreprise ne voit que ses propres leads et notes.

6. Module 4 – Soumissions (Devis) (À VENIR ⏳)
- Création de soumission depuis la fiche lead.
- Lignes d'articles (Description, Quantité, Prix, Taxes).
- Envoi par email avec lien unique.
- Page de signature/acceptation en ligne.

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
- [ ] Module Devis (Quotes) & PDF
- [ ] Module Facturation (Invoices)
- [ ] Portail SEO & Annuaire

10. Multi-tenant Isolation (Implémenté)
- `CompanyScope` : Filtrage global automatique sur toutes les requêtes Eloquent.
- `BelongsToCompany` : Trait gérant l'auto-injection du `company_id` à la création des records.
