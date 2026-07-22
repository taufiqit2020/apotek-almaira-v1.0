<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        // If password fields are filled
        if ($request->filled('password')) {
            $rules['old_password'] = 'required';
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules, [
            'old_password.required' => 'Password lama wajib diisi untuk mengubah password.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'avatar.image' => 'File yang diupload harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'avatar.max' => 'Ukuran gambar maksimal adalah 2 MB.',
        ]);

        // Verify old password if changing password
        if ($request->filled('password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'Password lama yang Anda masukkan salah.'])->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        $oldData = $user->toArray();
        unset($oldData['password']);

        // Handle avatar upload (cropped base64 or raw file)
        if ($request->filled('cropped_avatar')) {
            $base64Data = $request->input('cropped_avatar');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatches)) {
                $fileData = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
                $extension = strtolower($typeMatches[1]);
                if (in_array($extension, ['jpeg', 'png', 'jpg', 'webp']) && $fileData !== false) {
                    if ($user->avatar && file_exists(public_path($user->avatar))) {
                        @unlink(public_path($user->avatar));
                    }
                    $filename = 'avatar_' . $user->id . '_' . time() . '.' . $extension;
                    $destDir = public_path('uploads/avatars');
                    if (!file_exists($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    file_put_contents($destDir . '/' . $filename, $fileData);
                    $user->avatar = 'uploads/avatars/' . $filename;
                }
            }
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/avatars'), $filename);
            $user->avatar = 'uploads/avatars/' . $filename;
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->save();

        $newData = $user->toArray();
        unset($newData['password']);

        ActivityLogService::updated('Profile', 'Memperbarui profil mandiri', $oldData, $newData);

        return back()->with('toast_success', 'Profil Anda berhasil diperbarui!');
    }
}
