<?php

namespace Database\Seeders;

use App\Models\WidgetTemplate;
use Illuminate\Database\Seeder;

class WidgetTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'                => 'Contact rapide',
                'category'            => 'general',
                'icon'                => '📞',
                'description'         => 'Formulaire simple : nom, téléphone, email.',
                'layout_mode'         => 'stack',
                'submit_button_label' => 'Envoyer',
                'fields'              => [
                    ['label' => 'Nom complet',      'type' => 'text',  'required' => true,  'placeholder' => 'Jean Dupont',       'options' => []],
                    ['label' => 'Téléphone',         'type' => 'tel',   'required' => true,  'placeholder' => '514 000-0000',      'options' => []],
                    ['label' => 'Courriel',          'type' => 'email', 'required' => false, 'placeholder' => 'jean@exemple.com',  'options' => []],
                ],
            ],
            [
                'name'                => 'Demande de devis — Construction',
                'category'            => 'construction',
                'icon'                => '🏗️',
                'description'         => 'Formulaire adapté aux entreprises de construction et rénovation.',
                'layout_mode'         => 'slider',
                'submit_button_label' => 'Soumettre ma demande',
                'fields'              => [
                    ['label' => 'Nom complet',          'type' => 'text',     'required' => true,  'placeholder' => 'Jean Dupont',     'options' => []],
                    ['label' => 'Téléphone',             'type' => 'tel',      'required' => true,  'placeholder' => '514 000-0000',    'options' => []],
                    ['label' => 'Courriel',              'type' => 'email',    'required' => false, 'placeholder' => '',                'options' => []],
                    ['label' => 'Type de projet',        'type' => 'select',   'required' => true,  'placeholder' => '',                'options' => ['Rénovation intérieure', 'Construction neuve', 'Toiture', 'Fondation', 'Autre']],
                    ['label' => 'Description du projet', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Décrivez votre projet en quelques mots…', 'options' => []],
                ],
            ],
            [
                'name'                => 'Prise de rendez-vous — Esthétique',
                'category'            => 'esthetic',
                'icon'                => '💆',
                'description'         => 'Formulaire pour salons, spas et services de beauté.',
                'layout_mode'         => 'slider',
                'submit_button_label' => 'Réserver',
                'fields'              => [
                    ['label' => 'Prénom',             'type' => 'text',     'required' => true,  'placeholder' => 'Marie', 'options' => []],
                    ['label' => 'Téléphone',           'type' => 'tel',      'required' => true,  'placeholder' => '514 000-0000', 'options' => []],
                    ['label' => 'Service souhaité',    'type' => 'select',   'required' => true,  'placeholder' => '',     'options' => ['Coiffure', 'Soins du visage', 'Manucure', 'Massage', 'Autre']],
                    ['label' => 'Disponibilités',      'type' => 'checkbox', 'required' => false, 'placeholder' => '',     'options' => ['Matin', 'Après-midi', 'Soirée', 'Weekend']],
                    ['label' => 'Note additionnelle',  'type' => 'textarea', 'required' => false, 'placeholder' => 'Précisez votre demande…', 'options' => []],
                ],
            ],
            [
                'name'                => 'Évaluation immobilière',
                'category'            => 'real_estate',
                'icon'                => '🏠',
                'description'         => 'Formulaire pour agences immobilières et courtiers.',
                'layout_mode'         => 'slider',
                'submit_button_label' => 'Demander une évaluation',
                'fields'              => [
                    ['label' => 'Nom complet',     'type' => 'text',   'required' => true,  'placeholder' => 'Jean Dupont',      'options' => []],
                    ['label' => 'Téléphone',        'type' => 'tel',    'required' => true,  'placeholder' => '514 000-0000',     'options' => []],
                    ['label' => 'Courriel',         'type' => 'email',  'required' => true,  'placeholder' => 'jean@exemple.com', 'options' => []],
                    ['label' => 'Adresse du bien',  'type' => 'text',   'required' => true,  'placeholder' => '123 rue Principale, Montréal', 'options' => []],
                    ['label' => 'Type de propriété','type' => 'radio',  'required' => true,  'placeholder' => '',                 'options' => ['Maison', 'Condo', 'Terrain', 'Commercial', 'Autre']],
                ],
            ],
            [
                'name'                => 'Devis automobile',
                'category'            => 'auto',
                'icon'                => '🚗',
                'description'         => 'Formulaire pour garages, carrosseries et ateliers mécaniques.',
                'layout_mode'         => 'stack',
                'submit_button_label' => 'Demander un devis',
                'fields'              => [
                    ['label' => 'Nom complet',         'type' => 'text',     'required' => true,  'placeholder' => 'Jean Dupont',   'options' => []],
                    ['label' => 'Téléphone',            'type' => 'tel',      'required' => true,  'placeholder' => '514 000-0000',  'options' => []],
                    ['label' => 'Marque et modèle',    'type' => 'text',     'required' => true,  'placeholder' => 'Toyota Corolla 2020', 'options' => []],
                    ['label' => 'Type de service',     'type' => 'select',   'required' => true,  'placeholder' => '',               'options' => ['Changement d\'huile', 'Freins', 'Pneus', 'Carrosserie', 'Diagnostic', 'Autre']],
                    ['label' => 'Description',         'type' => 'textarea', 'required' => false, 'placeholder' => 'Décrivez le problème…', 'options' => []],
                ],
            ],
            [
                'name'                => 'Demande de service général',
                'category'            => 'service',
                'icon'                => '📋',
                'description'         => 'Formulaire polyvalent adapté à tout type de service.',
                'layout_mode'         => 'stack',
                'submit_button_label' => 'Envoyer ma demande',
                'fields'              => [
                    ['label' => 'Nom complet',          'type' => 'text',     'required' => true,  'placeholder' => 'Jean Dupont',      'options' => []],
                    ['label' => 'Téléphone',             'type' => 'tel',      'required' => true,  'placeholder' => '514 000-0000',     'options' => []],
                    ['label' => 'Courriel',              'type' => 'email',    'required' => false, 'placeholder' => 'jean@exemple.com', 'options' => []],
                    ['label' => 'Votre message',         'type' => 'textarea', 'required' => true,  'placeholder' => 'Comment peut-on vous aider ?', 'options' => []],
                ],
            ],
        ];

        foreach ($templates as $template) {
            WidgetTemplate::updateOrCreate(
                ['name' => $template['name'], 'is_system' => true, 'company_id' => null],
                array_merge($template, ['is_system' => true, 'company_id' => null])
            );
        }
    }
}
