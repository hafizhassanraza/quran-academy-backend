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
use App\Models\Transaction;


class AccountController extends Controller
{
    
    public function index()
    {
        $transactions = Transaction::with('teacher:id,full_name,employee_id')
            ->get()
            ->map(function ($transaction) {
            $extra_info = [
                'teacher_name'    => $transaction->teacher->full_name ?? null,
                'teacher_emp_id'  => $transaction->teacher->employee_id ?? null,
            ];
            $transactionArr = $transaction->toArray();
            unset($transactionArr['teacher']);
            $transactionArr['extra_info'] = $extra_info;
            return $transactionArr;
            });

        return response()->json(['transactions' => $transactions]);

    }
    public function show($id)
    {
        $transaction = Transaction::with('teacher:id,full_name,employee_id')->findOrFail($id);
        $extra_info = [
            'teacher_name'    => $transaction->teacher->full_name ?? null,
            'teacher_emp_id'  => $transaction->teacher->employee_id ?? null,
        ];
        $transactionArr = $transaction->toArray();
        unset($transactionArr['teacher']);
        $transactionArr['extra_info'] = $extra_info;

        return response()->json(['transaction' => $transactionArr]);
    }

    public function validateTransaction(Request $request)
    {
        return Validator::make($request->all(), [
      
        'teacher_id' => 'required|exists:teachers,id',
        'amount'     => 'required|numeric|min:0',
        'type'       => 'required|string|max:50',
        'status'     => 'required|string|max:50',
        'reference'  => 'nullable|string|max:100',
        'other'      => 'nullable|string|max:255',
        ], [
            'teacher_id.exists' => 'The selected teacher does not exist.',
            'amount.min'        => 'The amount must be at least 0.'

        ]);
    }

   public function store(Request $request)
   {
       $validator = $this->validateTransaction($request);
       if ($validator->fails()) {
           throw new ValidationException($validator);
       }

       $transaction = Transaction::create($validator->validated());

       return response()->json(['transaction' => $transaction], 201);
   }

   public function update(Request $request, $id)
   {
       $transaction = Transaction::findOrFail($id);

       $validator = $this->validateTransaction($request);
       if ($validator->fails()) {
           throw new ValidationException($validator);
       }

       $transaction->update($validator->validated());

       return response()->json(['transaction' => $transaction]);
   }

   public function destroy($id)
   {
       $transaction = Transaction::findOrFail($id);
       $transaction->delete();

       return response()->json(['message' => 'Transaction deleted successfully']);
   }







}
