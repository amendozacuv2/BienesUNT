<?php

namespace App\Http\Controllers;

use App\Services\Estates\EstateFieldSuggestions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstateFieldSuggestionController extends Controller
{
    public function __invoke(Request $request, EstateFieldSuggestions $suggestions): JsonResponse
    {
        abort_unless(
            $request->user()?->can('create.estate') || $request->user()?->can('edit.estate'),
            403
        );

        $validated = $request->validate([
            'field' => ['required', 'string', Rule::in($suggestions->allowedFields())],
            'term' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'results' => $suggestions->forSelect2(
                $validated['field'],
                $validated['term'] ?? null,
                20
            ),
        ]);
    }
}
