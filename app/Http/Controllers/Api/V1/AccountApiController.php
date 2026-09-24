<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Department;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountApiController extends Controller
{
    /**
     * 1. List Chart of Accounts (COA) / Akun Perkiraan
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        $query = Account::where('company_id', $companyId)->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('code')->get();

        return response()->json([
            'status' => 'success',
            'data' => $accounts,
        ]);
    }

    /**
     * Update Saldo Awal Akun COA
     */
    public function updateInitialBalances(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        $validated = $request->validate([
            'conversion_date' => 'required|date',
            'balances' => 'required|array',
            'balances.*.id' => 'required|exists:accounts,id',
            'balances.*.initial_debit' => 'nullable|numeric|min:0',
            'balances.*.initial_credit' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['balances'] as $bal) {
            Account::where('company_id', $companyId)
                ->where('id', $bal['id'])
                ->update([
                    'initial_debit' => floatval($bal['initial_debit'] ?? 0),
                    'initial_credit' => floatval($bal['initial_credit'] ?? 0),
                ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Saldo awal akun berhasil diperbarui.',
        ]);
    }

    /**
     * 2. Master Kontak (Filterable by type: vendor, customer, employee, other)
     */
    public function contacts(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $query = Contact::where('company_id', $companyId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $contacts,
        ]);
    }

    /**
     * Shortcut: Khusus Vendor
     */
    public function vendors(Request $request): JsonResponse
    {
        $request->merge(['type' => 'vendor']);
        return $this->contacts($request);
    }

    /**
     * Shortcut: Khusus Customer / Klien
     */
    public function customers(Request $request): JsonResponse
    {
        $request->merge(['type' => 'customer']);
        return $this->contacts($request);
    }

    /**
     * Tambah Kontak Baru via API (Customer / Vendor / Karyawan / Kandidat)
     */
    public function storeContact(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,vendor,employee,other',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $contact = Contact::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kontak berhasil ditambahkan.',
            'data' => $contact,
        ], 201);
    }

    /**
     * 3. Master Departemen
     */
    public function departments(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $query = Department::where('company_id', $companyId);

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $departments,
        ]);
    }

    /**
     * Tambah Departemen Baru via API
     */
    public function storeDepartment(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $department = Department::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->input('is_active', true),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Departemen berhasil ditambahkan.',
            'data' => $department,
        ], 201);
    }

    /**
     * 4. Master Proyek (Project)
     */
    public function projects(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $query = Project::with('department')->where('company_id', $companyId);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $projects,
        ]);
    }

    /**
     * Tambah Proyek Baru via API
     */
    public function storeProject(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'contract_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,completed,on_hold',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'company_id' => $companyId,
            'department_id' => $validated['department_id'] ?? null,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'contract_amount' => $validated['contract_amount'] ?? 0,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Proyek berhasil ditambahkan.',
            'data' => $project->load('department'),
        ], 201);
    }

    /**
     * 5. Master Tag / Label
     */
    public function tags(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $query = Tag::where('company_id', $companyId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $tags = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tags,
        ]);
    }

    /**
     * Tambah Tag Baru via API
     */
    public function storeTag(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $tag = Tag::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'color' => $validated['color'] ?: '#3b82f6',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tag berhasil ditambahkan.',
            'data' => $tag,
        ], 201);
    }

    /**
     * Master Cara Pembayaran
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $methods = PaymentMethod::with('account')->where('company_id', $companyId)->get();

        return response()->json([
            'status' => 'success',
            'data' => $methods,
        ]);
    }

    /**
     * 6. Master Bundle (Mengambil Semua Master Data Sekaligus untuk Form Aplikasi Eksternal)
     */
    public function masterBundle(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        return response()->json([
            'status' => 'success',
            'data' => [
                'accounts' => Account::where('company_id', $companyId)->where('is_active', true)->orderBy('code')->get(),
                'vendors' => Contact::where('company_id', $companyId)->where('type', 'vendor')->orderBy('name')->get(),
                'customers' => Contact::where('company_id', $companyId)->where('type', 'customer')->orderBy('name')->get(),
                'departments' => Department::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
                'projects' => Project::where('company_id', $companyId)->where('status', 'active')->orderBy('name')->get(),
                'tags' => Tag::where('company_id', $companyId)->orderBy('name')->get(),
                'payment_methods' => PaymentMethod::with('account')->where('company_id', $companyId)->get(),
                'taxes' => Tax::where('company_id', $companyId)->get(),
            ],
        ]);
    }
}

