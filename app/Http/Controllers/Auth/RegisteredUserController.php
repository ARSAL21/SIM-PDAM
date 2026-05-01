<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'nomor_pelanggan' => ['required', 'string'],
            'no_whatsapp' => ['required', 'string', 'max:20', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Query Data Pelanggan berdasarkan Nomor Input
        $pelanggan = \App\Models\Pelanggan::where('no_pelanggan', $request->nomor_pelanggan)->first();

        // 3. Validasi Kondisi A: Nomor tidak ditemukan di sistem
        if (!$pelanggan) {
            throw ValidationException::withMessages([
                'nomor_pelanggan' => 'Nomor Pelanggan tidak ditemukan. Pastikan ketikkan sesuai struk asli.',
            ]);
        }

        // 4. Validasi Kondisi B: Nomor sudah diklaim
        if ($pelanggan->user_id !== null) {
            throw ValidationException::withMessages([
                'nomor_pelanggan' => 'Nomor Pelanggan ini sudah ditautkan ke akun lain.',
            ]);
        }

        // 5. Validasi Kondisi C: Cross-check nama dengan data admin
        if (mb_strtolower(trim($request->name)) !== mb_strtolower(trim($pelanggan->nama_lengkap))) {
            throw ValidationException::withMessages([
                'name' => 'Nama tidak sesuai dengan data pelanggan yang terdaftar di sistem. Pastikan nama diisi sesuai KTP.',
            ]);
        }

        // 6. Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nomor_pelanggan' => $request->nomor_pelanggan,
            'no_whatsapp' => $request->no_whatsapp,
            'password' => Hash::make($request->password),
        ]);

        // assign role pelanggan
        $user->assignRole('pelanggan');

        // verify email
        $user->update([
            'email_verified_at' => now(),
        ]);

        // 7. Tautkan (Claim) ID user baru ke tabel Pelanggan
        $pelanggan->update([
            'user_id' => $user->id
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
