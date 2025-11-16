<?php

namespace App\Exports;

use App\Models\SensorData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SensorHistoryExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return SensorData::select(
            'sensor_id',
            'ec',
            'fertility',
            'hum',
            'k',
            'n',
            'p',
            'ph',
            'temp',
            'recorded_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Sensor ID',
            'Electrical Conductivity (EC)',
            'Fertility',
            'Humidity',
            'Potassium (K)',
            'Nitrogen (N)',
            'Phosphorus (P)',
            'pH Level',
            'Temperature',
            'Timestamp',
        ];
    }
}
