<?php

namespace App\Http\Controllers;

use App\Models\WidgetTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WidgetTemplateController extends Controller
{
    /**
     * List system templates + company custom templates.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company?->id;

        $templates = WidgetTemplate::availableFor($companyId)
            ->orderByDesc('is_system')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (WidgetTemplate $t) => [
                'id'                  => $t->id,
                'name'                => $t->name,
                'category'            => $t->category,
                'icon'                => $t->icon,
                'description'         => $t->description,
                'layout_mode'         => $t->layout_mode,
                'submit_button_label' => $t->submit_button_label,
                'fields'              => $t->fields,
                'is_system'           => $t->is_system,
                'company_id'          => $t->company_id,
            ]);

        return response()->json($templates);
    }

    /**
     * Save a new custom template for this company.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'category'            => ['nullable', 'string', 'max:50'],
            'icon'                => ['nullable', 'string', 'max:10'],
            'description'         => ['nullable', 'string', 'max:255'],
            'layout_mode'         => ['required', 'in:stack,slider'],
            'submit_button_label' => ['nullable', 'string', 'max:100'],
            'fields'              => ['required', 'array', 'min:1'],
            'fields.*.label'      => ['required', 'string'],
            'fields.*.type'       => ['required', 'in:text,email,tel,textarea,select,radio,checkbox'],
            'fields.*.required'   => ['boolean'],
            'fields.*.placeholder'=> ['nullable', 'string'],
            'fields.*.options'    => ['array'],
        ]);

        $company = $request->user()->company;

        $template = WidgetTemplate::create([
            'company_id'          => $company->id,
            'name'                => $validated['name'],
            'category'            => $validated['category'] ?? 'custom',
            'icon'                => $validated['icon'] ?? '⭐',
            'description'         => $validated['description'] ?? null,
            'layout_mode'         => $validated['layout_mode'],
            'submit_button_label' => $validated['submit_button_label'] ?? 'Envoyer',
            'fields'              => $validated['fields'],
            'is_system'           => false,
        ]);

        return response()->json([
            'id'      => $template->id,
            'name'    => $template->name,
            'message' => 'Template sauvegardé avec succès.',
        ], 201);
    }

    /**
     * Delete a custom company template (cannot delete system templates).
     */
    public function destroy(Request $request, WidgetTemplate $widgetTemplate): RedirectResponse|JsonResponse
    {
        $company = $request->user()->company;

        if ($widgetTemplate->is_system || $widgetTemplate->company_id !== $company->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $widgetTemplate->delete();

        return response()->json(['message' => 'Template supprimé.']);
    }
}
