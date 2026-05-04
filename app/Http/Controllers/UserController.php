<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $users = User::all();

        $companies = \DB::connection('hris')->select("
            SELECT DISTINCT \"CompanyId\", TRIM(\"Name\") AS \"Name\"
            FROM \"Member\".\"CM_Company\"
            ORDER BY TRIM(\"Name\")
        ");

        return view('users.index', compact('users', 'companies'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->username,
            'password' => bcrypt($request->password),
            'role'     => $request->role ?? 'user',
        ]);

        if ($request->companies) {
            foreach (array_filter($request->companies) as $c) {
                \App\Models\UserCompany::create([
                    'user_id'      => $user->id,
                    'company_code' => $c
                ]);
            }
        }

        return back();
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $user = User::findOrFail($id);
        $users = User::all();
        $companies = \DB::connection('hris')->select("
            SELECT DISTINCT \"CompanyId\", TRIM(\"Name\") AS \"Name\"
            FROM \"Member\".\"CM_Company\"
            ORDER BY TRIM(\"Name\")
        ");
        $userCompanies = \App\Models\UserCompany::where('user_id', $id)
            ->pluck('company_code')
            ->toArray();

        return view('users.index', compact('users', 'companies', 'user', 'userCompanies'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $user = User::findOrFail($id);
        $user->name     = $request->name;
        $user->username = $request->username;
        $user->role     = $request->role ?? 'user';

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        \App\Models\UserCompany::where('user_id', $id)->delete();
        if ($request->companies) {
            foreach (array_filter($request->companies) as $c) {
                \App\Models\UserCompany::create([
                    'user_id'      => $id,
                    'company_code' => $c
                ]);
            }
        }

        return redirect('/users');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        // Cegah admin hapus diri sendiri
        if (auth()->user()->id == $id) {
            return redirect('/users')->with('error', 'Tidak bisa hapus akun sendiri!');
        }

        \App\Models\UserCompany::where('user_id', $id)->delete();
        User::findOrFail($id)->delete();

        return redirect('/users');
    }
}