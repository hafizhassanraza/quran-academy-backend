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



class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return response()->json(['students' => $students]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateStudent($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $student = Student::create($request->all());

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student
        ], 201);

    }

    public function show($id)
    {
        $student = Student::findOrFail($id)->load('enrollments', 'slots', 'courses', 'teachers');
        return response()->json(['student' => $student]);
    }

    public function update(Request $request, $id)
    {

        $validator = $this->validateStudent($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $student = Student::findOrFail($id);
        $student->update($request->all());

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student
        ]);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return response()->json([
            'message' => 'Student deleted successfully'
        ]);
        
    }





    public function studentLogin(Request $request)
    {
        $validator = $this->validateStudentLogin($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);


        $student = Student::where('registration_no', $request->registration_no)->first();

        if (!$student || $request->password !== $student->password) {
            throw ValidationException::withMessages([
            'registration_no' => ['The provided credentials are incorrect.'],
            ]);
        }
        return response()->json([
            'student' => $student,
        ]);
    }




    protected function validateStudentLogin(Request $request)
    {
        $rules = [
            'registration_no' => 'required|string',
            'password' => 'required|string',
        ];
        $messages = [
            'registration_no.required' => 'Registration number is required.',
            'registration_no.string' => 'Registration number must be a string.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
        ];
        return Validator::make($request->all(), $rules, $messages);
    }









    protected function validateStudent(Request $request)
    {
        return Validator::make($request->all(), [
            'registration_no'   => 'required|string|max:50|unique:students,registration_no',
            'photo'             => 'nullable|string|max:255',
            'full_name'         => 'required|string|max:255',
            'father_name'       => 'required|string|max:255',
            'gender'            => 'required|in:male,female,other',
            'age'               => 'required|integer|min:1|max:120',
            'email'             => 'required|email|unique:students,email',
            'phone'             => 'required|string|max:20',
            'alternate_phone'   => 'nullable|string|max:20',
            'address'           => 'required|string|max:255',
            'city'              => 'required|string|max:100',
            'country'           => 'required|string|max:100',
            'enrollment_date'   => 'required|date',
            'username'          => 'required|string|max:50|unique:students,username',
            'password'          => 'required|string|min:6|max:255',
            'last_login'        => 'nullable|date',
            'national_id'       => 'nullable|string|max:50',
            'time_zone'         => 'nullable|string|max:100',
            'other'             => 'nullable|string|max:255',
        ], [
            'registration_no.required' => 'Registration number is required.',
            'registration_no.unique' => 'Registration number must be unique.',
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
            'enrollment_date.required' => 'Enrollment date is required.',
            'username.required' => 'Username is required.',
            'username.unique' => 'Username must be unique.',
            'password.required' => 'Password is required.',
        ]);
    }




    //create JSON request samples for all methods
    /*
    {
        "registration_no": "REG12345",
        "photo": "path/to/photo.jpg",
        "full_name": "John Doe",
        "father_name": "Richard Roe",
        "gender": "male",
        "age": 20,
        "email": "john.doe@example.com",
        "phone": "123-456-7890",
        "alternate_phone": "098-765-4321",
        "address": "123 Main St",
        "city": "Anytown",
        "country": "USA",
        "enrollment_date": "2023-01-01",
        "username": "johndoe",
        "password": "password123",
        "last_login": "2023-01-01",
        "national_id": "NID123456",
        "time_zone": "UTC",
        "other": "Some other information"
    }*/
        
}

