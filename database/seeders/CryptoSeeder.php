<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cryptocurrency;

class CryptoSeeder extends Seeder
{
    public function run(): void
    {
        $cryptos = [
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'current_price' => 65000.00,
                'logo_url' => 'https://cryptologos.cc/logos/bitcoin-btc-logo.png'
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'current_price' => 3500.00,
                'logo_url' => 'https://cryptologos.cc/logos/ethereum-eth-logo.png'
            ],
            [
                'name' => 'IceCoin',
                'symbol' => 'ICE',
                'current_price' => 1.50, // La crypto officielle de ton app !
                'logo_url' => 'https://via.placeholder.com/150/0000FF/808080 ?Text=ICE'
            ]
        ];

        foreach ($cryptos as $crypto) {
            Cryptocurrency::create($crypto);
        }
    }
}