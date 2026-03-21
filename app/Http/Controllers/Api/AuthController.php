<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\School;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:admin,teacher,student,guardian',
            'password' => 'required|string|confirmed|min:8',

            // Fields for creating a new school (required only for admin)
            'school_name' => 'required_if:role,admin|string|max:255',
            'school_type_id' => 'required_if:role,admin|exists:school_types,id',
            'phone' => 'required_if:role,admin|string|max:20|unique:schools,phone',

            // Field for joining an existing school (required for others)
            'school_id' => 'required_unless:role,admin|exists:schools,id',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Create User first
            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // 2. Handle School association
            if ($request->role === 'admin') {
                // Create a new school for the admin
                $school = School::create([
                    'name' => $request->school_name,
                    'phone' => $request->phone,
                    'school_type_id' => $request->school_type_id,
                    'owner_id' => $user->id,
                    'slug' => Str::slug($request->school_name),
                    'invite_code' => strtoupper(Str::random(6)),
                    'active' => true,
                ]);

                // Link admin to their new school
                $user->update(['school_id' => $school->id]);
            }
            else {
                // Link non-admin user to the existing school provided
                $user->update(['school_id' => $request->school_id]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token, 'user' => $user->load('school')], 201);
        });
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user->load('school')], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Senha alterada com sucesso.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['school', 'subscription.plan']);

        $currentPlan = null;
        $subscription = $user->subscription;

        if ($subscription) {
            $currentPlan = [
                'id'            => $subscription->plan->id,
                'name'          => $subscription->plan->name,
                'description'   => $subscription->plan->description,
                'price'         => $subscription->plan->price,
                'billing_cycle' => $subscription->plan->billing_cycle,
                'status'        => $subscription->status,
                'is_trial'      => $subscription->isTrial(),
                'is_expired'    => $subscription->isExpired(),
                'expires_at'    => $subscription->ends_at,
                'trial_ends_at' => $subscription->trial_ends_at,
            ];
        }

        return response()->json(array_merge($user->toArray(), ['currentPlan' => $currentPlan]));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Link de recuperação enviado com sucesso.'])
            : response()->json(['message' => 'Não foi possível enviar o link de recuperação.', 'status' => $status], 400);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(\Illuminate\Support\Str::random(60));

            $user->save();

            event(new \Illuminate\Auth\Events\PasswordReset($user));
        }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Sua senha foi redefinida com sucesso!'])
            : response()->json(['message' => 'Não foi possível redefinir sua senha.', 'status' => $status], 400);
    }
}