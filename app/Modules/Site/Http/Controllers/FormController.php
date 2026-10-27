<?php

namespace App\Modules\Site\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\FormService;
use App\Modules\Site\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FormController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected FormService $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * List all forms for a site.
     */
    public function index(Request $request, int $siteId): JsonResponse
    {
        $forms = $this->formService->getForms($siteId, $this->tenantId());
        
        return response()->json([
            'success' => true,
            'data' => $forms,
        ]);
    }

    /**
     * Get a single form.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $form = $this->formService->getFormById($id, $this->tenantId());

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $form,
        ]);
    }

    /**
     * Create a new form.
     */
    public function store(Request $request, int $siteId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'submit_action' => 'nullable|string|in:email,webhook,database',
            'success_message' => 'nullable|string',
            'spam_protection' => 'nullable|array',
            'notification_email' => 'nullable|array',
        ]);

        $validated['tenant_id'] = $this->tenantId();
        $validated['site_id'] = $siteId;

        $form = $this->formService->createForm($validated);

        return response()->json([
            'success' => true,
            'message' => 'Form created successfully.',
            'data' => $form,
        ], 201);
    }

    /**
     * Update a form.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $form = $this->formService->getFormById($id, $this->tenantId());

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'sometimes|array',
            'submit_action' => 'nullable|string|in:email,webhook,database',
            'success_message' => 'nullable|string',
            'spam_protection' => 'nullable|array',
            'notification_email' => 'nullable|array',
        ]);

        $form = $this->formService->updateForm($form, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Form updated successfully.',
            'data' => $form,
        ]);
    }

    /**
     * Delete a form.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $form = $this->formService->getFormById($id, $this->tenantId());

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found.',
            ], 404);
        }

        $this->formService->deleteForm($form);

        return response()->json([
            'success' => true,
            'message' => 'Form deleted successfully.',
        ]);
    }

    /**
     * Toggle form active status.
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $form = $this->formService->getFormById($id, $this->tenantId());

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found.',
            ], 404);
        }

        $form = $this->formService->toggleActive($form);

        return response()->json([
            'success' => true,
            'message' => 'Form status updated.',
            'data' => $form,
        ]);
    }

    /**
     * Get form submissions.
     */
    public function submissions(Request $request, int $id): JsonResponse
    {
        $form = $this->formService->getFormById($id, $this->tenantId());

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found.',
            ], 404);
        }

        $submissions = $this->formService->getSubmissions($form);
        
        return response()->json([
            'success' => true,
            'data' => $submissions,
        ]);
    }

    /**
     * Submit a form response (public endpoint).
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $form = $this->formService->getFormById($id, null);

        if (!$form || !$form->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found or inactive.',
            ], 404);
        }

        // Validate fields dynamically based on form configuration
        $fieldRules = [];
        foreach ($form->fields as $field) {
            if (!empty($field['required'])) {
                $fieldRules[$field['name']] = 'required';
            } else {
                $fieldRules[$field['name']] = 'nullable';
            }
        }

        $validated = $request->validate($fieldRules);

        $submission = $this->formService->submitForm(
            $form,
            $validated,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully.',
            'data' => $submission,
        ], 201);
    }
}
