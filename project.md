LeadGen + Mini CRM SaaS
Spécification Produit – Version Enrichie

Stack: Laravel + Inertia.js + Vue.js

1. Vision du Projet

Créer une plateforme SaaS permettant aux entreprises (construction, esthétique, services, etc.) de :

Générer des leads via un widget intelligent

Centraliser et gérer ces leads dans un mini CRM

Envoyer des soumissions

Générer des factures

Optionnellement disposer d’une page publique SEO

Être listées dans un mini-portail local

Objectif :
Offrir un écosystème complet allant de la capture du lead jusqu’à la facturation.

2. Architecture Générale
Stack Technique

Backend : Laravel

Frontend : Vue.js via Inertia.js

Base de données : MySQL / PostgreSQL

Auth : Laravel Auth (multi-tenant)

API Widget : Endpoint public sécurisé

PDF : Laravel Snappy / DomPDF

Email : Laravel Mail + Queue

Notifications : Email + (option futur SMS)

3. Module 1 – Onboarding SaaS
3.1 Inscription

Création de compte

Vérification email

Choix du plan (si applicable)

3.2 Profil Entreprise

Champs :

Nom entreprise

Téléphone

Email

Adresse

Logo

Secteur d’activité

Zones desservies

Couleurs (optionnel)

Paramètres devis/facturation

Structure DB :

users

companies

subscriptions

company_settings

4. Module 2 – Générateur de Widget Lead
4.1 Objectif

Permettre au client de générer un widget de demande de soumission intégrable sur son site.

4.2 Fonctionnement

Le client :

Crée un formulaire personnalisé

Définit les champs

Génère un script JS

Intègre le script sur son site

4.3 Champs Obligatoires

Nom

Téléphone OU Email

4.4 Champs Personnalisables

Types de champs :

Text

Email

Téléphone

Select

Radio

Checkbox

Textarea

Budget (range ou select)

Upload fichier (option futur)

4.5 Structure DB

lead_forms

lead_form_fields

leads

lead_data (JSON)

Exemple :

{
  "service": "Rénovation salle de bain",
  "budget": "15000-25000",
  "delai": "3 mois"
}
4.6 Widget

Script généré :

<script src="https://app.domain.com/widget.js" data-key="UNIQUE_KEY"></script>

Fonctionnalités :

Affichage en bas à droite

Multi-step

Responsive

Thème personnalisable

Envoi vers API sécurisée

5. Module 3 – Gestion des Leads (Mini CRM)
5.1 Notification

Lorsqu’un lead est reçu :

Email automatique

Notification dashboard

Option SMS (phase future)

5.2 Fiche Lead

Structure :

Nom

Téléphone

Email

Données JSON

Statut

Source

Date création

5.3 Enrichissement Manuel

Après appel :

Nom complet

Entreprise

Adresse

Notes

Tags

5.4 Kanban Pipeline

Statuts personnalisables :

Nouveau

Contacté

Soumission envoyée

Négociation

Accepté

Refusé

Converti

Tables :

pipelines

pipeline_stages

Drag & drop Kanban.

5.5 Activités & Commentaires

Sous-module unique :

Notes

Appels

Emails

Rendez-vous

Historique complet

Table :

activities

6. Module 4 – Soumissions (Devis)
6.1 Création de Soumission

Depuis une fiche lead :

Ajouter lignes

Description

Quantité

Prix unitaire

Taxes

Conditions

Tables :

quotes

quote_items

6.2 Envoi

Email automatique

Lien sécurisé unique

6.3 Page Publique Devis

Vue propre

Logo entreprise

Boutons :

Accepter

Refuser

Demander modification

6.4 PDF

Génération PDF

Téléchargement

Impression

7. Module 5 – Facturation
7.1 Conversion

Soumission acceptée → Facture automatique possible

Tables :

invoices

invoice_items

7.2 Fonctionnalités

Génération PDF

Historique

Statut (payée, partielle, en attente)

Option futur Stripe

8. Module 6 – Mini Page SEO (Sans Site Web)

Pour clients sans site web.

8.1 Page Personnalisée

Contenu :

Nom entreprise

Description

Services

Zones

Téléphone

Bouton demande soumission

Avis futurs

URL exemple :

app.domain.com/pro/nom-entreprise
8.2 Optimisation SEO

Meta title

Meta description

Slug optimisé

Schema local business

9. Module 7 – Mini Portail / Annuaire

Regroupement des pages clients :

Par ville

Par catégorie

Par secteur

Exemple :

/construction/montreal

/esthetique/laval

Objectif :

Générer trafic SEO global

Lead partagé (option futur)

10. Roadmap Recommandée
Phase 1 (Stabilisation)

Widget amélioré

Gestion leads complète

Kanban

Activités

Phase 2

Soumissions

PDF

Acceptation en ligne

Phase 3

Facturation

Mini page SEO

Phase 4

Portail

Paiements intégrés

SMS

Automations

11. Multi-Tenant Architecture

Chaque entreprise doit être isolée.

Approche recommandée :

company_id sur toutes les tables principales

Global scopes Laravel

Policies strictes

12. Objectif Business

Ce SaaS doit :

Augmenter la valeur moyenne client

Créer revenu mensuel récurrent

Centraliser les opérations

Réduire dépendance aux outils tiers

13. Positionnement Produit

Ce n’est PAS juste un CRM.

C’est :

Un système complet de génération + gestion + conversion de leads pour PME locales.

14. Prochaine Étape

Priorité actuelle :

→ Version enrichie LeadGen + CRM
→ Structure propre et scalable
→ UX simple et rapide
