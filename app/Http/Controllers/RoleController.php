<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use App\Models\Menu;
use App\Logs;
use Auth;
use DB;

class RoleController extends Controller
{
    private function activityLog(string $section, string $content): void
    {
        try {
            (new Logs('Logs_RoleController'))->write($section, $content);
        } catch (\Throwable $e) {
            \Log::error('[RoleController] Gagal menulis log: ' . $e->getMessage());
        }
    }

    private function actor(): string
    {
        $user = Auth::user();
        if (!$user) return 'Guest';
        return $user->username ?? $user->name ?? "ID:{$user->id}";
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ================= INDEX ================= */
    public function index()
    {
        $roles = Role::orderBy('id','DESC')->get();
        return view('pages.roles.roles_index', compact('roles'));
    }

    /* ================= CREATE ================= */
    public function create()
    {
        $menus = Menu::where('is_active',1)
            ->orderBy('order_no')
            ->get()
            ->groupBy('parent_menu_id');

        $apps = DB::table('apps')->where('is_active',1)->get();

        return view('pages.roles.roles_create', compact('menus','apps'));
    }

    /* ================= STORE ================= */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|unique:roles,name'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => 'validation_error',
    //             'message' => $validator->errors()->first()
    //         ], 422);
    //     }

    //     DB::transaction(function () use ($request) {

    //         $role = Role::create([
    //             'name'       => $request->name,
    //             'guard_name' => 'web'
    //         ]);

    //         foreach ($request->permissions ?? [] as $menuId => $actions) {

    //             DB::table('role_menus')->insert([
    //                 'role_id'     => $role->id,
    //                 'menu_id'     => $menuId,

    //                 // MENU AKTIF = ADA DI FORM
    //                 'is_enabled'  => 1,

    //                 // VIEW
    //                 'can_view'    => isset($actions['view']) ? 1 : 0,

    //                 // ACTION
    //                 'can_create'  => isset($actions['create']) ? 1 : 0,
    //                 'can_update'  => isset($actions['update']) ? 1 : 0,
    //                 'can_delete'  => isset($actions['delete']) ? 1 : 0,
    //                 'can_approve' => isset($actions['approve']) ? 1 : 0,
    //                 'can_reject'  => isset($actions['reject']) ? 1 : 0,
    //                 'can_print'   => isset($actions['print']) ? 1 : 0,

    //                 'created_at'  => now(),
    //                 'updated_at'  => now(),
    //             ]);
    //         }

    //         foreach ($request->apps ?? [] as $appCode) {
    //             DB::table('role_apps')->insert([
    //                 'role_id'    => $role->id,
    //                 'app_code'   => $appCode,
    //                 'is_enabled' => 1,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    //     });

    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Role berhasil dibuat'
    //     ]);
    // }
    public function store(Request $request)
    {
        $namaRole = $request->name ?? '-';
        $this->activityLog('TAMBAH_ROLE', "User: {$this->actor()} | Nama Role: {$namaRole} | Status: PROCESS");

        try {
            DB::transaction(function () use ($request) {
                $role = Role::create([
                    'name'       => $request->name,
                    'guard_name' => 'web',
                ]);

                foreach ($request->permissions ?? [] as $menuId => $p) {
                    DB::table('role_menus')->insert([
                        'role_id'     => $role->id,
                        'menu_id'     => $menuId,
                        'is_enabled'  => isset($p['enabled']) && $p['enabled'] == 1 ? 1 : 0,
                        'can_view'    => isset($p['view'])    ? 1 : 0,
                        'can_create'  => isset($p['create'])  ? 1 : 0,
                        'can_update'  => isset($p['update'])  ? 1 : 0,
                        'can_delete'  => isset($p['delete'])  ? 1 : 0,
                        'can_approve' => isset($p['approve']) ? 1 : 0,
                        'can_reject'  => isset($p['reject'])  ? 1 : 0,
                        'can_print'   => isset($p['print'])   ? 1 : 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                foreach ($request->apps ?? [] as $appCode) {
                    DB::table('role_apps')->insert([
                        'role_id'    => $role->id,
                        'app_code'   => $appCode,
                        'is_enabled' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            $this->activityLog('TAMBAH_ROLE', "User: {$this->actor()} | Nama Role: {$namaRole} | Jumlah Menu: " . count($request->permissions ?? []) . " | Status: SUCCESS");

            return response()->json(['status' => 'success', 'message' => 'Role berhasil dibuat']);

        } catch (\Throwable $e) {
            $this->activityLog('TAMBAH_ROLE', "User: {$this->actor()} | Nama Role: {$namaRole} | Status: FAILED | Error: {$e->getMessage()} | File: {$e->getFile()}:{$e->getLine()}");
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'], 500);
        }
    }


    /* ================= EDIT ================= */
    public function edit($id)
    {
        $role = Role::findOrFail($id);

        $menus = Menu::where('is_active',1)
            ->orderBy('order_no')
            ->get()
            ->groupBy('parent_menu_id');

        $rolePermissions = DB::table('role_menus')
            ->where('role_id', $id)
            ->get()
            ->keyBy('menu_id');

        $apps = DB::table('apps')->where('is_active',1)->get();

        $roleApps = DB::table('role_apps')
            ->where('role_id', $id)
            ->pluck('app_code')
            ->toArray();

        return view('pages.roles.roles_edit', compact(
            'role','menus','rolePermissions','apps','roleApps'
        ));
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $id)
    {
        $role     = Role::find($id);
        $namaRole = $role->name ?? '-';
        $this->activityLog('UBAH_ROLE', "User: {$this->actor()} | ID: {$id} | Nama Role: {$namaRole} | Status: PROCESS");

        $validator = Validator::make($request->all(), ['name' => 'required']);

        if ($validator->fails()) {
            $this->activityLog('UBAH_ROLE', "User: {$this->actor()} | ID: {$id} | Nama Role: {$namaRole} | Status: VALIDATION_ERROR | Error: " . $validator->errors()->first());
            return response()->json(['status' => 'validation_error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::transaction(function () use ($request, $id) {
                Role::where('id', $id)->update(['name' => $request->name]);

                DB::table('role_menus')->where('role_id', $id)->delete();
                DB::table('role_apps')->where('role_id', $id)->delete();

                foreach ($request->permissions ?? [] as $menuId => $actions) {
                    DB::table('role_menus')->insert([
                        'role_id'     => $id,
                        'menu_id'     => $menuId,
                        'is_enabled'  => 1,
                        'can_view'    => isset($actions['view'])    ? 1 : 0,
                        'can_create'  => isset($actions['create'])  ? 1 : 0,
                        'can_update'  => isset($actions['update'])  ? 1 : 0,
                        'can_delete'  => isset($actions['delete'])  ? 1 : 0,
                        'can_approve' => isset($actions['approve']) ? 1 : 0,
                        'can_reject'  => isset($actions['reject'])  ? 1 : 0,
                        'can_print'   => isset($actions['print'])   ? 1 : 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                foreach ($request->apps ?? [] as $appCode) {
                    DB::table('role_apps')->insert([
                        'role_id'    => $id,
                        'app_code'   => $appCode,
                        'is_enabled' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            $this->activityLog('UBAH_ROLE', "User: {$this->actor()} | ID: {$id} | Nama Role: {$request->name} | Jumlah Menu: " . count($request->permissions ?? []) . " | Status: SUCCESS");

            return response()->json(['status' => 'success', 'message' => 'Role berhasil diperbarui']);

        } catch (\Throwable $e) {
            $this->activityLog('UBAH_ROLE', "User: {$this->actor()} | ID: {$id} | Nama Role: {$namaRole} | Status: FAILED | Error: {$e->getMessage()} | File: {$e->getFile()}:{$e->getLine()}");
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'], 500);
        }
    }
}