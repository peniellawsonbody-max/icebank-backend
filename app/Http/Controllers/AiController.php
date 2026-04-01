<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $token = env('HUGGINGFACE_TOKEN');

        if (empty($token)) {
            return response()->json(['reply' => "Assistant Icebank : Simulation activée."]);
        }

        // NOUVELLE URL STABLE DU ROUTER
        $response = Http::withToken($token)
            ->post("https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.3", [
                'inputs' => "[INST] Tu es l'assistant de la banque Icebank. Réponds brièvement : " . $request->message . " [/INST]",
                'parameters' => ['max_new_tokens' => 150, 'return_full_text' => false],
                'options' => ['wait_for_model' => true]
            ]);

        $data = $response->json();

        // Succès !
        if ($response->successful() && isset($data[0]['generated_text'])) {
            return response()->json(['reply' => trim($data[0]['generated_text'])]);
        }

        // Si le modèle charge (503) ou erreur temporaire
        return response()->json([
            'reply' => "Désolé, je vérifie nos serveurs sécurisés. Réessayez dans un instant !",
            'debug_status' => $response->status() // On garde ça pour toi
        ]);
    }
}