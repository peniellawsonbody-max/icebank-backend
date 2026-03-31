<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FiatWallet;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Fonction pour s'inscrire
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'pin_code' => 'nullable|string|min:4|max:6'
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'pin_code' => $validated['pin_code'] ?? null,
        ]);

        // Création automatique du compte Fiat (Euros) à l'inscription
        FiatWallet::create([
            'user_id' => $user->id,
            'iban' => 'FR' . rand(10, 99) . 'ICEBANK' . rand(1000000, 9999999),
            'balance' => 0.00,
            'currency' => 'EUR',
        ]);

        // Génération du token de sécurité pour l'application mobile
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // Fonction pour se connecter
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Vérification des identifiants
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}