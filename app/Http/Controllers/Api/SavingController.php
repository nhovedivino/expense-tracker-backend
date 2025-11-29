<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saving;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $savings = $request->user()->savings()
            ->orderBy('date', 'desc')
            ->paginate(15);

        return response()->json($savings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        $saving = $request->user()->savings()->create($request->all());

        return response()->json([
            'message' => 'Saving created successfully',
            'saving' => $saving
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Saving $saving)
    {
        // Ensure the saving belongs to the authenticated user
        if ($saving->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($saving);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Saving $saving)
    {
        // Ensure the saving belongs to the authenticated user
        if ($saving->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        $saving->update($request->all());

        return response()->json([
            'message' => 'Saving updated successfully',
            'saving' => $saving
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Saving $saving)
    {
        // Ensure the saving belongs to the authenticated user
        if ($saving->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $saving->delete();

        return response()->json([
            'message' => 'Saving deleted successfully'
        ]);
    }
}
