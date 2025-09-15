<?php

namespace App\Http\Controllers\Auth\MultiTenancyRbac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function tokens(Request $request)
    {
        return response()->json($request->user()->tokens);
    }
    
    public function createToken(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
        ]);
        
        $token = $request->user()->createToken(
            $validated['name'],
            $validated['abilities'] ?? ['*']
        );
        
        return response()->json([
            'message' => 'Token created successfully',
            'token' => $token->plainTextToken
        ]);
    }
    
    public function deleteToken(Request $request, $tokenId)
    {
        $request->user()->tokens()->findOrFail($tokenId)->delete();
        
        return response()->json([
            'message' => 'Token deleted successfully'
        ]);
    }
    
    public function deleteAllTokens(Request $request)
    {
        $request->user()->tokens()->delete();
        
        return response()->json([
            'message' => 'All tokens deleted successfully'
        ]);
    }
}
