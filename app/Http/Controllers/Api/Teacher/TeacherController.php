<?php

namespace App\Http\Controllers\Api\Teacher;

//Facades
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


//Models
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Slot;


class TeacherController extends Controller
{
    

    public function index()
    {
        $teachers = Teacher::all();
        return response()->json(['teachers' => $teachers]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateTeacher($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $request->merge(['employee_id' => $this->getNextEmployeeID()]);
        $teacher = Teacher::create($request->all());

        return response()->json([
            'message' => 'Teacher created successfully',
            'teacher' => $teacher
        ], 201);
    }

    public function show($id)
    {
        $teacher = Teacher::findOrFail($id)->load( 'courses', 'studentEnrollments');
        return response()->json(['teacher' => $teacher]);
    }

    public function update(Request $request, $id)
    {
        //$validator = $this->validateTeacher($request, $id);
        //if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $teacher = Teacher::findOrFail($id);
        $teacher->update($request->all());

        return response()->json([
            'message' => 'Teacher updated successfully',
            'teacher' => $teacher
        ]);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();
        return response()->json([
            'message' => 'Teacher deleted successfully'
        ]);
    }


    public function transactions(Request $request)
    {
        $teacher = Teacher::findOrFail($request->teacher_id);
        $transactions = $teacher->transactions()->get();
        return response()->json(['transactions' => $transactions]);
    }



    public function teacherLogin(Request $request)
    {
        $validator = $this->validateTeacherLogin($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $teacher = Teacher::where('employee_id', $request->employee_id)->first();
        if (!$teacher || $request->password !== $teacher->password) return response()->json(['errors' => 'The provided credentials are incorrect.'], 422);

        return response()->json([
            'teacher' => $teacher,
        ]);
    }


    protected function validateTeacherLogin(Request $request)
    {
        $rules = [
            'employee_id' => 'required|string',
            'password' => 'required|string',
        ];
        $messages = [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.string' => 'Employee ID must be a string.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
        ];
        return Validator::make($request->all(), $rules, $messages);
    }

    protected function validateTeacher(Request $request)
    {
        
        return Validator::make($request->all(), [
            'photo'            => 'nullable|string|max:255',
            'full_name'        => 'required|string|max:255',
            'father_name'      => 'required|string|max:255',
            'gender'           => 'required|in:male,female,other',
            'age'              => 'required|integer|min:18|max:120',
            'email'             => 'required|unique:teachers,email|string|max:255',
            'phone'            => 'required|string|max:20',
            'alternate_phone'  => 'nullable|string|max:20',
            'address'          => 'required|string|max:255',
            'city'             => 'required|string|max:100',
            'country'          => 'required|string|max:100',
            'hire_date'        => 'required|date',
            'password'          => 'required|string|min:6|max:255',
            'national_id'      => 'nullable|string|max:50',
            'time_zone'        => 'nullable|string|max:100',
            'other'            => 'nullable|string|max:255',
            'status'           => 'nullable|in:active,inactive,dropped',
        ], [

            'full_name.required' => 'Full name is required.',
            'father_name.required' => 'Father name is required.',
            'gender.required' => 'Gender is required.',
            'age.required' => 'Age is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'Email must be unique.',
            'phone.required' => 'Phone is required.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'country.required' => 'Country is required.',
            'hire_date.required' => 'Hire date is required.',
            'password.required' => 'Password is required.',
        ]);
    }
    

    public function getNextEmployeeID()
    {
        $lastTeacher = Teacher::orderBy('id', 'desc')->first();
        $nextEmpNumber = 1001;
        if ($lastTeacher && preg_match('/^QET(\d+)$/', $lastTeacher->employee_id, $matches)) {
            $nextEmpNumber = (int)$matches[1] + 1;
        }
        return 'QET' . $nextEmpNumber;
    }
    
    //create JSON request samples for all methods
    /*
    {
        "employee_id": "EMP12345",
        "photo": "http://example.com/photo.jpg",
        "full_name": "Jane Smith",
        "father_name": "John Smith",
        "gender": "female",
        "age": 30,
        "email": "jane.smith@example.com",
        "phone": "123-456-7890",
        "alternate_phone": "098-765-4321",
        "address": "456 Elm St",
        "city": "Othertown",
        "country": "USA",
        "hire_date": "2023-01-01",
        "username": "janesmith",
        "password": "password123",
        "last_login": "2023-01-01",
        "national_id": "NID123456",
        "time_zone": "UTC",
        "other": "Some other information"
    }*/
}
