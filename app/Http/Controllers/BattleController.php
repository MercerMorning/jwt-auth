<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\User;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user.*' => 'required|exists:users,email',
        ]);

        $users = User::query()
            ->whereIn('email', $validated['user'])
            ->get()
            ->pluck('id')
            ->toArray();

        $battle = Battle::create([]);

        $battle->users()->attach($users);

        return response()->json([
            'status' => 'success',
            'message' => 'Battle created successfully',
            'battle' => $battle,
        ]);
    }

    public function participate(Request $request)
    {
        $validated = $request->validate([
            'battle' => 'required|exists:battles,id',
        ]);

        $battleUserIds =  Battle::find($validated['battle'])->users->pluck('id')->toArray();

        if (!in_array($request->user()?->id, $battleUserIds)) {
            return response('FAIL', 400);
        }

        return response('OK', 200);
    }
}
