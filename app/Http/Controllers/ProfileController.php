<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Logs;
use Auth;
use Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function activityLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_ProfileController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[ProfileController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

    private function isOwnerOrAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->role && in_array(strtolower($user->role->name), ['owner', 'admin']);
    }

    public function showChangePassword()
    {
        if (!$this->isOwnerOrAdmin()) {
            abort(403, 'Akses ditolak. Hanya Owner yang dapat mengganti password.');
        }

        return view('pages.profile.change_password');
    }

    public function updatePassword(Request $request)
    {
        if (!$this->isOwnerOrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $this->activityLog('GANTI_PASSWORD', "User: {$this->actor()} | Status: PROCESS");

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password'     => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&^()\-_+=\[\]{};:\'",.<>\\/\\\\|~]/',
            ],
        ], [
            'current_password.required'  => 'Password lama wajib diisi.',
            'new_password.required'      => 'Password baru wajib diisi.',
            'new_password.min'           => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'     => 'Konfirmasi password baru tidak cocok.',
            'new_password.regex'         => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol (contoh: @$!#%).',
        ]);

        if ($validator->fails()) {
            $this->activityLog('GANTI_PASSWORD', "User: {$this->actor()} | Status: VALIDATION_ERROR | Error: " . $validator->errors()->first());
            return response()->json(['status' => 'validation_error', 'message' => $validator->errors()->first()], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            $this->activityLog('GANTI_PASSWORD', "User: {$this->actor()} | Status: WRONG_CURRENT_PASSWORD");
            return response()->json(['status' => 'validation_error', 'message' => 'Password lama tidak sesuai.'], 422);
        }

        try {
            $user->update(['password' => Hash::make($request->new_password)]);
            $this->activityLog('GANTI_PASSWORD', "User: {$this->actor()} | Status: SUCCESS");
            return response()->json(['status' => 'success', 'message' => 'Password berhasil diperbarui.']);
        } catch (\Throwable $e) {
            $this->activityLog('GANTI_PASSWORD', "User: {$this->actor()} | Status: FAILED | Error: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui password.'], 500);
        }
    }
}
