<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsenController extends Controller
{
    public function index(Request $request)
    {
        $data = [];

        // Ambil list PT berdasarkan akses user
        $user = auth()->user();

        if ($user->role === 'admin') {
            $activeCompanies = \App\Models\ActiveCompany::all();
            if ($activeCompanies->isEmpty()) {
            $companies = DB::connection('hris')->select("
                SELECT \"CompanyId\", TRIM(\"Name\") AS \"Name\"
                FROM \"Member\".\"CM_Company\"
                WHERE \"Name\" IS NOT NULL
                GROUP BY \"CompanyId\", TRIM(\"Name\")
                ORDER BY TRIM(\"Name\")
            ");
        } else {
            $ids = $activeCompanies->pluck('company_id')->toArray();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $companies = DB::connection('hris')->select("
                SELECT \"CompanyId\", TRIM(\"Name\") AS \"Name\"
                FROM \"Member\".\"CM_Company\"
                WHERE \"CompanyId\" IN ($placeholders)
                AND \"Name\" IS NOT NULL
                GROUP BY \"CompanyId\", TRIM(\"Name\")
                ORDER BY TRIM(\"Name\")
            ", $ids);
        }
        } else {
        $userCompanyCodes = \App\Models\UserCompany::where('user_id', $user->id)
            ->pluck('company_code')
            ->toArray();

            if (empty($userCompanyCodes)) {
                $companies = [];
            } else {
            $placeholders = implode(',', array_fill(0, count($userCompanyCodes), '?'));
            $companies = DB::connection('hris')->select("
                SELECT \"CompanyId\", TRIM(\"Name\") AS \"Name\"
                FROM \"Member\".\"CM_Company\"
                WHERE \"CompanyId\" IN ($placeholders)
                AND \"Name\" IS NOT NULL
                GROUP BY \"CompanyId\", TRIM(\"Name\")
                ORDER BY TRIM(\"Name\")
            ", $userCompanyCodes);
        }
    }

        // Ambil divisi sesuai PT yang dipilih
        $divisions = [];
        if ($request->filled('company_id')) {
            $divisions = DB::connection('hris')->select("
            SELECT \"Id\", TRIM(\"Name\") AS \"Name\"
            FROM \"Member\".\"CM_Division\"
            WHERE \"IsDeleted\" IS FALSE
            AND \"CompanyId\" = ?
            AND TRIM(\"Name\") IS NOT NULL
            GROUP BY \"Id\", TRIM(\"Name\")
            ORDER BY TRIM(\"Name\")
        ", [$request->company_id]);
        }

        if ($request->filled('tanggal_dari') || $request->filled('tanggal')) {

            $keyword       = $request->keyword;
            $tanggalDari   = $request->tanggal_dari ?? $request->tanggal;
            $tanggalSampai = $request->tanggal_sampai ?? $tanggalDari;
            $divisionId    = $request->division_id;
            $companyId     = $request->company_id;

            $keywordFilter  = $keyword    ? "AND (v.\"FullName\" ILIKE ? OR e.\"EmployeeNo\" ILIKE ?)" : "";
            $divisionFilter = $divisionId ? "AND d.\"Id\" = ?"       : "";
            $companyFilter  = $companyId  ? "AND e.\"CompanyId\" = ?" : "";

            // Kalau bukan admin, paksa filter hanya PT yang dia punya akses
            $userAccessFilter = "";
            if ($user->role !== 'admin') {
                $userCompanyCodes = \App\Models\UserCompany::where('user_id', $user->id)
                    ->pluck('company_code')
                    ->toArray();
                if (!empty($userCompanyCodes)) {
                    $placeholders = implode(',', array_fill(0, count($userCompanyCodes), '?'));
                    $userAccessFilter = "AND e.\"CompanyId\" IN ($placeholders)";
                } else {
                    // User tidak punya akses PT apapun, return kosong
                    return view('absen.index', compact('data', 'companies', 'divisions'));
                }
            }

            $params = [];
            if ($keyword) { $params[] = "%$keyword%"; $params[] = "%$keyword%"; }
            $params[] = $tanggalDari;
            $params[] = $tanggalSampai;
            if ($companyId)  $params[] = $companyId;
            if ($divisionId) $params[] = $divisionId;
            if ($user->role !== 'admin' && !empty($userCompanyCodes)) {
            $params = array_merge($params, $userCompanyCodes);
        }

    $data = DB::connection('hris')->select("
        SELECT 
            A.\"ClockRequestId\",
            A.\"ClockDate\",
            A.\"ClockTime\",
            crm.\"Latitude\",
            crm.\"Longitude\",
            v.\"FullName\",
            e.\"EmployeeNo\",
            b.\"BranchName\",
            TRIM(d.\"Name\") AS \"DivisionName\",
            TRIM(c.\"Name\") AS \"CompanyName\"
        FROM \"Member\".\"ATT_ClockRequest\" A
        JOIN \"Member\".\"CM_Employee\" e ON A.\"EmployeeId\" = e.\"EmployeeId\"
        JOIN \"Member\".\"V_EmployeeName\" v ON e.\"EmployeeId\" = v.\"EmployeeId\"
        JOIN \"Member\".\"CM_Branch\" b ON e.\"BranchId\" = b.\"BranchId\"
        JOIN \"Member\".\"CM_Company\" c ON e.\"CompanyId\" = c.\"CompanyId\"
        LEFT JOIN \"Member\".\"CM_JobTitle\" jt ON e.\"JobTitleId\" = jt.\"JobTitleId\"
        LEFT JOIN \"Member\".\"CM_Division\" d ON jt.\"DivisionId\" = d.\"Id\"
        LEFT JOIN \"Member\".\"ATT_ClockRequestMobile\" crm 
            ON A.\"ClockRequestId\" = crm.\"ClockRequestId\"
        WHERE A.\"IsDeleted\" IS FALSE
        $keywordFilter
        AND DATE(A.\"ClockDate\") BETWEEN ? AND ?
        $companyFilter
        $divisionFilter
        $userAccessFilter
        ORDER BY A.\"ClockDate\" DESC, A.\"ClockTime\" DESC
    ", $params);
}

        return view('absen.index', compact('data', 'companies', 'divisions'));
    }

    public function getDivisions(Request $request)
    {
        $companyId = $request->company_id;
        if (!$companyId) return response()->json([]);

        $divisions = DB::connection('hris')->select("
            SELECT \"Id\", TRIM(\"Name\") AS \"Name\"
            FROM \"Member\".\"CM_Division\"
            WHERE \"IsDeleted\" IS FALSE
            AND \"CompanyId\" = ?
            AND TRIM(\"Name\") IS NOT NULL
            GROUP BY \"Id\", TRIM(\"Name\")
            ORDER BY TRIM(\"Name\")
        ", [$companyId]);

        return response()->json($divisions);
    }

    public function autocomplete(Request $request)
    {
        $keyword = $request->q;
        if (!$keyword) return response()->json([]);

        $results = DB::connection('hris')->select("
            SELECT DISTINCT v.\"FullName\", e.\"EmployeeNo\"
            FROM \"Member\".\"V_EmployeeName\" v
            JOIN \"Member\".\"CM_Employee\" e ON v.\"EmployeeId\" = e.\"EmployeeId\"
            WHERE v.\"FullName\" ILIKE ? OR e.\"EmployeeNo\" ILIKE ?
            LIMIT 8
        ", ["%$keyword%", "%$keyword%"]);

        return response()->json($results);
    }

    public function foto($id)
    {
        $data = DB::connection('hris')->selectOne("
            SELECT \"ClockDate\"
            FROM \"Member\".\"ATT_ClockRequest\"
            WHERE \"ClockRequestId\" = ?
        ", [$id]);

        if (!$data) abort(404, 'Data tidak ditemukan');

        $dt = new \DateTime($data->ClockDate);
        $tahun = $dt->format('Y');
        $bulan = $dt->format('m');
        $pathInternal = "/mnt/data-internal/FOTO/$tahun/$bulan/$id.jpg";
        $pathExternal = "/mnt/foto/FOTO/$tahun/$bulan/$id.jpg";

        if (file_exists($pathInternal)) {
            return response()->file($pathInternal);
        } elseif (file_exists($pathExternal)) {
            return response()->file($pathExternal);
        } else {
            return response("File tidak ditemukan", 404);
        }
    }
}