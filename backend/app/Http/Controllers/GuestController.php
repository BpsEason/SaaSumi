<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GuestController extends Controller
{
    /**
     * Display a listing of the guests.
     */
    public function index(Request $request)
    {
        // For multi-tenancy, filter by current tenant if guests are tenant-scoped
        // $guests = Guest::where('tenant_id', tenant()->id)->orderBy('id', 'desc')->get();
        // For demonstration without tenant scoping for Guests model:
        $guests = Guest::orderBy('id', 'desc')->get();

        // Implement search and sort based on request query parameters
        $query = $request->query('query');
        $sortBy = $request->query('sortBy', 'id');
        $sortDirection = $request->query('sortDirection', 'asc');

        if ($query) {
            $guests = $guests->filter(function($guest) use ($query) {
                return str_contains(strtolower($guest->name), strtolower($query)) ||
                       str_contains(strtolower($guest->email), strtolower($query));
            });
        }

        $guests = $guests->sortBy(function($guest) use ($sortBy) {
            return is_string($guest->{$sortBy}) ? strtolower($guest->{$sortBy}) : $guest->{$sortBy};
        }, SORT_NATURAL | SORT_FLAG_CASE, $sortDirection === 'desc');

        return response()->json($guests->values()); // values() to reset array keys after filter/sort
    }

    /**
     * Store a newly created guest in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:guests,email',
                'phone' => 'nullable|string|max:50',
                'country' => 'nullable|string|max:100',
                'stay_count' => 'nullable|integer|min:0',
                'last_stay_date' => 'nullable|date',
                'status' => 'nullable|string|in:active,inactive',
            ]);

            // Add tenant_id if Guests model uses BelongsToTenant
            // $validatedData['tenant_id'] = tenant()->id;

            $guest = Guest::create($validatedData);
            return response()->json($guest, 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified guest.
     */
    public function show(Guest $guest)
    {
        // Add policy or check tenant_id if guest is tenant-scoped
        return response()->json($guest);
    }

    /**
     * Update the specified guest in storage.
     */
    public function update(Request $request, Guest $guest)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:guests,email,' . $guest->id,
                'phone' => 'nullable|string|max:50',
                'country' => 'nullable|string|max:100',
                'stay_count' => 'nullable|integer|min:0',
                'last_stay_date' => 'nullable|date',
                'status' => 'nullable|string|in:active,inactive',
            ]);

            $guest->update($validatedData);
            return response()->json($guest);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified guest from storage.
     */
    public function destroy(Guest $guest)
    {
        // Add policy or check tenant_id if guest is tenant-scoped
        $guest->delete();
        return response()->json(null, 204);
    }
}
