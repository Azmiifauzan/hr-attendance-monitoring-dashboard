<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActiveCompany;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $allCompanies = DB::connection('hris')->select("
            SELECT \"CompanyId\", TRIM(\"Name\") AS \"Name\"
            FROM \"Member\".\"CM_Company\"
            WHERE \"Name\" IS NOT NULL
            GROUP BY \"CompanyId\", TRIM(\"Name\")
            ORDER BY TRIM(\"Name\")
        ");

        $activeIds = ActiveCompany::pluck('company_id')->toArray();

        return view('companies.index', compact('allCompanies', 'activeIds'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        ActiveCompany::truncate();

        if ($request->companies) {
            foreach ($request->companies as $id => $name) {
                ActiveCompany::create([
                    'company_id'   => $id,
                    'company_name' => $name,
                ]);
            }
        }

        return redirect('/companies')->with('success', 'PT aktif berhasil disimpan!');
    }
}