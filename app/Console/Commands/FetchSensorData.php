<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\SensorData;
use Kreait\Firebase\Factory;
use Carbon\Carbon;

class FetchSensorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:sensor-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Firebase and store in MySQL';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // إعداد Firebase
        try {
            $firebase = (new Factory)
                ->withServiceAccount(config('services.firebase.credentials_file'))
                ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');
    
            $database = $firebase->createDatabase();
    
            // جلب البيانات من Firebase
            $data = $database->getReference('sensor_data')->getValue();
    
            // إذا كانت البيانات موجودة، نقوم بتخزينها في MySQL
            if ($data) {
                foreach ($data as $key => $sensor) {
                    // التأكد من وجود timestamp في البيانات
                    if (!isset($sensor['timestamp'])) {
                        continue; // تخطي الإدخال إذا لم يحتوي على timestamp
                    }
                
                    // تحويل الـ timestamp إلى DateTime format
                    $timestamp = Carbon::parse($sensor['timestamp'])->toDateTimeString();
                
                    // التحقق من وجود نفس القيم بنفس الـ timestamp لمنع التكرار
                    $existingData = SensorData::where('sensor_id', $sensor['sensor_id'])
                                              ->where('recorded_at', $timestamp)
                                              ->first();
                
                    if (!$existingData) {
                        SensorData::create([
                            'sensor_id' => $sensor['sensor_id'],
                            'ec' => $sensor['EC'] ?? null,
                            'fertility' => $sensor['Fertility'] ?? null,
                            'hum' => $sensor['Hum'] ?? null,
                            'k' => $sensor['K'] ?? null,
                            'n' => $sensor['N'] ?? null,
                            'p' => $sensor['P'] ?? null,
                            'ph' => $sensor['PH'] ?? null,
                            'temp' => $sensor['Temp'] ?? null,
                            'recorded_at' => $timestamp,
                        ]);
                    }
                }
    
                $this->info('Sensor data fetching and storing process completed.');
            } else {
                $this->error('No data found from Firebase.');
            }
        } catch (\Exception $e) {
            $this->error('Error fetching or storing data: ' . $e->getMessage());
        }
    }
}
