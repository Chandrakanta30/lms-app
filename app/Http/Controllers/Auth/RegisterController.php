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

        $generatedCorporateId = $this->generateCorporateId(); // renamed method to reflect corporate ID generation

        $user = User::create([
            'name' => $validated['name'],
            'corporate_id' => $generatedCorporateId, //  FIXED
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'deparment_id' => 1
        ]);

        $traineeRole = Role::findOrCreate('Trainee', 'web');
        $user->assignRole($traineeRole);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Registration completed successfully. Your Corporate ID is ' . $generatedCorporateId . '.');
    }

    private function generateCorporateId(): string
    {
        $nextId = (User::max('id') ?? 0) + 1;

        do {
            $candidate = 'TRN' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            $nextId++;
        } while (User::where('corporate_id', $candidate)->exists());  //fixed

        return $candidate;
    }
}
