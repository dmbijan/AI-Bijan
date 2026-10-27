<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\CustomerContact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['salesperson', 'contacts'])
            ->latest();
        
        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_code', 'like', '%' . $request->search . '%')
                  ->orWhere('ssm_number', 'like', '%' . $request->search . '%');
            });
        }
        
        $customers = $query->paginate(15);
        
        return response()->json($customers);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_type' => ['required', Rule::in(['individual', 'business'])],
            'name' => 'required|string|max:255',
            'name_bm' => 'nullable|string|max:255',
            'ssm_number' => 'nullable|string|max:50',
            'sst_number' => 'nullable|string|max:50',
            'tax_identification' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'category' => ['nullable', Rule::in(['retail', 'wholesale', 'distributor', 'enterprise', 'government'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'blocked'])],
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:3',
            'salesperson_id' => 'nullable|exists:users,id',
            'region' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_if:contacts,*.name|string|max:255',
            'contacts.*.position' => 'nullable|string|max:100',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.is_primary' => 'nullable|boolean',
        ]);
        
        $customer = Customer::create([
            'tenant_id' => app(TenantContext::class)->id(),
            'customer_code' => Customer::generateCustomerCode(),
            'customer_type' => $validated['customer_type'],
            'name' => $validated['name'],
            'name_bm' => $validated['name_bm'] ?? null,
            'ssm_number' => $validated['ssm_number'] ?? null,
            'sst_number' => $validated['sst_number'] ?? null,
            'tax_identification' => $validated['tax_identification'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postcode' => $validated['postcode'] ?? null,
            'category' => $validated['category'] ?? 'retail',
            'status' => $validated['status'] ?? 'active',
            'credit_limit' => $validated['credit_limit'] ?? 0,
            'payment_terms_days' => $validated['payment_terms_days'] ?? 30,
            'currency' => $validated['currency'] ?? 'MYR',
            'salesperson_id' => $validated['salesperson_id'] ?? null,
            'region' => $validated['region'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // Create contacts if provided
        if (isset($validated['contacts'])) {
            foreach ($validated['contacts'] as $contactData) {
                $customer->contacts()->create(array_merge($contactData, [
                    'is_primary' => $contactData['is_primary'] ?? false,
                ]));
            }
        }
        
        return response()->json($customer->load('contacts'), 201);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        return response()->json($customer->load(['contacts', 'salesperson', 'opportunities']));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_type' => ['required', Rule::in(['individual', 'business'])],
            'name' => 'required|string|max:255',
            'name_bm' => 'nullable|string|max:255',
            'ssm_number' => 'nullable|string|max:50',
            'sst_number' => 'nullable|string|max:50',
            'tax_identification' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'category' => ['nullable', Rule::in(['retail', 'wholesale', 'distributor', 'enterprise', 'government'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'blocked'])],
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:3',
            'salesperson_id' => 'nullable|exists:users,id',
            'region' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*.id' => 'nullable|exists:customer_contacts,id',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.position' => 'nullable|string|max:100',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.is_primary' => 'nullable|boolean',
        ]);
        
        $customer->update(array_filter($validated, fn($key) => !in_array($key, ['contacts']), ARRAY_FILTER_USE_KEY));
        
        // Handle contacts update
        if (isset($validated['contacts'])) {
            $existingContactIds = [];
            
            foreach ($validated['contacts'] as $contactData) {
                if (isset($contactData['id'])) {
                    // Update existing contact
                    $contact = CustomerContact::findOrFail($contactData['id']);
                    $contact->update($contactData);
                    $existingContactIds[] = $contactData['id'];
                } else {
                    // Create new contact
                    $customer->contacts()->create($contactData);
                }
            }
            
            // Delete contacts not in the request
            $customer->contacts()->whereNotIn('id', $existingContactIds)->delete();
        }
        
        return response()->json($customer->load('contacts'));
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        
        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
