<?php

namespace App\Http\Controllers;

use App\Models\ActivityData;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CSVUploadRequest;
use Illuminate\Support\Facades\Log;

class CSVUploadController extends Controller
{
    public function upload(CSVUploadRequest $request)
    {
        $file = $request->file('file');
        // $path = $file->storeAs('uploads/csv', time() . '-' . $file->getClientOriginalName());

        $success = $this->processCSV($file);

        if ($success) {
            return response()->json(['message' => 'CSV file cleaned and data stored successfully.']);
        } else {
            return response()->json(['message' => 'Error processing CSV file.'], 500);
        }
    }

    protected function processCSV($file){
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            Log::error('Failed to open CSV file: ' . $file->getPathname());
            return response()->json(['message' => 'Error reading the file.'], 400);
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'cleaned_csv');
        $cleanedHandle = fopen($tempFilePath, 'w');

        if ($cleanedHandle === false) {
            Log::error('Failed to create temporary CSV file.');
            fclose($handle);
            return response()->json(['message' => 'Error processing the file.'], 500);
        }

        DB::beginTransaction();

        try {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ' . implode(', ', $header));
            fputcsv($cleanedHandle, $header);

            while (($data = fgetcsv($handle)) !== false) {
                Log::info('CSV Row: ' . implode(', ', $data));
                Log::info('CSV Row Column Count: ' . count($data));

                if (count($data) >= 4) {
                    $date = $data[1];

                    // Split date string and validate components (using '/' as delimiter)
                    $dateParts = explode('/', $date);

                    if (count($dateParts) !== 3) {
                        Log::warning('Date format invalid (Skipping row): ' . implode(', ', $data));
                        continue;
                    }

                    if (strlen($dateParts[0]) > 2 || strlen($dateParts[1]) > 2 || strlen($dateParts[2]) !== 4) {
                        Log::warning('Date format invalid (Skipping row): ' . implode(', ', $data));
                        continue;
                    }

                    if (!ctype_digit($dateParts[0]) || !ctype_digit($dateParts[1]) || !ctype_digit($dateParts[2])) {
                        Log::warning('Date format invalid (Skipping row): ' . implode(', ', $data));
                        continue;
                    }

                    // Attempt to create DateTime object (M/D/YYYY format)
                    try {
                        $dateObject = \DateTime::createFromFormat('n/j/Y', $date); // Use n/j/Y format
                        if (!$dateObject || $dateObject->format('n/j/Y') !== $date) {
                            Log::warning('Invalid date (Skipping row): ' . implode(', ', $data));
                            continue;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Date format invalid (Skipping row): ' . implode(', ', $data));
                        continue;
                    }

                    // Write valid row to cleaned CSV
                    fputcsv($cleanedHandle, $data);

                    ActivityData::create([
                        'date' => $dateObject->format('Y-m-d'), // store as Y-m-d
                        'steps' => $data[2],
                        'distance_km' => $data[3],
                        'active_minutes' => $data[4],
                    ]);
                } else {
                    Log::warning('Invalid CSV Row (Skipping): ' . implode(', ', $data));
                }
            }

            DB::commit();

            // Replace the original file with the cleaned file
            if (is_resource($handle)) {
                fclose($handle);
            }
            if (is_resource($cleanedHandle)) {
                fclose($cleanedHandle);
            }

            unlink($file->getPathname());
            rename($tempFilePath, $file->getPathname());

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing CSV data: ' . $e->getMessage());
            if (is_resource($handle)) {
                fclose($handle);
            }
            if (is_resource($cleanedHandle)) {
                fclose($cleanedHandle);
            }
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            return false;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            if (is_resource($cleanedHandle)) {
                fclose($cleanedHandle);
            }
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }
}
