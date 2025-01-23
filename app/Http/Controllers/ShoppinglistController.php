<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShoppinglistResource;
use App\Models\Household;
use App\Models\Shoppinglist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShoppinglistController extends Controller
{
    public function store(Request $request, Household $household)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $shoppinglist = $household->shoppinglists()->create([
            'name' => $request->name,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'List created', 'shoppinglist' => new ShoppinglistResource($shoppinglist)], 201);
    }

    public function index(Household $household)
    {
        $lists = $household->shoppinglists;
        return ShoppinglistResource::collection($lists);
    }

    public function show(Household $household, Shoppinglist $shoppinglist)
    {
        if ($shoppinglist->household_id !== $household->id) {
            Log::channel('custom')->warning('Unauthorized attempt to view list', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'list_id' => $shoppinglist->id,
                'household_id' => $household->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            abort(403, 'Unauthorized');
        }
        $shoppinglist->load('items');
        return new ShoppinglistResource($shoppinglist);
    }

    public function update(Request $request, Household $household, Shoppinglist $shoppinglist)
    {
        if ($shoppinglist->household_id !== $household->id) {
            Log::channel('custom')->warning('Unauthorized attempt to update list', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'list_id' => $shoppinglist->id,
                'household_id' => $household->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            abort(403, 'Unauthorized');
        }
        $request->validate(['name' => 'required|string|max:255']);
        $shoppinglist->update($request->only('name'));
        return response()->json(['message' => 'List updated', 'shoppinglist' => new ShoppinglistResource($shoppinglist)]);
    }

    public function destroy(Household $household, Shoppinglist $shoppinglist)
    {
        if ($shoppinglist->household_id !== $household->id) {
            Log::channel('custom')->warning('Unauthorized attempt to delete list', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'list_id' => $shoppinglist->id,
                'household_id' => $household->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            abort(403, 'Unauthorized');
        }
        $shoppinglist->delete();
        return response()->json(['message' => 'List deleted']);
    }
}
