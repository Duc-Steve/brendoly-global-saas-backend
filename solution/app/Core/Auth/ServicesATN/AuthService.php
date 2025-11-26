<?php

namespace App\Core\Auth\ServicesATN;

use App\Core\Auth\ModelsATN\UserIdentify;
use App\Core\Auth\TraitsATN\AssistantTrait;
use App\Core\MultiTenancy\ModelsMTY\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Str;

/**
 * Service de gestion de l'authentification et de l'inscription
 * Fournit des méthodes pour l'inscription, la connexion et la récupération du profil utilisateur
 */
class AuthService
{
    use AssistantTrait; // Traite les fonctions auxiliaires comme isEmail() et isPhone()

    /**
     * Inscription d'un nouvel utilisateur avec création de tenant
     * 
     * @param array $data Données reçues depuis le formulaire d'inscription
     * @return User L'utilisateur créé
     */
    public function register(array $data): UserIdentify
    {
        // Tout le processus se fait dans une transaction pour garantir la cohérence des données
        return DB::transaction(function () use ($data) {

            // Création du tenant (entreprise) avec cryptage des données sensibles si nécessaire
            $tenant = Tenant::create([
                'id_tenant' => (string) Str::uuid(), // Identifiant unique
                'name' => $data['company_name'],
                'type' => $data['company_type'],
                'sector' => $data['company_sector'],
                'employees_number' => $data['company_employees_number'] ?? null,
                'address' => $data['company_address'] ?? null,
                'city' => $data['company_city'] ?? null,
                'zipcode' => $data['company_zipcode'] ?? null,
                'country' => $data['company_country'],
            ]);

            // Si create() retourne null → exception → rollback
            if (! $tenant) {
                throw new \Exception("Erreur création tenant");
            }


            // Création de l'utilisateur associé au tenant
            $user = UserIdentify::create([
                'id_user_identify' => (string) Str::uuid(), // Identifiant unique
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']), // Hash sécurisé du mot de passe
                'tenant_id' => $tenant->id_tenant, // Association avec le tenant créé
            ]);

            // Si create() retourne null → exception → rollback
            if (! $user) {
                throw new \Exception("Erreur création user");
            }


            return $user; // Retourne l'utilisateur créé
        });
    }

    /**
     * Connexion utilisateur
     * 
     * @param string $credential Email ou téléphone
     * @param string $password Mot de passe
     * @return User|null L'utilisateur si authentifié, sinon null
     */
    public function login(string $credential, string $password): ?UserIdentify
    {
        $query = UserIdentify::where('is_active', true); // Ne considérer que les utilisateurs actifs

        // Vérification si le credential est un email ou un numéro de téléphone
        if ($this->isEmail($credential)) {
            $query->where('email', $credential);
        } elseif ($this->isPhone($credential)) {
            $query->where('phone', $credential);
        } else {
            return null; // Credential invalide
        }

        $user = $query->first(); // Récupère le premier utilisateur correspondant

        // Vérifie si le mot de passe correspond
        if (!$user || !Hash::check($password, $user->password)) {
            return null; // Authentification échouée
        }

        return $user; // Authentification réussie
    }

    /**
     * Récupération du profil utilisateur
     * 
     * @param User $user Utilisateur authentifié
     * @return array Profil détaillé utilisateur + tenant
     */
    public function getProfile(UserIdentify $user): array
    {
        return [
            'user' => [
                'id' => $user->id_user_identify,
                'first_name' => $user->first_name, // Décrypté automatiquement si nécessaire
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $user->phone_verified_at,
            ],
            'tenant' => $user->tenant ? [
                'id' => $user->tenant->id_tenant,
                'name' => $user->tenant->name,
                'type' => $user->tenant->type,
                'sector' => $user->tenant->sector,
                'employees_number' => $user->tenant->employees_number,
                'country' => $user->tenant->country,
            ] : null
        ];
    }
}
