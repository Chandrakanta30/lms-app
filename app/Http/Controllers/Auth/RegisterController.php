<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $generatedUserId = $this->generateUserId();

        $user = User::create([
            'name' => $validated['name'],
            'user_id' => $generatedUserId,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $traineeRole = Role::findOrCreate('Trainee', 'web');
        $user->assignRole($traineeRole);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Registration completed successfully. Your user ID is ' . $generatedUserId . '.');
    }

    private function generateUserId(): string
    {
        $nextId = (User::max('id') ?? 0) + 1;

        do {
            $candidate = 'TRN' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            $nextId++;
        } while (User::where('user_id', $candidate)->exists());

        return $candidate;
    }
}
