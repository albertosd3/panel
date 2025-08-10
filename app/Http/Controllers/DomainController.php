<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DomainController extends Controller
{
    /**
     * Display a listing of the domains.
     */
    public function index()
    {
        $domains = Domain::withCount('shortlinks')->get();
        return view('panel.domains', compact('domains'));
    }

    /**
     * Store a newly created domain in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'domain' => 'required|string|max:255|unique:domains,domain',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $domain = Domain::create([
            'domain' => $request->domain,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'domain' => $domain,
            'message' => 'Domain added successfully!'
        ]);
    }

    /**
     * Update the specified domain in storage.
     */
    public function update(Request $request, Domain $domain)
    {
        $validator = Validator::make($request->all(), [
            'domain' => 'required|string|max:255|unique:domains,domain,' . $domain->id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $domain->update([
            'domain' => $request->domain,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'domain' => $domain,
            'message' => 'Domain updated successfully!'
        ]);
    }

    /**
     * Remove the specified domain from storage.
     */
    public function destroy(Domain $domain)
    {
        // Check if this domain has any shortlinks
        if ($domain->shortlinks()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete domain that has shortlinks associated with it.'
            ], 400);
        }

        $domain->delete();

        return response()->json([
            'success' => true,
            'message' => 'Domain deleted successfully!'
        ]);
    }

    /**
     * Set a domain as the default domain.
     */
    public function setDefault(Domain $domain)
    {
        // Remove default from all domains
        Domain::query()->update(['is_default' => false]);
        
        // Set this domain as default
        $domain->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default domain updated successfully!'
        ]);
    }

    /**
     * Toggle domain active status.
     */
    public function toggleActive(Domain $domain)
    {
        $domain->update(['is_active' => !$domain->is_active]);

        return response()->json([
            'success' => true,
            'domain' => $domain,
            'message' => 'Domain status updated successfully!'
        ]);
    }

    /**
     * Test if a domain is properly configured.
     */
    public function testDomain(Domain $domain)
    {
        try {
            $url = 'https://' . $domain->domain;
            
            // Check if domain resolves
            $headers = @get_headers($url, 1);
            
            if (!$headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domain does not resolve or is not accessible.'
                ]);
            }

            // Check if it's pointing to this Laravel app
            $response = @file_get_contents($url . '/health-check');
            
            if ($response === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domain is accessible but not pointing to this application.'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Domain is properly configured!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing domain: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API endpoint to list all active domains.
     */
    public function apiList()
    {
        $domains = Domain::active()->get(['id', 'domain', 'description', 'is_default']);
        
        return response()->json([
            'ok' => true,
            'data' => $domains
        ]);
    }
}
