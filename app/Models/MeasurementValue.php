<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class MeasurementValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_id',
        'parameter_id',
        'value'
    ];

    public static function newMeasurementValue(array $data)
    {
        $rules = [
            'measurement_id' => 'required',
            'parameter_id' => 'required',
            'value' => 'required',
        ];

        $validation = Validator::make($data, $rules);

        if ($validation->fails()) {
            return [
                'success' => false,
                'message' => 'Measurement data validation error',
                'data' => $validation->errors()
            ];
        } else {
            $measurement_value = self::create([
                'measurement_id' => $data['measurement_id'],
                'parameter_id' => $data['parameter_id'],
                'value' => $data['value'],
            ]);
            if ($measurement_value->save()) {
                return [$measurement_value->id];
            } else {
                return [
                    'success' => false,
                    'message' => 'Measurement cannot be added'
                ];
            }
        }
    }

    public static function updateMeasurementValue(array $data)
    {
        $validation = Validator::make($data, ['parameter_id' => 'required']);

        if ($validation->fails()) {
            return [
                'success' => false,
                'message' => 'Measurement data validation error',
                'data' => $validation->errors()
            ];
        } else {
            $measurement = self::where('parameter_id', $data['parameter_id'])->first();
            if (empty($measurement)) {
                return [
                    'success' => true,
                    'message' => 'Measurement does not exist'
                ];
            } else {
                $measurement->value = $data['value'];
                $measurement->save();
                return [
                    $measurement->id
                ];
            }
        }
    }
}
