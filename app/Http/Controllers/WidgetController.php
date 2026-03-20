<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadFormRequest;
use App\Http\Requests\UpdateLeadFormRequest;
use App\Models\LeadForm;
use App\Models\LeadFormField;
use Illuminate\Contracts\View\View as BladeView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use App\Notifications\LeadReceivedNotification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WidgetController extends Controller
{
    /**
     * Serve the embeddable widget script.
     */
    public function script(): HttpResponse
    {
        $script = <<<'JS'
(function () {
    var script = document.currentScript;
    if (!script) return;

    var uid = script.getAttribute('data-key');
    if (!uid) return;

    var origin = new URL(script.src, window.location.href).origin;
    var configUrl = origin + '/widget-config?uid=' + encodeURIComponent(uid);
    var submitUrl = origin + '/widget-submit';
    var state = {
        collapsed: false,
        step: 0,
        values: {}
    };

    var css = `
        #clientux-widget-${uid} {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2147483647;
            width: 360px;
            max-width: 92vw;
            border: 1px solid #e2e8f0;
            border-top: 5px solid #0f172a;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 12px 32px rgba(0,0,0,0.16);
            padding: 18px;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            box-sizing: border-box;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        #clientux-widget-${uid}.clientux-collapsed {
            width: auto;
            max-width: none;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
        }
        #clientux-widget-${uid} * {
            box-sizing: border-box;
        }
        @media screen and (max-width: 480px) {
            #clientux-widget-${uid} {
                right: 4vw;
                left: 4vw;
                width: auto;
                max-width: 92vw;
                bottom: 15px;
            }
            #clientux-widget-${uid}.clientux-collapsed {
                left: auto;
                right: 20px;
            }
        }
        .clientux-btn {
            cursor: pointer;
            transition: filter 0.2s;
        }
        .clientux-btn:active {
            filter: brightness(0.9);
        }
        .clientux-field-label {
            display: block;
            font-size: 14px;
            margin-bottom: 6px;
            color: #334155;
            font-weight: 500;
        }
        .clientux-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 16px; /* Prevents iOS zoom */
            background: #fff;
            color: #0f172a;
            outline-color: #0f172a;
        }
        .clientux-textarea {
            min-height: 90px;
            resize: vertical;
        }
    `;

    var styleTag = document.createElement('style');
    styleTag.textContent = css;
    document.head.appendChild(styleTag);

    var container = document.createElement('div');
    container.id = 'clientux-widget-' + uid;
    document.body.appendChild(container);

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fieldMarkup(field, index) {
        var requiredAttr = field.required ? 'required' : '';
        var key = escapeHtml(field.field_key);
        var label = '<label class="clientux-field-label">'
            + escapeHtml(field.label)
            + (field.required ? ' *' : '')
            + '</label>';

        if (field.type === 'textarea') {
            return '<div style="margin-bottom:12px;">' + label
                + '<textarea name="' + key + '" ' + requiredAttr + ' class="clientux-input clientux-textarea" placeholder="' + escapeHtml(field.placeholder || '') + '"></textarea>'
                + '</div>';
        }

        if (field.type === 'select') {
            var options = (field.options || []).map(function (opt) {
                return '<option value="' + escapeHtml(opt) + '">' + escapeHtml(opt) + '</option>';
            }).join('');

            return '<div style="margin-bottom:12px;">' + label
                + '<select name="' + key + '" ' + requiredAttr + ' class="clientux-input">'
                + '<option value="">Select...</option>'
                + options
                + '</select></div>';
        }

        if (field.type === 'radio' || field.type === 'checkbox') {
            var groupName = key + (field.type === 'checkbox' ? '[]' : '');
            var groupOptions = (field.options || []).map(function (opt, optionIndex) {
                var inputId = 'clientux-' + uid + '-' + index + '-' + optionIndex;
                return '<label for="' + inputId + '" style="display:flex;align-items:center;gap:10px;font-size:15px;margin-bottom:8px;cursor:pointer;">'
                    + '<input id="' + inputId + '" type="' + field.type + '" name="' + groupName + '" value="' + escapeHtml(opt) + '" style="width:18px;height:18px;">'
                    + escapeHtml(opt)
                    + '</label>';
            }).join('');

            return '<div style="margin-bottom:12px;">' + label + groupOptions + '</div>';
        }

        var type = field.type === 'email' || field.type === 'tel' ? field.type : 'text';
        var extraAttrs = '';
        if (field.type === 'email') {
            extraAttrs = ' autocomplete="email" inputmode="email"';
        } else if (field.type === 'tel') {
            extraAttrs = ' autocomplete="tel" inputmode="tel"';
        } else if (field.type === 'text') {
            var lbl = (field.label || '').toLowerCase();
            if (lbl.indexOf('name') !== -1 || lbl.indexOf('nom') !== -1) {
                extraAttrs = ' autocomplete="name"';
            }
        }

        return '<div style="margin-bottom:12px;">' + label
            + '<input type="' + type + '" name="' + key + '" ' + requiredAttr + extraAttrs + ' placeholder="' + escapeHtml(field.placeholder || '') + '" class="clientux-input">'
            + '</div>';
    }

    function collectFieldValue(form, field) {
        var key = field.field_key;

        if (field.type === 'checkbox') {
            var checkboxes = form.querySelectorAll('input[name="' + key + '[]"]');
            if (!checkboxes.length) return null;
            return Array.prototype.slice.call(checkboxes)
                .filter(function (i) { return i.checked; })
                .map(function (i) { return i.value; });
        }

        if (field.type === 'radio') {
            var radios = form.querySelectorAll('input[name="' + key + '"]');
            if (!radios.length) return null;
            var checked = form.querySelector('input[name="' + key + '"]:checked');
            return checked ? checked.value : null;
        }

        var element = form.querySelector('[name="' + key + '"]');
        if (!element) return null;
        return element.value;
    }

    function applyFieldValue(form, field, value) {
        if (value === null || typeof value === 'undefined') {
            return;
        }

        var key = field.field_key;

        if (field.type === 'checkbox') {
            var selectedValues = Array.isArray(value) ? value : [value];
            var checkboxes = form.querySelectorAll('input[name="' + key + '[]"]');
            Array.prototype.forEach.call(checkboxes, function (checkbox) {
                checkbox.checked = selectedValues.indexOf(checkbox.value) !== -1;
            });
            return;
        }

        if (field.type === 'radio') {
            var radios = form.querySelectorAll('input[name="' + key + '"]');
            Array.prototype.forEach.call(radios, function (radio) {
                radio.checked = radio.value === value;
            });
            return;
        }

        var element = form.querySelector('[name="' + key + '"]');
        if (element) {
            element.value = String(value);
        }
    }

    function updateStateFromVisibleFields(form, visibleFields) {
        visibleFields.forEach(function (field) {
            var value = collectFieldValue(form, field);
            if (value !== null) {
                state.values[field.field_key] = value;
            }
        });
    }

    function restoreVisibleFields(form, visibleFields) {
        visibleFields.forEach(function (field) {
            applyFieldValue(form, field, state.values[field.field_key]);
        });
    }

    function isRequiredFieldFilled(field, value) {
        if (!field.required) return true;

        if (field.type === 'checkbox') {
            return Array.isArray(value) && value.length > 0;
        }

        if (field.type === 'radio') {
            return typeof value === 'string' && value.length > 0;
        }

        if (typeof value === 'string') {
            return value.trim().length > 0;
        }

        return value !== null && typeof value !== 'undefined';
    }

    function showMessage(message, isError) {
        var msg = document.getElementById('clientux-widget-msg-' + uid);
        if (!msg) return;
        msg.style.display = 'block';
        msg.style.color = isError ? '#991b1b' : '#166534';
        msg.textContent = message;
    }

    function buildPayload(allFields, form, visibleFields) {
        updateStateFromVisibleFields(form, visibleFields);

        var payload = {};
        allFields.forEach(function (field) {
            if (typeof state.values[field.field_key] === 'undefined') {
                payload[field.field_key] = field.type === 'checkbox' ? [] : null;
                return;
            }
            payload[field.field_key] = state.values[field.field_key];
        });

        var hp = form.querySelector('[name="_website_url_hp"]');
        if (hp) {
            payload['_website_url_hp'] = hp.value;
        }

        return payload;
    }

    fetch(configUrl)
        .then(function (response) {
            if (!response.ok) throw new Error('Widget config not found');
            return response.json();
        })
        .then(function (config) {
            var allFields = config.fields || [];
            var layoutMode = config.layout_mode === 'slider' ? 'slider' : 'stack';
            var title = escapeHtml(config.name || 'Lead Widget');
            var buttonLabel = escapeHtml(config.submit_button_label || 'Send request');

            function getVisibleFields() {
                if (layoutMode !== 'slider') {
                    return allFields;
                }
                var current = allFields[state.step];
                return current ? [current] : [];
            }

            function render() {
                if (state.collapsed) {
                    container.className = 'clientux-collapsed';
                    container.innerHTML =
                        '<div id="clientux-widget-toggle-' + uid + '" class="clientux-btn" style="width:56px;height:56px;border-radius:50%;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(0,0,0,0.2);">'
                        + '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>'
                        + '</div>';
                } else {
                    container.className = '';
                    var visibleFields = getVisibleFields();
                    var fieldsHtml = visibleFields.map(function (field) {
                        var index = allFields.findIndex(function (candidate) {
                            return candidate.field_key === field.field_key;
                        });
                        return fieldMarkup(field, index);
                    }).join('');

                    var isLastStep = state.step >= allFields.length - 1;
                    var sliderNavigationHtml = '';

                    if (layoutMode === 'slider' && allFields.length > 1) {
                        sliderNavigationHtml =
                            '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin:8px 0 12px;">'
                            + '<button type="button" id="clientux-widget-prev-' + uid + '" class="clientux-btn" style="background:#fff;border:1px solid #cbd5e1;color:#334155;border-radius:8px;padding:10px 14px;font-size:14px;" ' + (state.step === 0 ? 'disabled' : '') + '>Previous</button>'
                            + '<span style="font-size:13px;color:#64748b;">Step ' + (state.step + 1) + ' / ' + allFields.length + '</span>'
                            + '<button type="button" id="clientux-widget-next-' + uid + '" class="clientux-btn" style="background:#fff;border:1px solid #cbd5e1;color:#334155;border-radius:8px;padding:10px 14px;font-size:14px;" ' + (isLastStep ? 'disabled' : '') + '>Next</button>'
                            + '</div>';
                    }

                    container.innerHTML =
                        '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">'
                        + '<div style="font-size:16px;font-weight:700;color:#0f172a;">' + title + '</div>'
                        + '<button type="button" id="clientux-widget-toggle-' + uid + '" class="clientux-btn" style="background:transparent;border:0;color:#64748b;padding:4px;display:flex;">'
                        + '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
                        + '</button>'
                        + '</div>'
                        + '<div id="clientux-widget-body-' + uid + '">'
                        + (allFields.length === 0
                            ? '<p style="margin:8px 0 0;color:#64748b;font-size:14px;">No fields configured.</p>'
                            : '<form id="clientux-widget-form-' + uid + '">'
                            + '<div style="position:absolute;left:-9999px;top:-9999px;"><label for="clientux-hp-' + uid + '">Leave this field empty</label><input type="text" id="clientux-hp-' + uid + '" name="_website_url_hp" tabindex="-1" autocomplete="new-password"></div>'
                            + fieldsHtml
                            + sliderNavigationHtml
                            + ((layoutMode === 'stack' || isLastStep)
                                ? '<button type="submit" class="clientux-btn" style="width:100%;background:#0f172a;color:#fff;border:0;border-radius:8px;padding:12px;font-size:15px;font-weight:600;">' + buttonLabel + '</button>'
                                : '')
                            + '<p id="clientux-widget-msg-' + uid + '" style="display:none;margin-top:12px;font-size:14px;text-align:center;"></p>'
                            + '</form>')
                        + '</div>';
                }

                var toggle = document.getElementById('clientux-widget-toggle-' + uid);
                if (toggle) {
                    toggle.addEventListener('click', function () {
                        state.collapsed = !state.collapsed;
                        render();
                    });
                }

                var form = document.getElementById('clientux-widget-form-' + uid);
                if (!form) return;

                restoreVisibleFields(form, visibleFields);

                var previousButton = document.getElementById('clientux-widget-prev-' + uid);
                if (previousButton) {
                    previousButton.addEventListener('click', function () {
                        updateStateFromVisibleFields(form, visibleFields);
                        if (state.step > 0) {
                            state.step -= 1;
                            render();
                        }
                    });
                }

                var nextButton = document.getElementById('clientux-widget-next-' + uid);
                if (nextButton) {
                    nextButton.addEventListener('click', function () {
                        updateStateFromVisibleFields(form, visibleFields);
                        var currentField = allFields[state.step];
                        var currentValue = currentField ? state.values[currentField.field_key] : null;

                        if (currentField && !isRequiredFieldFilled(currentField, currentValue)) {
                            showMessage('Please fill this required field.', true);
                            return;
                        }

                        if (state.step < allFields.length - 1) {
                            state.step += 1;
                            render();
                        }
                    });
                }

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    var payload = buildPayload(allFields, form, visibleFields);

                    fetch(submitUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            uid: uid,
                            data: payload
                        })
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                return response.json().then(function (json) {
                                    throw new Error((json && json.message) || 'Submission failed');
                                });
                            }
                            return response.json();
                        })
                        .then(function () {
                            state.values = {};
                            if (layoutMode === 'slider') {
                                state.step = 0;
                            } else {
                                form.reset();
                            }
                            showMessage('Submission sent successfully.', false);
                            setTimeout(function() {
                                state.collapsed = true;
                                render();
                            }, 2000);
                        })
                        .catch(function (error) {
                            showMessage(error.message || 'Submission failed.', true);
                        });
                });
            }

            render();
        })
        .catch(function () {
            // Silent fail on host pages.
        });
})();

JS;

        return response($script, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Provide public widget configuration by UID.
     */
    public function config(Request $request): JsonResponse
    {
        $uid = (string) $request->query('uid', '');

        abort_if($uid === '', 404);

        $form = LeadForm::query()
            ->with('fields')
            ->where('embed_key', $uid)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'name' => $form->name,
            'submit_button_label' => $form->submit_button_label,
            'layout_mode' => $form->layout_mode,
            'fields' => $form->fields->map(fn (LeadFormField $field): array => [
                'label' => $field->label,
                'field_key' => $field->field_key,
                'type' => $field->type,
                'required' => $field->is_required,
                'placeholder' => $field->placeholder ?? '',
                'options' => $field->options ?? [],
            ])->values(),
        ]);
    }

    /**
     * Display the company widgets.
     */
    public function index(Request $request): Response
    {
        $company = $request->user()->company;

        $leadForms = $company->leadForms()
            ->withCount('fields')
            ->latest()
            ->get()
            ->map(function (LeadForm $leadForm): array {
                return [
                    'id' => $leadForm->id,
                    'name' => $leadForm->name,
                    'embed_key' => $leadForm->embed_key,
                    'is_active' => $leadForm->is_active,
                    'submit_button_label' => $leadForm->submit_button_label,
                    'layout_mode' => $leadForm->layout_mode,
                    'fields_count' => $leadForm->fields_count,
                    'embed_script' => sprintf(
                        '<script src="%s/widget.js" data-key="%s"></script>',
                        rtrim(url('/'), '/'),
                        $leadForm->embed_key,
                    ),
                ];
            });

        return Inertia::render('widgets/Index', [
            'leadForms' => $leadForms,
        ]);
    }

    /**
     * Show the form to create a widget.
     */
    public function create(): Response
    {
        return Inertia::render('widgets/Create', [
            'defaultLayoutMode' => 'stack',
            'defaultFields' => [
                [
                    'label' => 'Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Your full name',
                    'options' => [],
                ],
                [
                    'label' => 'Phone',
                    'type' => 'tel',
                    'required' => true,
                    'placeholder' => '',
                    'options' => [],
                ],
                [
                    'label' => 'Email',
                    'type' => 'email',
                    'required' => false,
                    'placeholder' => '',
                    'options' => [],
                ],
            ],
        ]);
    }

    /**
     * Persist a newly created widget.
     */
    public function store(StoreLeadFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $company = $request->user()->company;

        $leadForm = $company->leadForms()->create([
            'name' => $validated['name'],
            'embed_key' => (string) Str::ulid(),
            'is_active' => $validated['is_active'] ?? true,
            'submit_button_label' => $validated['submit_button_label'] ?? 'Send request',
            'layout_mode' => $validated['layout_mode'] ?? 'stack',
        ]);

        $this->syncFields($leadForm, $validated['fields']);

        return to_route('widgets.index');
    }

    /**
     * Show the form to edit an existing widget.
     */
    public function edit(Request $request, int $leadForm): Response
    {
        $form = $this->resolveCompanyLeadForm($request, $leadForm);

        return Inertia::render('widgets/Edit', [
            'leadForm' => [
                'id' => $form->id,
                'name' => $form->name,
                'is_active' => $form->is_active,
                'submit_button_label' => $form->submit_button_label,
                'layout_mode' => $form->layout_mode,
                'fields' => $form->fields->map(fn ($field): array => [
                    'label' => $field->label,
                    'type' => $field->type,
                    'required' => $field->is_required,
                    'placeholder' => $field->placeholder ?? '',
                    'options' => $field->options ?? [],
                ])->values()->all(),
            ],
        ]);
    }

    /**
     * Display widget dashboard view with preview and submitted records.
     */
    public function show(Request $request, int $leadForm): Response
    {
        $form = $this->resolveCompanyLeadForm($request, $leadForm);

        $records = $form->records()
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($record): array => [
                'id' => $record->id,
                'name' => $record->name,
                'email' => $record->email,
                'phone' => $record->phone,
                'payload' => $record->payload ?? [],
                'source' => $record->source,
                'created_at' => $record->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('widgets/Show', [
            'leadForm' => [
                'id' => $form->id,
                'name' => $form->name,
                'embed_key' => $form->embed_key,
                'is_active' => $form->is_active,
                'submit_button_label' => $form->submit_button_label,
                'layout_mode' => $form->layout_mode,
                'embed_script' => sprintf(
                    '<script src="%s/widget.js" data-key="%s"></script>',
                    rtrim(url('/'), '/'),
                    $form->embed_key,
                ),
                'fields' => $form->fields->map(fn ($field): array => [
                    'label' => $field->label,
                    'type' => $field->type,
                    'required' => $field->is_required,
                    'placeholder' => $field->placeholder ?? '',
                    'options' => $field->options ?? [],
                ])->values()->all(),
            ],
            'records' => $records,
        ]);
    }

    /**
     * Display a public Blade preview page for widget integration using UID.
     */
    public function preview(Request $request): BladeView
    {
        $uid = (string) $request->query('uid', '');

        abort_if($uid === '', 404);

        $form = LeadForm::query()
            ->with('fields')
            ->where('embed_key', $uid)
            ->firstOrFail();

        return view('widgets.preview', [
            'leadForm' => $form,
            'integrationScript' => sprintf(
                '<script src="%s/widget.js" data-key="%s"></script>',
                rtrim(url('/'), '/'),
                $form->embed_key,
            ),
        ]);
    }

    /**
     * Persist a public preview widget submission.
     */
    public function submitPreview(Request $request): RedirectResponse
    {
        $uid = (string) $request->input('uid', '');

        $form = LeadForm::query()
            ->with('fields')
            ->where('embed_key', $uid)
            ->firstOrFail();

        $rules = ['uid' => ['required', 'string']];

        foreach ($form->fields as $field) {
            $fieldRules = $this->buildFieldRules($field);
            $rules[$field->field_key] = $fieldRules['main'];

            if (! empty($fieldRules['nested'])) {
                $rules[$field->field_key.'.*'] = $fieldRules['nested'];
            }
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $payload = [];
        $capturedName = null;
        $capturedEmail = null;
        $capturedPhone = null;

        foreach ($form->fields as $field) {
            $value = $validated[$field->field_key] ?? null;
            $payload[$field->field_key] = $value;

            if ($capturedEmail === null && $field->type === 'email' && is_string($value)) {
                $capturedEmail = $value;
            }

            if ($capturedPhone === null && $field->type === 'tel' && is_string($value)) {
                $capturedPhone = $value;
            }

            if (
                $capturedName === null
                && is_string($value)
                && (str_contains(strtolower($field->label), 'name') || str_contains(strtolower($field->label), 'nom'))
            ) {
                $capturedName = $value;
            }
        }

        $record = $form->records()->create([
            'company_id' => $form->company_id,
            'name' => $capturedName,
            'email' => $capturedEmail,
            'phone' => $capturedPhone,
            'payload' => $payload,
            'source' => 'widget_preview',
        ]);

        Notification::send($form->company->users, new LeadReceivedNotification($record));

        return to_route('widgets.preview', ['uid' => $uid])
            ->with('widget_submission_status', 'submitted');
    }

    /**
     * Persist a public widget submission from embedded script.
     */
    public function submit(Request $request): JsonResponse
    {
        $uid = (string) $request->input('uid', '');

        $form = LeadForm::query()
            ->with('fields')
            ->where('embed_key', $uid)
            ->where('is_active', true)
            ->firstOrFail();

        /** @var array<string, mixed> $data */
        $data = $request->input('data', []);

        if (!empty($data['_website_url_hp'])) {
            return response()->json([
                'status' => 'ok',
            ]);
        }

        $rules = [];

        foreach ($form->fields as $field) {
            $fieldRules = $this->buildFieldRules($field);
            $rules[$field->field_key] = $fieldRules['main'];

            if (! empty($fieldRules['nested'])) {
                $rules[$field->field_key.'.*'] = $fieldRules['nested'];
            }
        }

        $validated = Validator::make($data, $rules)->validate();

        $payload = [];
        $capturedName = null;
        $capturedEmail = null;
        $capturedPhone = null;

        foreach ($form->fields as $field) {
            $value = $validated[$field->field_key] ?? null;
            $payload[$field->field_key] = $value;

            if ($capturedEmail === null && $field->type === 'email' && is_string($value)) {
                $capturedEmail = $value;
            }

            if ($capturedPhone === null && $field->type === 'tel' && is_string($value)) {
                $capturedPhone = $value;
            }

            if (
                $capturedName === null
                && is_string($value)
                && (str_contains(strtolower($field->label), 'name') || str_contains(strtolower($field->label), 'nom'))
            ) {
                $capturedName = $value;
            }
        }

        $record = $form->records()->create([
            'company_id' => $form->company_id,
            'name' => $capturedName,
            'email' => $capturedEmail,
            'phone' => $capturedPhone,
            'payload' => $payload,
            'source' => 'widget_script',
        ]);

        Notification::send($form->company->users, new LeadReceivedNotification($record));

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Update an existing widget.
     */
    public function update(UpdateLeadFormRequest $request, int $leadForm): RedirectResponse
    {
        $validated = $request->validated();
        $form = $this->resolveCompanyLeadForm($request, $leadForm);

        $form->update([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
            'submit_button_label' => $validated['submit_button_label'] ?? 'Send request',
            'layout_mode' => $validated['layout_mode'] ?? 'stack',
        ]);

        $this->syncFields($form, $validated['fields']);

        return to_route('widgets.index');
    }

    /**
     * Delete a widget.
     */
    public function destroy(Request $request, int $leadForm): RedirectResponse
    {
        $form = $this->resolveCompanyLeadForm($request, $leadForm);
        $form->delete();

        return to_route('widgets.index');
    }

    /**
     * Resolve a lead form scoped to the authenticated user's company.
     */
    private function resolveCompanyLeadForm(Request $request, int $leadFormId): LeadForm
    {
        return $request->user()->company->leadForms()
            ->with('fields')
            ->findOrFail($leadFormId);
    }

    /**
     * Replace widget fields with validated payload.
     *
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function syncFields(LeadForm $leadForm, array $fields): void
    {
        $leadForm->fields()->delete();

        foreach (array_values($fields) as $index => $field) {
            $label = trim((string) $field['label']);
            $fieldKey = Str::snake(Str::limit($label, 35, ''));
            $fieldKey = $fieldKey === '' ? 'field_'.$index : $fieldKey.'_'.$index;

            $leadForm->fields()->create([
                'label' => $label,
                'field_key' => $fieldKey,
                'type' => $field['type'],
                'is_required' => (bool) ($field['required'] ?? false),
                'placeholder' => $field['placeholder'] ?? null,
                'options' => array_values(array_filter($field['options'] ?? [], fn ($value): bool => (string) $value !== '')),
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Build validation rules for a lead form field.
     *
     * @return array{main: array<int, string>, nested: array<int, string>}
     */
    private function buildFieldRules(LeadFormField $field): array
    {
        $rules = [];
        $nestedRules = [];
        $options = is_array($field->options) ? $field->options : [];

        if ($field->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        if ($field->type === 'checkbox') {
            $rules[] = 'array';

            if ($field->is_required) {
                $rules[] = 'min:1';
            }

            if (! empty($options)) {
                $nestedRules[] = 'in:'.implode(',', $options);
            }

            return ['main' => $rules, 'nested' => $nestedRules];
        }

        if ($field->type === 'email') {
            $rules[] = 'string';
            $rules[] = 'email:rfc';
        } elseif ($field->type === 'select' || $field->type === 'radio') {
            $rules[] = 'string';
            if (! empty($options)) {
                $rules[] = 'in:'.implode(',', $options);
            }
        } else {
            $rules[] = 'string';
        }

        return ['main' => $rules, 'nested' => []];
    }
}
