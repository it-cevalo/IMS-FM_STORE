<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;
use DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roles = Role::orderBy('id','DESC')->get();
        return view('pages.roles.roles_index', compact('roles'));
    }

    public function create()
    {
        $menus = Menu::where('is_active',1)
            ->orderBy('order_no')
            ->get()
            ->groupBy('parent_menu_id');
        $apps = DB::table('apps')->where('is_active',1)->get();

        return view('pages.roles.roles_create', compact('menus','apps'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => $validator->errors()->first()
            ], 422);
        }
    
        try {
            DB::transaction(function () use ($request) {
    
                $role = Role::create([
                    'name' => $request->name,
                    'guard_name' => 'web'
                ]);
    
                foreach ($request->menus ?? [] as $menuId) {
                    DB::table('role_menus')->insert([
                        'role_id'    => $role->id,
                        'menu_id'    => $menuId,
                        'is_enabled' => 1,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ]);
                }
                
                foreach ($request->apps ?? [] as $appCode) {
                    DB::table('role_apps')->insert([
                        'role_id' => $role->id,
                        'app_code' => $appCode,
                        'is_enabled' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    
            return response()->json([
                'status'  => 'success',
                'message' => 'Role berhasil dibuat'
            ]);
    
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat role'
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $role = Role::findOrFail($id);
    
        $menus = Menu::where('is_active',1)
            ->orderBy('order_no')
            ->get()
            ->groupBy('parent_menu_id');
    
        $roleMenus = DB::table('role_menus')
            ->where('role_id', $id)
            ->pluck('menu_id')
            ->toArray();
    
        $apps = DB::table('apps')->where('is_active',1)->get();
    
        $roleApps = DB::table('role_apps')
            ->where('role_id', $id)
            ->pluck('app_code')
            ->toArray();
    
        return view('pages.roles.roles_edit', compact(
            'role','menus','roleMenus','apps','roleApps'
        ));
    }

    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => 'validation_error',
    //             'message' => $validator->errors()->first()
    //         ], 422);
    //     }
    
    //     try {
    //         DB::transaction(function () use ($request, $id) {
    
    //             $role = Role::findOrFail($id);
    //             $role->update(['name' => $request->name]);
    
    //             DB::table('role_menus')->where('role_id', $id)->delete();
    
    //             foreach ($request->menus ?? [] as $menuId) {
    //                 DB::table('role_menus')->insert([
    //                     'role_id' => $id,
    //                     'menu_id' => $menuId,
    //                     'is_enabled' => 1,
    //                     'created_at'=> now(),
    //                     'updated_at'=> now(),
    //                 ]);
    //             }
                
    //             foreach ($request->apps ?? [] as $appCode) {
    //                 DB::table('role_apps')->insert([
    //                     'role_id' => $role->id,
    //                     'app_code' => $appCode,
    //                     'is_enabled' => 1,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         });
    
    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Role berhasil diperbarui'
    //         ]);
    
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Gagal memperbarui role'
    //         ], 500);
    //     }
    // }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $id) {

                $role = Role::findOrFail($id);
                $role->update(['name' => $request->name]);

                // ================= WEB MENU =================
                DB::table('role_menus')->where('role_id', $id)->delete();

                foreach ($request->menus ?? [] as $menuId) {
                    DB::table('role_menus')->insert([
                        'role_id'    => $id,
                        'menu_id'    => $menuId,
                        'is_enabled' => 1,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ]);
                }

                // ================= APPS MENU (INI YANG KURANG) =================
                DB::table('role_apps')->where('role_id', $id)->delete();

                foreach ($request->apps ?? [] as $appCode) {
                    DB::table('role_apps')->insert([
                        'role_id'    => $id,
                        'app_code'   => $appCode,
                        'is_enabled' => 1,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ]);
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Role berhasil diperbarui'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() // 👈 sementara biar keliatan
            ], 500);
        }
    }
}