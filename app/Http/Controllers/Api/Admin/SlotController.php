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

    public function updateSlot(Request $request)
    {
        // Validate the request including the status field
        $validator = $this->validateSlotUpdate($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        

        if ($request->input('status') === 'rescheduled') {
            if($this->isSlotConflict($request->teacher_id, [$request->slot_code])) {
                return response()->json(['error' => 'The rescheduled date conflicts with this teacher\'s existing schedule.'], 422);
            }
        }



        $slot = Slot::findOrFail($request->id);
        $slot->update($request->all());
        $slot->refresh();

        // If the slot status is updated to 'completed', update the teacher's balance
        if ($request->input('status') === 'completed') {
            $enrollment = Enrollment::find($slot->enrollment_id);
            if ($enrollment) {
                $this->updateTeacherBalance($enrollment->teacher_id, '50'); // Assuming each completed slot adds 50 to the teacher's balance    
            }
        }

        return response()->json([
            'message' => 'Slot updated successfully',
            'slot' => $slot
        ]);
    }





    // ********************* ADMIN SLOTS *************************

    //by-date // comleted + leaved (bydefault without reschedule request) + started + active 
    public function registeredSlots(Request $request)
    {
        $slots = Slot::status($request->status)->forDate($request->date)->get();
        $slots->load('enrollment.student', 'enrollment.course', 'enrollment.teacher');

        return response()->json([
            'slots' => $slots
        ]);
    }

    // missed + current slot (not active) +   upcoming
    public function unregisteredSlots(Request $request)
    {
        $enrollments = Enrollment::with(['student', 'course'])->get();

        $todayRegisteredSlots = Slot::forDate($request->date)->get();
        $slotDay = $request->slot_day;
        $todayNotRegisteredSlots = [];
        foreach ($enrollments as $enrollment) {
            $allSlotCodes = $enrollment->slots ?? [];
            foreach ($allSlotCodes as $slotCode) {
                if (str_starts_with($slotCode, $slotDay) && !$todayRegisteredSlots->contains('slot_code', $slotCode)) {
                    $todayNotRegisteredSlots[] = [
                        'enrollment_id' => $enrollment->id,
                        'chapter_id' => null,
                        'slot_code' => $slotCode,
                        'slot_date' => null,
                        'reschedule_date' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'status' => 'not registered',
                        'reschedule_reason' => null,
                        'reschedule' => null,
                        'other' => 'No record found, assumed not active',
                        'student' => $enrollment->student,
                        'course' => $enrollment->course,
                    ];
                }
            }
        }
        return response()->json(['slots' => $todayNotRegisteredSlots]);

        
    }

    // Req. Reschedule
    public function allRescheduleReqSlots(Request $request)
    {
        $slots = Slot::status('leaved')->reschedule($request->reschedule)->get();
        $slots->load('enrollment.student', 'enrollment.course', 'enrollment.teacher');

        return response()->json([
            'slots' => $slots
        ]);
    }










































    // ********************* TEACHER SLOTS *************************


    public function todayTeacherSlots(Request $request)
    {
        // Get all enrollments for the given teacher with slots for today
        $enrollments = Enrollment::with(['student', 'course'])
            ->where('teacher_id', $request->teacher_id)
            ->get();

        $today = $request->slot_day;
        $date = $request->date; // 'Y-m-d'


        $slots = [];

        foreach ($enrollments as $enrollment) {
            foreach (($enrollment->slots ?? []) as $slotCode) // enrollments contain array of slot codes
            {

                //$existingSlots = $enrollment->slotsRegistered()->forDate($date)->pluck('slot_code')->toArray();

                if (str_starts_with($slotCode, $today)) {

                    // against this enrollment -> all (completed, start, active, reschedule) slots registered -> for date -> slot code - > first
                    $regularSlot = $enrollment->slotsRegistered()->forDate($date)->slotCode($slotCode)->first();
                    // against this enrollment -> all (completed, start, active, reschedule) slots registered -> for status = rescheduled -> slot code - > first
                    $rescheduledSlots = $enrollment->slotsRegistered()->rescheduled()->slotCode($slotCode)->first();



                    if($regularSlot ) {
                        $regularSlot->type = 'regular';
                        $regularSlot->student = $enrollment->student;
                        $regularSlot->course = $enrollment->course;
                        $slots[] = $regularSlot->toArray();
                    }
                    elseif($rescheduledSlots) {
                        $rescheduledSlots->type = 'rescheduled';
                        $rescheduledSlots->student = $enrollment->student;
                        $rescheduledSlots->course = $enrollment->course;
                        $slots[] = $rescheduledSlots->toArray();
                    } 
                    else {
                        $slots[] = [
                            'enrollment_id' => $enrollment->id,
                            'chapter_id' => null,
                            'slot_code' => $slotCode,
                            'slot_date' => null,
                            'reschedule_date' => null,
                            'start_time' => null,
                            'end_time' => null,
                            'type' => "regular",
                            'status' => "not registered",
                            'reschedule_reason' => null,
                            'reschedule' => null,
                            'other' => null,
                            'student' => $enrollment->student,
                            'course' => $enrollment->course,
                        ];
                    }
                    

                    
                }
            }
        }

        return response()->json(['slots' => $slots]);


    }


    public function slotOfTeacher(Request $request)
    {

        $enrollments = Enrollment::with(['student', 'course'])
            ->where('teacher_id', $request->teacher_id)
            ->get();

        $slots = [];

        foreach ($enrollments as $enrollment) {

            $tempSlots = $enrollment->slotsRegistered()->status($request->status)->get();
            if($tempSlots->isNotEmpty()) {

                $tempSlots->load('enrollment.student', 'enrollment.course');
                $tempSlots->student = $enrollment->student;
                $tempSlots->course = $enrollment->course;
                $slots[] = $tempLeavedSlots->toArray();
            }
        }

        return response()->json(['slots' => $slots]);

    }



    public function getAllCompletedSlotsOfTheDay(Request $request)
    {
        $enrollments = Enrollment::with(['student', 'course'])->get();
        $today = $request->slot_day;
        $date = $request->date; // 'Y-m-d'
        $slots = [];

        foreach ($enrollments as $enrollment) {

            foreach (($enrollment->slots ?? []) as $slotCode) // enrollments contain array of slot codes
            {

                //$existingSlots = $enrollment->slotsRegistered()->forDate($date)->pluck('slot_code')->toArray();

                if (str_starts_with($slotCode, $today)) {

                    // against this enrollment -> all (completed, start, active, reschedule) slots registered -> for date -> slot code - > first
                    $completedSlot = $enrollment->slotsRegistered()->forDate($date)->status('completed')->first();



                    if($completedSlot) {
                        $completedSlot->student = $enrollment->student;
                        $completedSlot->course = $enrollment->course;
                        $slots[] = $completedSlot->toArray();
                    }
                    else {
                        $slots[] = [
                            'enrollment_id' => $enrollment->id,
                            'chapter_id' => null,
                            'slot_code' => $slotCode,
                            'slot_date' => null,
                            'reschedule_date' => null,
                            'start_time' => null,
                            'end_time' => null,
                            'status' => "Missed",
                            'reschedule_reason' => null,
                            'reschedule' => null,
                            'other' => null,
                            'student' => $enrollment->student,
                            'course' => $enrollment->course,
                        ];
                    }
                }
            }
            
        }


        return response()->json(['slots' => $slots]);



    }







































    public function getSlots(Request $request)
    {

        $type = $request->input('type');
        $slotCode = $request->input('slot_code');

        $query = Slot::query();

        if ($type === 'previous') {
            $query->where('slot_code', $slotCode)
                ->whereIn('status', ['completed', 'missed', 'leave']);
        } elseif ($type === 'current') {
            $query->where('slot_code', $slotCode);
        } elseif ($type === 'upcoming') {
            $query->where('slot_code', $slotCode)
                ->whereNotIn('status', ['completed', 'missed', 'start', 'active']);
        } elseif ($type === 'reschedule') {
            $query->where('slot_code', $slotCode)
                ->where('status', 'rescheduled');
        }

        $slots = $query->get();

        return response()->json(['slots' => $slots]);

    }



    /**
     * Get slots by type: previous, current, upcoming.
     * 
     * @param string $type ('previous', 'current', 'upcoming')
     * @param string $slot_day
     * @param int $slot_number
     * @param string $date
     * @return array
     */
    protected function getSlotsByType($type, $slot_day, $slot_number, $date)
    {
        $enrollments = Enrollment::all();
        $resultSlots = [];
        $slot_code = $slot_day . $slot_number;

        foreach ($enrollments as $enrollment) {
            $allSlotCodes = $enrollment->slots ?? [];

            if ($type === 'previous') {
                $completedSlots = $enrollment->slotsRegistered()->status('completed')->forDate($date)->get();
                $leavedSlots = $enrollment->slotsRegistered()->status('leaved')->forDate($date)->get();

                $completedSlotCodes = $completedSlots->pluck('slot_code')->toArray();
                $leavedSlotCodes = $leavedSlots->pluck('slot_code')->toArray();

                $missedSlots = [];
                foreach ($allSlotCodes as $code) {
                    if (strpos($code, $slot_day) === 0) {
                        $codeNumber = (int)substr($code, strlen($slot_day));
                        if ($codeNumber < $slot_number &&
                            !in_array($code, $completedSlotCodes) &&
                            !in_array($code, $leavedSlotCodes)) {
                            $missedSlots[] = [
                                'enrollment_id' => $enrollment->id,
                                'chapter_id' => null,
                                'slot_code' => $code,
                                'slot_date' => null,
                                'reschedule_date' => null,
                                'start_time' => null,
                                'end_time' => null,
                                'status' => 'missed',
                                'reschedule_reason' => null,
                                'reschedule' => null,
                                'other' => 'No record found, assumed missed'
                            ];
                        }
                    }
                }

                $resultSlots = array_merge(
                    $resultSlots,
                    $completedSlots->toArray(),
                    $leavedSlots->toArray(),
                    $missedSlots
                );
            }
            elseif ($type === 'current') {
                $startedSlots = $enrollment->slotsRegistered()->status('started')->forDate($date)->get();
                $activeSlots = $enrollment->slotsRegistered()->status('active')->forDate($date)->get();

                $startedSlotCodes = $startedSlots->pluck('slot_code')->toArray();
                $activeSlotCodes = $activeSlots->pluck('slot_code')->toArray();

                $notActiveSlots = [];
                if (in_array($slot_code, $allSlotCodes) &&
                    !in_array($slot_code, $startedSlotCodes) &&
                    !in_array($slot_code, $activeSlotCodes)) {
                    $notActiveSlots[] = [
                        'enrollment_id' => $enrollment->id,
                        'chapter_id' => null,
                        'slot_code' => $slot_code,
                        'slot_date' => null,
                        'reschedule_date' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'status' => 'not active',
                        'reschedule_reason' => null,
                        'reschedule' => null,
                        'other' => 'No record found, assumed not active'
                    ];
                }

                $resultSlots = array_merge(
                    $resultSlots,
                    $startedSlots->toArray(),
                    $activeSlots->toArray(),
                    $notActiveSlots
                );
            }
            elseif ($type === 'upcoming') {
                for ($next_slot_number = $slot_number + 1; $next_slot_number <= 48; $next_slot_number++) {
                    $next_slot_code = $slot_day . $next_slot_number;
                    if (in_array($next_slot_code, $allSlotCodes)) {
                        $resultSlots[] = [
                            'enrollment_id' => $enrollment->id,
                            'chapter_id' => null,
                            'slot_code' => $next_slot_code,
                            'slot_date' => null,
                            'reschedule_date' => null,
                            'start_time' => null,
                            'end_time' => null,
                            'status' => 'not active',
                            'reschedule_reason' => null,
                            'reschedule' => null,
                            'other' => 'No record found, assumed not active'
                        ];
                    }
                }
            }
        }

        return $resultSlots;
    }





    //***************************  Utilities *******************************//


    protected function updateTeacherBalance($teacher_id, $amount) 
    {
        $teacher = Teacher::find($teacher_id);
        if ($teacher) {
            $teacher->balance += $amount;
            $teacher->save();
        }
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



    /* protected function validateSlot(Request $request, $id = null)
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

            $notActiveSlots = [];

            if (in_array($slot_code, $enrollment->slots ?? [])) 
            {
                $notActiveSlots[] = [
                    'enrollment_id' => $enrollment->id,
                    'chapter_id' => null,
                    'slot_code' => $slot_code,
                    'slot_date' => null,
                    'reschedule_date' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'status' => 'not active',
                    'reschedule_reason' => null,
                    'reschedule' => null,
                    'other' => 'No record found, assumed not active'
                ];
            }

            $upcomingSlots = array_merge(
                $upcomingSlots,
                $startedSlots->toArray(),
                $activeSlots->toArray(),
                $notActiveSlots
            );

        }

        return $upcomingSlots;
    } */


    



/*     protected function isSlotConflict($teacher_id, $newSlots)
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
 */



    protected function validateSlotUpdate(Request $request)
    {
        $rules = [
            'id'                => 'required|exists:slots,id',
            'chapter_id'        => 'nullable|exists:chapters,id',
            'reschedule_date'   => 'nullable|date|after_or_equal:slot_date',
            'reschedule'          => 'nullable|string',
            'slot_code'         => 'nullable|string|max:10',
            'reschedule_reason'   => 'nullable|string',
            'active_time'        => 'nullable|date_format:H:i',
            'start_time'        => 'nullable|date_format:H:i',
            'end_time'          => 'nullable|date_format:H:i|after:start_time',
            'other'             => 'nullable|string|max:255',
            'status'            => 'in:scheduled,active,started,completed,missed,rescheduled,leaved',
        ];
        $messages = [
            'id.required'              => 'The slot ID is required.',
            'id.exists'                => 'The specified slot does not exist.',
            'chapter_id.exists'        => 'The selected chapter does not exist.',
            'reschedule_date.date'     => 'The reschedule date must be a valid date.',
            'reschedule_date.after_or_equal' => 'The reschedule date must be after or equal to the slot date.',
            'slot_code.required'       => 'The slot code is required.',
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

    protected function validateSlot(Request $request, $id = null)
    {
        $rules = [
            'enrollment_id'   => 'required|exists:enrollments,id',
            'teacher_id'      => 'required|exists:teachers,id',
            'chapter_id'      => 'nullable|exists:chapters,id',
            'slot_code'       => 'required|string|max:10',
            'slot_date'       => 'required|date',
            'reschedule'          => 'nullable|string',
            'reschedule_reason'   => 'nullable|string',
            'reschedule_date' => 'nullable|date|after_or_equal:slot_date',
            'active_time'        => 'nullable|date_format:H:i',
            'start_time'      => 'nullable|date_format:H:i',
            'end_time'        => 'nullable|date_format:H:i|after:start_time',
            'other'           => 'nullable|string|max:255',
            'status'          => 'in:scheduled,active,started,completed,missed,rescheduled,leaved',
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
