<?php
// app/Services/AdminService.php
namespace App\Services;

use App\Models\Subject;
use App\Models\SubjectSchedule;
use App\Models\Passkey;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminService
{
    public function createSubject($data)
    {
        DB::beginTransaction();
        
        try {
            // Check if subject code exists
            if (Subject::where('code', $data['code'])->exists()) {
                return false;
            }

            // Check for duplicate schedules
            $uniqueSchedules = [];
            foreach ($data['schedules'] as $schedule) {
                $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
                if (!isset($uniqueSchedules[$key])) {
                    $uniqueSchedules[$key] = $schedule;
                }
            }
            $schedules = array_values($uniqueSchedules);

            // Create subject
            $subject = Subject::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'units' => $data['units'],
                'year_level' => $data['year_level'],
                'semester' => $data['semester'],
                'max_students' => $data['max_students'],
                'created_by' => auth()->guard('admin')->id(),
                'is_active' => 1
            ]);

            // Add prerequisites
            if (!empty($data['prerequisites'])) {
                $subject->prerequisites()->attach($data['prerequisites']);
            }

            // Add schedules
            foreach ($schedules as $schedule) {
                SubjectSchedule::create([
                    'subject_id' => $subject->id,
                    'Section' => $schedule['section'],
                    'day' => $schedule['day'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'room' => $schedule['room'] ?? null,
                    'Type' => $schedule['type']
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Subject creation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getPrerequisiteOptions()
    {
        return Subject::where('is_active', 1)
            ->select('id', 'code', 'name')
            ->orderBy('code')
            ->get();
    }

    public function getAllSubjectsWithSchedules()
    {
        return Subject::with(['schedules'])
            ->where('is_active', 1)
            ->orderBy('code')
            ->get()
            ->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'code' => $subject->code,
                    'name' => $subject->name,
                    'units' => $subject->units,
                    'year_level' => $subject->year_level,
                    'semester' => $subject->semester,
                    'schedules' => $subject->schedules
                ];
            });
    }

    // Add other methods from your original Admin class...
}