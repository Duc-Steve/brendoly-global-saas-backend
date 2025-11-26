<?php

namespace App\Core\Auth\ServiceATN;

use App\Core\Auth\TraitsATN\AssistantTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service de gestion des mots de passe
 * Supporte les réinitialisations et changements via email ou téléphone
 */
class PasswordService
{
    use AssistantTrait; // Fournit des méthodes auxiliaires comme isEmail() et isPhone()

    /**
     * Demande de réinitialisation de mot de passe
     * Envoie un code de vérification par email ou SMS
     */
    public function requestPasswordReset(string $credential): bool
    {
        try {
            // Recherche de l'utilisateur actif correspondant à la donnée fournie
            $user = $this->findUserByCredential($credential);

            if (!$user) {
                // Retourne true même si l'utilisateur n'existe pas
                // pour ne pas révéler l'existence d'un compte
                return true;
            }

            // Génération du code de vérification
            $code = $this->generateVerificationCode();

            // Stocke ou met à jour le token dans la table password_reset_tokens
            PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                [
                    'code' => $code,
                    'created_at' => now()
                ]
            );

            // Envoi du code par email ou SMS selon le type de credential
            $this->sendVerificationCode($user, $code, $credential);

            return true;

        } catch (Exception $e) {
            Log::error("Password reset request failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie la validité du code envoyé pour la réinitialisation
     */
    public function verifyCode(string $credential, string $code): bool
    {
        try {
            $user = $this->findUserByCredential($credential);
            if (!$user) return false;

            // Récupère le token de réinitialisation correspondant
            $resetToken = PasswordResetToken::where('email', $user->email)->first();

            if (!$resetToken || $resetToken->code !== $code) return false;

            // Vérifie si le token n'a pas expiré (15 minutes)
            return $resetToken->created_at->diffInMinutes(now()) <= 15;

        } catch (Exception $e) {
            Log::error("Code verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialise le mot de passe avec le code de vérification
     */
    public function resetPassword(string $credential, string $code, string $password): bool
    {
        try {
            $user = $this->findUserByCredential($credential);
            if (!$user) return false;

            $resetToken = PasswordResetToken::where('email', $user->email)->first();
            if (!$resetToken || $resetToken->code !== $code) return false;

            // Vérifie expiration du code
            if ($resetToken->created_at->diffInMinutes(now()) > 15) {
                $resetToken->delete(); // Supprime le token expiré
                return false;
            }

            // Met à jour le mot de passe avec un hash sécurisé
            $user->update(['password' => Hash::make($password)]);

            // Supprime le token utilisé pour éviter toute réutilisation
            $resetToken->delete();

            return true;

        } catch (Exception $e) {
            Log::error("Password reset failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Demande de changement de mot de passe pour un utilisateur connecté
     * Génère un code à envoyer par email
     */
    public function requestPasswordChange(User $user): bool
    {
        try {
            $code = $this->generateVerificationCode();

            PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                ['code' => $code, 'created_at' => now()]
            );

            $this->sendVerificationCode($user, $code, $user->email);

            return true;

        } catch (Exception $e) {
            Log::error("Password change request failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change le mot de passe avec code de vérification (sans ancien mot de passe)
     */
    public function changePasswordWithCode(User $user, string $code, string $newPassword): bool
    {
        try {
            $resetToken = PasswordResetToken::where('email', $user->email)->first();
            if (!$resetToken || $resetToken->code !== $code) return false;

            if ($resetToken->created_at->diffInMinutes(now()) > 15) {
                $resetToken->delete();
                return false;
            }

            $user->update(['password' => Hash::make($newPassword)]);
            $resetToken->delete();

            return true;

        } catch (Exception $e) {
            Log::error("Password change with code failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie le code pour un changement de mot de passe
     */
    public function verifyPasswordChangeCode(User $user, string $code): bool
    {
        try {
            $resetToken = PasswordResetToken::where('email', $user->email)->first();
            if (!$resetToken || $resetToken->code !== $code) return false;

            return $resetToken->created_at->diffInMinutes(now()) <= 15;

        } catch (Exception $e) {
            Log::error("Password change code verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Changement de mot de passe classique avec ancien mot de passe
     */
    public function changePasswordWithCurrent(User $user, string $currentPassword, string $newPassword): bool
    {
        try {
            // Vérifie l'ancien mot de passe
            if (!Hash::check($currentPassword, $user->password)) return false;

            $user->update(['password' => Hash::make($newPassword)]);

            return true;

        } catch (Exception $e) {
            Log::error("Password change with current failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recherche un utilisateur par email ou téléphone
     */
    private function findUserByCredential(string $credential): ?User
    {
        try {
            $query = User::where('is_active', true);

            if ($this->isEmail($credential)) $query->where('email', $credential);
            elseif ($this->isPhone($credential)) $query->where('phone', $credential);
            else return null;

            return $query->first();

        } catch (Exception $e) {
            Log::error("Find user by credential failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Envoi du code de vérification par email ou SMS
     */
    private function sendVerificationCode(User $user, string $code, string $credentialUsed): void
    {
        try {
            if ($this->isEmail($credentialUsed)) {
                $this->sendEmailVerificationCode($user->email, $code, $user->first_name);
            } else {
                $this->sendSmsVerificationCode($user->phone, $code);
            }

            Log::info("Verification code sent", [
                'user_id' => $user->id_user,
                'credential' => $credentialUsed,
                'code' => $code
            ]);

        } catch (Exception $e) {
            Log::error("Send verification code failed: " . $e->getMessage());
        }
    }

    /**
     * Envoi du code par email
     */
    private function sendEmailVerificationCode(string $email, string $code, string $firstName): void
    {
        try {
            $data = ['code' => $code, 'firstName' => $firstName, 'expiration' => 15];
            Mail::send('emails.verification-code', $data, function ($message) use ($email) {
                $message->to($email)->subject('Votre code de vérification');
            });
            Log::info("Verification code email sent to: {$email}");
        } catch (Exception $e) {
            Log::error("Email verification code failed: " . $e->getMessage());
            Log::info("VERIFICATION CODE for {$email}: {$code}");
        }
    }

    /**
     * Envoi du code par SMS
     */
    private function sendSmsVerificationCode(string $phone, string $code): void
    {
        try {
            Log::info("SMS verification code for {$phone}: {$code}");
            // Implémentation à compléter avec un service SMS (ex: Twilio)
        } catch (Exception $e) {
            Log::error("SMS verification code failed: " . $e->getMessage());
        }
    }

    /**
     * Supprime les tokens expirés (plus de 15 minutes)
     */
    public function cleanupExpiredTokens(): void
    {
        try {
            $expiredTokens = PasswordResetToken::where(
                'created_at', '<', now()->subMinutes(16)
            )->delete();

            Log::info("Cleaned up {$expiredTokens} expired password reset tokens");
        } catch (Exception $e) {
            Log::error("Cleanup expired tokens failed: " . $e->getMessage());
        }
    }
}
