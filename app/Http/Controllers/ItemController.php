<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Household;
use App\Models\Shoppinglist;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function store(Request $request, Household $household, Shoppinglist $shoppinglist)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $item = $shoppinglist->items()->create([
            'name' => $request->name,
        ]);
        $item->user()->associate(auth()->user());
        $item->save();

        return new ItemResource($item);
    }

    public function index(Household $household, Shoppinglist $shoppinglist)
    {
        if($shoppinglist->household_id !== $household->id) {
            Log::channel('custom')->warning('Unauthorized attempt to view items', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'shopping_list_id' => $shoppinglist->id,
                'household_id' => $household->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            abort(403, 'Unauthorized');
        }
        $items = $shoppinglist->items;
        return ItemResource::collection($items);
    }

    public function toggleCheck(Request $request, Household $household, Shoppinglist $shoppinglist, Item $item)
    {
        if($item->shoppinglist_id !== $shoppinglist->id) {
            Log::channel('custom')->warning('Unauthorized attempt to update item', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'item_id' => $item->id,
                'shopping_list_id' => $shoppinglist->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            abort(403, 'Unauthorized');
        }
        if($item->checked == $request->checked) {
            return response()->json(['message' => 'Item already ' . ($item->checked ? 'checked' : 'unchecked')], 409);
        } else {
            $item->update(['checked' => $request->checked]);
        }
        return response()->json([
            'message' => 'Item updated',
            'item' => new ItemResource($item)
        ]);
    }

    public function update(Request $request, Household $household, Shoppinglist $shoppinglist, Item $item)
    {
        if ($item->shoppinglist_id !== $shoppinglist->id) {
            Log::channel('custom')->warning('Unauthorized attempt to update item', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'item_id' => $item->id,
                'shopping_list_id' => $shoppinglist->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            abort(403, 'Unauthorized');
        }
        $request->validate(['name' => 'required|string|max:255']);
        $item->update($request->only('name'));
        return response()->json(['message' => 'Item updated', 'item' => new ItemResource($item)]);
    }

    public function destroy(Household $household, Shoppinglist $shoppinglist, Item $item)
    {
        if ($item->shoppinglist_id !== $shoppinglist->id) {
            Log::channel('custom')->warning('Unauthorized attempt to delete item', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'item_id' => $item->id,
                'shopping_list_id' => $shoppinglist->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            abort(403, 'Unauthorized');
        }
        $item->delete();
        return response()->json(['message' => 'Item deleted']);
    }
}
