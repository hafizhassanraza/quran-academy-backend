<?php

namespace App\Http\Controllers\Api\Admin;

//Facades
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

//Models
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Slot;


class SlotController extends Controller
{
    public function index()
    {
        $slots = Slot::all();
        return response()->json(['slots' => $slots]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateSlot($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);


        if ($request->input('status') === 'rescheduled') {
            if($this->isSlotConflict($request->teacher_id, [$request->slot_code])) {
                return response()->json(['error' => 'The rescheduled date conflicts with this teacher\'s existing schedule.'], 422);
            }
        }


        $slot = Slot::create($request->all());

        return response()->json([
            'message' => 'Slot created successfully',
            'slot' => $slot
        ], 201);
    }

    public function show($id)
    {
        $slot = Slot::findOrFail($id);
        return response()->json(['slot' => $slot]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateSlot($request, $id);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $slot = Slot::findOrFail($id);
        $slot->update($request->all());

        return response()->json([
            'message' => 'Slot updated successfully',
            'slot' => $slot
        ]);
    }




    protected function isSlotConflict($teacher_id, $newSlots)
    {

        $existingEnrollments = Enrollment::where('teacher_id', $teacher_id)->get();
        $rescheduledSlots = Slot::rescheduled()->slotOfTeacher($teacher_id)->pluck('slot_code')->toArray();

        $allExistingSlots = [];
        foreach ($existingEnrollments as $enrollment) {
            $existingSlots = $enrollment->slots ?? [];
            if (is_array($existingSlots)) {
                $allExistingSlots = array_merge($allExistingSlots, $existingSlots);
            }
        }
        $allExistingSlots = array_merge($allExistingSlots, $rescheduledSlots);

        if (array_intersect($allExistingSlots, $newSlots)) {
            return true; // Conflict found
        }

      
        foreach ($existingEnrollments as $enrollment) {
            $existingSlots = $enrollment->slots;
            if (array_intersect($existingSlots, $newSlots)) {
                return true; // Conflict found
            }
        }
        return false; // No conflict
    }



    protected function validateSlot(Request $request, $id = null)
    {
        $rules = [
            'enrollment_id'   => 'required|exists:enrollments,id',
            'teacher_id'      => 'required|exists:teachers,id',
            'chapter_id'      => 'nullable|exists:chapters,id',
            'slot_code'       => 'required|string|max:10',
            'slot_date'       => 'required|date',
            'reschedule_date' => 'nullable|date|after_or_equal:slot_date',
            'start_time'      => 'nullable|date_format:H:i',
            'end_time'        => 'nullable|date_format:H:i|after:start_time',
            'other'           => 'nullable|string|max:255',
            'status'          => 'in:scheduled,started,completed,missed,rescheduled',
        ];
        $messages = [
            'enrollment_id.required'   => 'The enrollment is required.',
            'enrollment_id.exists'     => 'The selected enrollment does not exist.',
            'teacher_id.exists'        => 'The selected teacher does not exist.',
            'chapter_id.required'      => 'The chapter is required.',
            'chapter_id.exists'        => 'The selected chapter does not exist.',
            'slot_code.required'     => 'The slot code is required.',
            'slot_date.required'       => 'The slot date is required.',
            'slot_date.date'           => 'The slot date must be a valid date.',
            'reschedule_date.date'     => 'The reschedule date must be a valid date.',
            'reschedule_date.after_or_equal' => 'The reschedule date must be after or equal to the slot date.',
            'start_time.required'      => 'The start time is required.',
            'start_time.date_format'   => 'The start time must be in the format H:i.',
            'end_time.required'        => 'The end time is required.',
            'end_time.date_format'     => 'The end time must be in the format H:i.',
            'end_time.after'           => 'The end time must be after the start time.',
            'other.string'             => 'The other field must be a string.',
            'other.max'                => 'The other field may not be greater than 255 characters.',
            'status.required'          => 'The status is required.',
            'status.in'                => 'The selected status is invalid. Allowed values: scheduled, started, completed, missed, rescheduled.',
        ];

        return Validator::make($request->all(), $rules, $messages);

        
    }


    /* 

    /*
        Sample JSON request for creating a Slot (for Postman):

        {
            "teacher_id": 1,
            "student_id": 1,
            "course_id": 1,
            "chapter_id": 1,
            "slot_number": 1,
            "slot_date": "2024-06-10",
            "reschedule_date": "2024-06-12",
            "start_time": "09:00",
            "end_time": "10:00",
            "other": "Additional notes here"
        }

            
    */
    
    




}
