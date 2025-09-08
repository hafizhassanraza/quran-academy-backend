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

    protected function validateSlot(Request $request, $id = null)
    {
        $rules = [
            'teacher_id'      => 'required|exists:teachers,id',
            'student_id'      => 'required|exists:students,id',
            'course_id'       => 'required|exists:courses,id',
            'chapter_id'      => 'nullable|exists:chapters,id',
            'slot_number'     => 'required|integer|min:1',
            'slot_date'       => 'required|date',
            'reschedule_date' => 'nullable|date|after_or_equal:slot_date',
            'start_time'      => 'required|date_format:H:i',
            'end_time'        => 'required|date_format:H:i|after:start_time',
            'other'           => 'nullable|string|max:255',
        ];
        $messages = [
            'teacher_id.required'      => 'The teacher is required.',
            'teacher_id.exists'        => 'The selected teacher does not exist.',
            'student_id.required'      => 'The student is required.',
            'student_id.exists'        => 'The selected student does not exist.',
            'course_id.required'       => 'The course is required.',
            'course_id.exists'         => 'The selected course does not exist.',
            'chapter_id.exists'        => 'The selected chapter does not exist.',
            'slot_number.required'     => 'The slot number is required.',
            'slot_number.integer'      => 'The slot number must be an integer.',
            'slot_number.min'          => 'The slot number must be at least 1.',
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
