<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Afficher le profil admin
     */
    public function index()
    {
        $user = auth()->user();

        return view('admin.profile.index', [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'account_info' => [
                'created_at' => $user->created_at->format('d/m/Y'),
                'last_login' => $user->updated_at->format('d/m/Y H:i'),
                'total_actions' => 0
            ]
        ]);
    }

    /**
     * Statistiques utilisateur
     */
    private function getUserStats($user)
    {
        return [
            'dossiers_traites' => 0,
            'actions_today' => 0,
            'login_count' => 0,
            'account_age' => $user->created_at->diffForHumans()
        ];
    }

    /**
     * Mettre à jour les informations du profil
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Changer le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.'
            ]);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Mot de passe changé avec succès.');
    }

    /**
     * Uploader une photo de profil
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
            }

            // Stocker la nouvelle photo
            $path = $request->file('avatar')->store('avatars', 'public');

            $user->update([
                'avatar' => $path
            ]);

            return redirect()->route('admin.profile.index')
                ->with('success', 'Photo de profil mise à jour avec succès.');
        }

        return back()->withErrors(['avatar' => 'Erreur lors du téléchargement de la photo.']);
    }
}
