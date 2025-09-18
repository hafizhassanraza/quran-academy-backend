<?php

namespace App\Http\Controllers\Api\Student;

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

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::with(['student:id,full_name,registration_no', 'course:id,course_name,course_code', 'teacher:id,full_name,employee_id'])
            ->get()
            ->map(function ($enrollment) {
            $extra_info = [
                'student_name'    => $enrollment->student->full_name ?? null,
                'student_reg_num' => $enrollment->student->registration_no ?? null,
                'teacher_name'    => $enrollment->teacher->full_name ?? null,
                'teacher_emp_id'  => $enrollment->teacher->employee_id ?? null,
                'course_name'     => $enrollment->course->course_name ?? null,
                'course_code'     => $enrollment->course->course_code ?? null,
            ];
            $enrollmentArr = $enrollment->toArray();
            unset($enrollmentArr['student'], $enrollmentArr['teacher'], $enrollmentArr['course']);
            $enrollmentArr['extra_info'] = $extra_info;
            return $enrollmentArr;
            });

        return response()->json(['enrollments' => $enrollments]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateEnrollment($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        if ($this->isSlotConflict($request->teacher_id, $request->slots)) {
            return response()->json(['error' => 'The selected slots conflict with this teacher\'s existing schedule.'], 422);
        }
        $enrollment = Enrollment::create($request->all());

        return response()->json([
            'message' => 'Enrollment created successfully',
            'enrollment' => $enrollment
        ], 201);
    }

    public function show($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        return response()->json(['enrollment' => $enrollment]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateEnrollment($request, $id);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $enrollment = Enrollment::findOrFail($id);
        $enrollment->update($request->all());

        return response()->json([
            'message' => 'Enrollment updated successfully',
            'enrollment' => $enrollment
        ]);
    }


    /* protected function isSlotConflict($teacher_id, $newSlots)
    {
        $existingEnrollments = Enrollment::where('teacher_id', $teacher_id)->get();

        foreach ($existingEnrollments as $enrollment) {
            $existingSlots = $enrollment->slots;
            if (array_intersect($existingSlots, $newSlots)) {
                return true; // Conflict found
            }
        }
        return false; // No conflict
    } */



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




    protected function validateEnrollment(Request $request, $id = null)
    {
        $rules = [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:teachers,id',

            'enrollment_date' => 'required|date',
            'starting_date' => 'required|date',
            'slots' => 'required|array|min:1',
            'slots.*' => 'required|string',
            'year' => 'required|integer|min:2000|max:2100',
            'other' => 'nullable|string|max:255',
        ];

        if ($id) {
            // For update, ensure the combination of student_id and course_id is unique except for the current record
            $rules['student_id'] .= '|unique:enrollments,student_id,' . $id . ',id,course_id,' . $request->course_id;
            $rules['course_id'] .= '|unique:enrollments,course_id,' . $id . ',id,student_id,' . $request->student_id;
        } else {
            // For create, ensure the combination of student_id and course_id is unique
            $rules['student_id'] .= '|unique:enrollments,student_id,NULL,id,course_id,' . $request->course_id;
            $rules['course_id'] .= '|unique:enrollments,course_id,NULL,id,student_id,' . $request->student_id;
        }

        $messages = [
            'student_id.required' => 'The student ID is required.',
            'student_id.exists' => 'The selected student does not exist.',
            'student_id.unique' => 'The student is already enrolled in this course.',
            'course_id.required' => 'The course ID is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'course_id.unique' => 'The course already has this student enrolled.',
            'teacher_id.exists' => 'The selected teacher does not exist.',
            'teacher_id.required' => 'The teacher ID is required.',
            'enrollment_date.required' => 'The enrollment date is required.',
            'enrollment_date.date' => 'The enrollment date must be a valid date.',
            'starting_date.required' => 'The starting date is required.',
            'starting_date.date' => 'The starting date must be a valid date.',
            'slots.required' => 'At least one slot is required.',
            'slots.array' => 'The slots must be an array.',
            'slots.min' => 'At least one slot is required.',
            'slots.*.required' => 'Each slot is required.',
            'slots.*.string' => 'Each slot must be a string.',

            'year.required' => 'The year is required.',
            'year.integer' => 'The year must be an integer.',
            'year.min' => 'The year must be at least 2000.',
            'year.max' => 'The year may not be greater than 2100.',
            'other.max' => 'The other field may not be greater than 255 characters.',
            'other.string' => 'The other field must be a string.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }



    /*
    request sample 
    {
      "student_id": 1,
      "course_id": 1,
      "teacher_id": 1,
      "enrollment_date": "2023-01-01",
      "grade": "A",
      "semester": "Spring",
      "year": 2023,
      "status": "active",
      "other": "Additional notes"
    }
    */


    /*
    Postman request sample (JSON):

    {
        "student_id": 1,
        "course_id": 1,
        "teacher_id": 1,
        "enrollment_date": "2023-01-01",
        "startng_date": "2023-01-10",
        "slots": [
            "MON1",
            "WED5",
            "FRI10"
        ],
        "grade": "A",
        "semester": "Spring",
        "year": 2023,
        "other": "Additional notes"
    }
    */


}
