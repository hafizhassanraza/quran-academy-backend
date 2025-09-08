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


class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return response()->json(['courses' => $courses]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateCourse($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $course = Course::create($request->all());

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }

    public function show($id)
    {
        $course = Course::findOrFail($id);
        return response()->json(['course' => $course]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateCourse($request, $id);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $course = Course::findOrFail($id);
        $course->update($request->all());

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course
        ]);
    }

    protected function validateCourse(Request $request, $id = null)
    {
        $rules = [
            'course_code' => 'required|string|unique:courses,course_code' . ($id ? ",$id" : ''),
            'course_name' => 'required|string',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:0',
            'department' => 'required|string',
            'instructor' => 'nullable|string',
            'schedule' => 'nullable|string',
            'location' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'enrollment_count' => 'nullable|integer|min:0',
            'prerequisites' => 'nullable|string',
            'syllabus' => 'nullable|string',
            'semester' => 'nullable|string',
            'year' => 'nullable|integer|min:2000',
            'status' => 'nullable|string',
            'other' => 'nullable|string',
        ];
        $messages = [
            'course_code.required' => 'The course code is required.',
            'course_code.string' => 'The course code must be a string.',
            'course_code.unique' => 'The course code must be unique.',
            'course_name.required' => 'The course name is required.',
            'course_name.string' => 'The course name must be a string.',
            'description.string' => 'The description must be a string.',
            'credits.required' => 'The credits are required.',
            'credits.integer' => 'The credits must be an integer.',
            'credits.min' => 'The credits must be at least 0.',
            'department.required' => 'The department is required.',
            'department.string' => 'The department must be a string.',
            'instructor.string' => 'The instructor must be a string.',
            'schedule.string' => 'The schedule must be a string.',
            'location.string' => 'The location must be a string.',
            'capacity.integer' => 'The capacity must be an integer.',
            'capacity.min' => 'The capacity must be at least 0.',
            'enrollment_count.integer' => 'The enrollment count must be an integer.',
            'enrollment_count.min' => 'The enrollment count must be at least 0.',
            'prerequisites.string' => 'The prerequisites must be a string.',
            'syllabus.string' => 'The syllabus must be a string.',
            'semester.string' => 'The semester must be a string.',
            'year.integer' => 'The year must be an integer.',
            'year.min' => 'The year must be at least 2000.',
            'status.string' => 'The status must be a string.',
            'other.string' => 'The other field must be a string.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }


    /*
    {
        "course_code": "CS101",
        "course_name": "Introduction to Computer Science",
        "description": "Basic concepts of computer science.",
        "credits": 3,
        "department": "Computer Science",
        "instructor": "Dr. John Doe",
        "schedule": "Mon/Wed/Fri 10:00-11:00",
        "location": "Room 101",
        "capacity": 30,
        "enrollment_count": 0,
        "prerequisites": "None",
        "syllabus": "Overview of computing, programming basics.",
        "semester": "Fall",
        "year": 2024,
        "status": "active",
        "other": ""
    }
    */




}
