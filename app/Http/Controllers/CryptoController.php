<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cryptocurrency;
use App\Models\CryptoWallet;
use App\Models\FiatWallet;
use App\Models\Transaction;

class CryptoController extends Controller
{
    // 1. Renvoyer le catalogue
    public function index()
    {
        return response()->json(Cryptocurrency::all());
    }

    // 2. Renvoyer les portefeuilles
    public function myWallets(Request $request)
    {
        $wallets = CryptoWallet::where('user_id', $request->user()->id)
            ->join('cryptocurrencies', 'crypto_wallets.cryptocurrency_id', '=', 'cryptocurrencies.id')
            ->select('crypto_wallets.*', 'cryptocurrencies.name', 'cryptocurrencies.symbol', 'cryptocurrencies.logo_url')
            ->get();
        return response()->json($wallets);
    }

    // 3. LOGIQUE D'ACHAT
    public function buy(Request $request)
    {
        // On vérifie que les données envoyées par Flutter sont correctes
        $request->validate([
            'cryptocurrency_id' => 'required|exists:cryptocurrencies,id',
            'amount_eur' => 'required|numeric|min:1'
        ]);

        $user = $request->user();
        $crypto = Cryptocurrency::find($request->cryptocurrency_id);
        $fiatWallet = FiatWallet::where('user_id', $user->id)->first();

        // Étape A : Vérifier le solde
        if ($fiatWallet->balance < $request->amount_eur) {
            return response()->json(['message' => 'Solde en euros insuffisant'], 400);
        }

        // Calculer combien de crypto il obtient avec ses euros
        $cryptoAmount = $request->amount_eur / $crypto->current_price;

        // DB::transaction garantit que si une étape plante, on annule tout (sécurité bancaire !)
        DB::transaction(function () use ($user, $crypto, $fiatWallet, $request, $cryptoAmount) {
            
            // Étape B : Débiter les euros
            $fiatWallet->balance -= $request->amount_eur;
            $fiatWallet->save();

            // Étape C : Créditer la crypto (on crée le wallet s'il n'existe pas encore)
            $cryptoWallet = CryptoWallet::firstOrCreate(
                ['user_id' => $user->id, 'cryptocurrency_id' => $crypto->id],
                ['wallet_address' => '0x' . bin2hex(random_bytes(16)), 'balance' => 0]
            );
            $cryptoWallet->balance += $cryptoAmount;
            $cryptoWallet->save();

            // Étape D : Enregistrer dans l'historique
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'crypto_buy',
                'amount' => $request->amount_eur,
                'currency' => 'EUR',
                'status' => 'completed',
                'description' => "Achat de " . number_format($cryptoAmount, 6) . " " . $crypto->symbol
            ]);
        });

        return response()->json(['message' => 'Achat de crypto réussi avec succès !']);
    }
    // 4. LOGIQUE DE VENTE
    public function sell(Request $request)
    {
        $request->validate([
            'cryptocurrency_id' => 'required|exists:cryptocurrencies,id',
            'amount_crypto' => 'required|numeric|min:0.000001'
        ]);

        $user = $request->user();
        $crypto = Cryptocurrency::find($request->cryptocurrency_id);
        $cryptoWallet = CryptoWallet::where('user_id', $user->id)
            ->where('cryptocurrency_id', $crypto->id)
            ->first();

        // Étape A : Vérifier que l'utilisateur possède bien cette crypto et en quantité suffisante
        if (!$cryptoWallet || $cryptoWallet->balance < $request->amount_crypto) {
            return response()->json(['message' => 'Solde en crypto insuffisant'], 400);
        }

        // Calculer la valeur en euros de la crypto vendue
        $eurAmount = $request->amount_crypto * $crypto->current_price;

        DB::transaction(function () use ($user, $crypto, $cryptoWallet, $request, $eurAmount) {
            
            // Étape B : Débiter la crypto
            $cryptoWallet->balance -= $request->amount_crypto;
            $cryptoWallet->save();

            // Étape C : Créditer les euros
            $fiatWallet = FiatWallet::where('user_id', $user->id)->first();
            $fiatWallet->balance += $eurAmount;
            $fiatWallet->save();

            // Étape D : Enregistrer dans l'historique
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'crypto_sell',
                'amount' => $eurAmount,
                'currency' => 'EUR',
                'status' => 'completed',
                'description' => "Vente de " . number_format($request->amount_crypto, 6) . " " . $crypto->symbol
            ]);
        });

        return response()->json(['message' => 'Vente de crypto réussie avec succès !']);
    }
}