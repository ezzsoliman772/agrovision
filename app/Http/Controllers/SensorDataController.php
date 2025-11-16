<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SensorHistoryExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SensorDataController extends Controller
{
    public function export()
    {
        return Excel::download(new SensorHistoryExport, 'sensor_history.xlsx');
    }
}
