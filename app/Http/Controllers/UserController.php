<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    public function __construct()
    {
        ini_set('memory_limit', '-1');
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $pageTitle = 'User Management';
        $data = User::with('role')->orderBy('id','DESC')->paginate(10);

        return view('pages.users.users_index', compact('data','pageTitle'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('pages.users.users_create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'username' => 'required|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                User::create([
                    'name'     => $request->name,
                    'username' => $request->username,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id'  => $request->role_id,
                    'position' => $request->position ?? null,
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'User berhasil dibuat'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan user'
            ], 500);
        }
    }

    public function edit($id)
    {
        $user  = User::findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('pages.users.users_edit', compact('user','roles'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'username' => 'required|unique:users,username,'.$id,
            'email'    => 'required|email|unique:users,email,'.$id,
            'role_id'  => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $id) {

                $user = User::findOrFail($id);

                $data = $request->only([
                    'name','username','email','role_id','position'
                ]);

                if ($request->filled('password')) {
                    $data['password'] = Hash::make($request->password);
                }

                $user->update($data);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'User berhasil diperbarui'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui user'
            ], 500);
        }
    }
}