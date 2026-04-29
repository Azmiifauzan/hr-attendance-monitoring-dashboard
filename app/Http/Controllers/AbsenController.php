<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsenController extends Controller
{
    public function index(Request $request)
    {
        $data = [];

        if ($request->filled('keyword') && $request->filled('tanggal')) {

            $keyword = $request->keyword;
            $tanggal = $request->tanggal;

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
                    d.\"Name\" AS \"DivisionName\"
                FROM \"Member\".\"ATT_ClockRequest\" A
                JOIN \"Member\".\"CM_Employee\" e ON A.\"EmployeeId\" = e.\"EmployeeId\"
                JOIN \"Member\".\"V_EmployeeName\" v ON e.\"EmployeeId\" = v.\"EmployeeId\"
                JOIN \"Member\".\"CM_Branch\" b ON e.\"BranchId\" = b.\"BranchId\"
                LEFT JOIN \"Member\".\"CM_JobTitle\" jt ON e.\"JobTitleId\" = jt.\"JobTitleId\"
                LEFT JOIN \"Member\".\"CM_Division\" d ON jt.\"DivisionId\" = d.\"Id\"
                LEFT JOIN \"Member\".\"ATT_ClockRequestMobile\" crm 
                    ON A.\"ClockRequestId\" = crm.\"ClockRequestId\"
                WHERE A.\"IsDeleted\" IS FALSE
                AND (
                    v.\"FullName\" ILIKE ?
                    OR e.\"EmployeeNo\" ILIKE ?
                )
                AND DATE(A.\"ClockDate\") = ?
                ORDER BY A.\"ClockTime\" DESC
            ", ["%$keyword%", "%$keyword%", $tanggal]);
        }

        // 🔥 INI YANG KAMU KURANG
        return view('absen.index', compact('data'));
    }

    public function foto($id)
    {
        $data = DB::connection('hris')->selectOne("
            SELECT \"ClockDate\"
            FROM \"Member\".\"ATT_ClockRequest\"
            WHERE \"ClockRequestId\" = ?
        ", [$id]);

        if (!$data) {
            abort(404, 'Data tidak ditemukan');
        }

        $dt = new \DateTime($data->ClockDate);
        $tahun = $dt->format('Y');
        $bulan = $dt->format('m');

        $path = "E:/FOTO/$tahun/$bulan/$id.jpg";

        if (!file_exists($path)) {
            return response("File tidak ditemukan: $path", 404);
        }

        return response()->file($path);
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
}