<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|numeric|digits:10|unique:users',
                'device_token' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 200);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'device_token' => $data['device_token'],
        ]);
        $user->assignRole('User');

        $token = $user->createToken('vtfs')->plainTextToken;
        $user->update(['auth_token' => $token]);
        return response()->json([
            'user' => $user,
        ]);
    }
    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|string|email', 'password' => 'required|string',]);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.'],]);
        }
        $token = $user->createToken('mobile-app')->plainTextToken;
        $user->auth_token = $token;
        $user->save();
        return response()->json(['user' => $user, 'token' => $token,]);
    }
    public function profile(Request $request)
    {
        return $request->user();
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function cleanupOldStocks()
    {
        DB::table('traded_stocks')
            ->whereDate('timestamp', '<', Carbon::today())
            ->delete();
    }
}
