<?php

namespace App\Modules\Site\Services;

use App\Modules\Site\Models\Form;
use App\Modules\Site\Models\FormSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FormService
{
    /**
     * Get all forms for a site.
     */
    public function getForms(int $siteId, int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Form::where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a form by ID.
     */
    public function getFormById(int $formId, int $tenantId): ?Form
    {
        return Form::where('id', $formId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new form.
     */
    public function createForm(array $data): Form
    {
        if (empty($data['slug'])) {
            $data['slug'] = Form::generateSlug($data['name']);
        }

        return DB::transaction(function () use ($data) {
            return Form::create($data);
        });
    }

    /**
     * Update a form.
     */
    public function updateForm(Form $form, array $data): Form
    {
        return DB::transaction(function () use ($form, $data) {
            $form->update($data);
            return $form->fresh();
        });
    }

    /**
     * Delete a form.
     */
    public function deleteForm(Form $form): bool
    {
        return $form->delete();
    }

    /**
     * Submit a form response.
     */
    public function submitForm(Form $form, array $data, ?string $ipAddress = null, ?string $userAgent = null): FormSubmission
    {
        return DB::transaction(function () use ($form, $data, $ipAddress, $userAgent) {
            $submission = FormSubmission::create([
                'tenant_id' => $form->tenant_id,
                'form_id' => $form->id,
                'data' => $data,
                'ip_address' => $ipAddress ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
                'is_spam' => false,
            ]);

            // Send notification emails if configured
            if (!empty($form->notification_email)) {
                $this->sendNotificationEmail($form, $submission);
            }

            return $submission;
        });
    }

    /**
     * Get all submissions for a form.
     */
    public function getSubmissions(Form $form): \Illuminate\Database\Eloquent\Collection
    {
        return FormSubmission::where('form_id', $form->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Toggle form active status.
     */
    public function toggleActive(Form $form): Form
    {
        return $form->update(['is_active' => !$form->is_active]) ? $form->fresh() : null;
    }

    /**
     * Mark submission as spam.
     */
    public function markAsSpam(FormSubmission $submission): FormSubmission
    {
        return $submission->update(['is_spam' => true]) ? $submission->fresh() : null;
    }

    /**
     * Send notification email for form submission.
     */
    protected function sendNotificationEmail(Form $form, FormSubmission $submission): void
    {
        // This is a placeholder - implement actual email sending logic
        // based on your mail configuration
    }

    /**
     * Get unique slug.
     */
    public function getUniqueSlug(string $name, int $siteId, int $tenantId): string
    {
        $baseSlug = Form::generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Form::where('slug', $slug)
            ->where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
