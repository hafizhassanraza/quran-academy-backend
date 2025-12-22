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
        $request->merge(['registration_no' => $this->getNextRegistrationNo()]);
        $student = Student::create($request->all());

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student
        ], 201);

    }

    public function show($id)
    {
        $student = Student::findOrFail($id)->load('enrollments', 'courses', 'teachers');
        return response()->json(['student' => $student]);
    }

    public function update(Request $request, $id)
    {
        
        $student = Student::findOrFail($id);



        // If status is changing, handle it
        if ($request->has('status') && $request->input('status') !== $student->status) {
            $oldStatus = $student->status;
            $newStatus = $request->input('status');

            if ($student->enrollments()->exists()) {
                $student->load('enrollments');
                foreach ($student->enrollments as $enrollment) {
                    $enrollment->status = $newStatus;
                    $enrollment->save();
                }
            }

            
        }

        $student->update($request->all());
        // Return the updated student
        $student->fresh();





        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student
        ]);
    }


    public function partialUpdate(Request $request, $id)
    {
        // Find student
        $student = Student::findOrFail($id);

        // Define validation rules but make them conditional with "sometimes"
        $rules = [
            'photo'             => 'sometimes|nullable|string|max:255',
            'full_name'         => 'sometimes|string|max:255',
            'father_name'       => 'sometimes|string|max:255',
            'gender'            => 'sometimes|in:male,female,other',
            'age'               => 'sometimes|integer|min:0|max:120',
            'email'             => 'sometimes|string|max:50',
            'phone'             => 'sometimes|string|max:20',
            'alternate_phone'   => 'sometimes|string|max:20',
            'address'           => 'sometimes|string|max:255',
            'city'              => 'sometimes|string|max:100',
            'country'           => 'sometimes|string|max:100',
            'enrollment_date'   => 'sometimes|date',
            //'temp_slots'        => 'sometimes|array|min:1',
            //'temp_slots.*'      => 'sometimes|string',
            'password'          => 'sometimes|string|min:6|max:255',
            'national_id'       => 'sometimes|nullable|string|max:50',
            'time_zone'         => 'sometimes|nullable|string|max:100',
            'other'             => 'sometimes|nullable|string|max:255',
            'status'            => 'sometimes|in:active,inactive,trail,completed,dropped',

        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Normalize age if provided (Postman might send as string)
        if ($request->has('age')) {
            $request->merge(['age' => (int) $request->input('age')]);
        }

        // Only update fields that are allowed/present
        $allowed = [
            'photo','full_name','father_name','gender','age','email','phone',
            'alternate_phone','address','city','country','enrollment_date',
            'temp_slots','password','national_id','time_zone','other','status'
        ];
        $data = $request->only($allowed);

        // Apply update
        $student->update($data);

        // update enrollment status | same as student status
        //if enrollment exists
        if ($student->enrollments()->exists()) {
            $student->enrollments()->update(['status' => $student->status]);
        }

        return response()->json([
            'message' => 'Student updated successfully ',
            'student' => $student->fresh()
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
        if (!$student || $request->password !== $student->password) return response()->json(['errors' => 'The provided credentials are incorrect.'], 422);

        
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
            //'registration_no'   => 'required|string|max:50|unique:students,registration_no',
            'photo'             => 'nullable|string|max:255',
            'full_name'         => 'required|string|max:255',
            'father_name'       => 'required|string|max:255',
            'gender'            => 'required|in:male,female,other',
            'age'               => 'nullable|integer|min:0|max:120',
            'email'             => 'nullable|string|max:50',
            'phone'             => 'nullable|string|max:20',
            'alternate_phone'   => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'city'              => 'required|string|max:100',
            'country'           => 'required|string|max:100',
            'enrollment_date'   => 'required|date',
            // 'temp_slots'        => 'required|array|min:1',
            // 'temp_slots.*'      => 'required|string',
            //'username'          => 'required|string|max:50|unique:students,username',
            'password'          => 'required|string|min:4|max:255',
            'national_id'       => 'nullable|string|max:50',
            'time_zone'         => 'nullable|string|max:100',
            'other'             => 'nullable|string|max:255',
            'status'            => 'nullable|in:active,inactive,trail,completed,dropped',


        ], [
            //'registration_no.required' => 'Registration number is required.',
            //'registration_no.unique' => 'Registration number must be unique.',
            'full_name.required' => 'Full name is required.',
            'father_name.required' => 'Father name is required.',
            'gender.required' => 'Gender is required.',
            
            'city.required' => 'City is required.',
            'country.required' => 'Country is required.',
            'enrollment_date.required' => 'Enrollment date is required.',
            //'username.required' => 'Username is required.',
            //'username.unique' => 'Username must be unique.',
            'password.required' => 'Password is required.',

            // 'temp_slots.required' => 'At least one slot is required.',
            // 'temp_slots.array' => 'The slots must be an array.',
            // 'temp_slots.min' => 'At least one slot is required.',
            // 'temp_slots.*.required' => 'Each slot is required.',
            // 'temp_slots.*.string' => 'Each slot must be a string.',
        ]);
    }

    public function getNextRegistrationNo()
    {
        $lastStudent = Student::orderBy('id', 'desc')->first();
        $nextRegNumber = 2001;
        if ($lastStudent && preg_match('/^QES(\d+)$/', $lastStudent->registration_no, $matches)) {
            $nextRegNumber = (int)$matches[1] + 1;
        }
        return 'QES' . $nextRegNumber;
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

