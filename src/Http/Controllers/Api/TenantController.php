<?php

namespace Elgaml\MultiTenancyRbac\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Elgaml\MultiTenancyRbac\Models\Tenant;
use Elgaml\MultiTenancyRbac\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    protected $tenantService;
    
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }
    
    public function index()
    {
        $this->authorize('viewAny', Tenant::class);
        
        return Tenant::all();
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Tenant::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants',
            'database_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        $tenant = $this->tenantService->create($validated);
        
        return response()->json($tenant, 201);
    }
    
    public function show($id)
    {
        $tenant = Tenant::findOrFail($id);
        $this->authorize('view', $tenant);
        
        return $tenant;
    }
    
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $this->authorize('update', $tenant);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255|unique:tenants,domain,'.$id,
            'is_active' => 'boolean',
        ]);
        
        $tenant->update($validated);
        
        return $tenant;
    }
    
    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $this->authorize('delete', $tenant);
        
        $tenant->delete();
        
        return response()->json(null, 204);
    }
}
