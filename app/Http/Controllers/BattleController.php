<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\User;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWT;

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

    public function participate(Request $request, JWT $JWT)
    {
        $validated = $request->validate([
            'battle' => 'required|exists:battles,id',
        ]);

        $battle = Battle::find($validated['battle']);
        $battleUserIds =  $battle->users->pluck('id')->toArray();

        if (!in_array($request->user()?->id, $battleUserIds)) {
            return response('FAIL', 400);
        }

        return response()->json([
            'status' => 'success',
            'battle' => $battle->id,
            'authorisation' => [
                'token' => $JWT->fromSubject($battle),
                'type' => 'bearer',
            ]
        ]);
    }

    public function execute(Request $request)
    {
        $validated = $request->validate([
            'battle' => 'required',
        ]);
        $token = JWTAuth::getToken();
        $battleId = JWTAuth::getPayload($token)->toArray()['sub'];
        if ($validated['battle'] !== $battleId) {
            return response('FAIL', 400);
        }
        return response('OK', 200);
    }
}
