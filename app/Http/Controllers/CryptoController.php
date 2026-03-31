<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cryptocurrency;
use App\Models\CryptoWallet;

class CryptoController extends Controller
{
    // 1. Renvoyer le catalogue des cryptomonnaies disponibles
    public function index()
    {
        $cryptos = Cryptocurrency::all();
        return response()->json($cryptos);
    }

    // 2. Renvoyer les portefeuilles crypto de l'utilisateur connecté
    public function myWallets(Request $request)
    {
        $user = $request->user();
        
        // On récupère les wallets de l'utilisateur et on inclut les infos de la crypto associée
        $wallets = CryptoWallet::where('user_id', $user->id)
            ->join('cryptocurrencies', 'crypto_wallets.cryptocurrency_id', '=', 'cryptocurrencies.id')
            ->select('crypto_wallets.*', 'cryptocurrencies.name', 'cryptocurrencies.symbol', 'cryptocurrencies.logo_url')
            ->get();

        return response()->json($wallets);
    }
}