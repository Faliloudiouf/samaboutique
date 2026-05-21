<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['userModel' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:150','unique:users,email,'.$user->id],
            'telephone' => ['nullable','string','max:30'],
            'photo' => ['nullable','image','max:2048'],
        ]);
        if ($request->hasFile('photo')) {
            if ($user->photo) Storage::disk('public')->delete($user->photo);
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }
        $user->update($data);
        return back()->with('ok', 'Profil mis à jour.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required','current_password'],
            'password' => ['required','confirmed', Password::min(6)],
        ]);
        $request->user()->update(['password' => Hash::make($request->input('password'))]);
        return back()->with('ok', 'Mot de passe modifié.');
    }

    public function deletePhoto(Request $request)
    {
        $user = $request->user();
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->update(['photo' => null]);
        }
        return back()->with('ok', 'Photo supprimée.');
    }
}
