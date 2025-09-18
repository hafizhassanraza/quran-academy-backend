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


class AccountController extends Controller
{
    
    public function index()
    {
        
        $teachers = Teacher::get();

        $per_slot_pay = 500;

        foreach ($teachers as $teacher) {
            // Calculate total complete slots (assuming completeSlots is a relationship)
            $total_complete_slots = method_exists($teacher, 'completeSlots') && is_object($teacher->completeSlots())
            ? $teacher->completeSlots()->count()
            : 0;

            $teacher->stats = [
                'total_complete_slots' => $total_complete_slots,
                'per_slot_pay' => $per_slot_pay,
                'total_pay' => $total_complete_slots * $per_slot_pay,
                'available_balance' => $teacher->balance ?? 0,
            ];
        }



        return response()->json(['teachers' => $teachers]);
    }


}
