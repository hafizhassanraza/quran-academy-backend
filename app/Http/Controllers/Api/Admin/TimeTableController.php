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

class TimeTableController extends Controller
{
    


       /*  $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        $totalClassesPerDay = 48;
        $slots = [];

        // For each slot number (1-48)
        for ($classNum = 1; $classNum <= $totalClassesPerDay; $classNum++) {
            $dayClasses = [];
            $hasClass = false;

            foreach ($days as $day) {
                $slotCode = $day . $classNum;

                // Find the first enrollment for this slot
                $enrollment = $enrollments->first(function ($enrollment) use ($slotCode) {
                    return isset($enrollment->slots) && is_array($enrollment->slots) && in_array($slotCode, $enrollment->slots);
                });

                if ($enrollment) {
                    $student = $enrollment->student;
                    $course = $enrollment->course;
                    $teacher = $enrollment->teacher;

                    $class = [
                        'student_id'         => $enrollment->student_id ?? null,
                        'course_id'          => $enrollment->course_id ?? null,
                        'teacher_id'         => $enrollment->teacher_id ?? null,
                        'enrollment_date'    => $enrollment->enrollment_date ?? null,
                        'starting_date'      => $enrollment->starting_date ?? null,
                        'ending_date'        => $enrollment->ending_date ?? null,
                        'registration_no'    => $student->registration_no ?? null,
                        'student_name'       => $student->full_name ?? null,
                        'student_father'     => $student->father_name ?? null,
                        'student_country'    => $student->country ?? null,
                        'student_time_zone'  => $student->time_zone ?? null,
                        'course_code'        => $course->course_code ?? null,
                        'course_name'        => $course->course_name ?? null,
                        'employee_id'        => $teacher->employee_id ?? null,
                        'teacher_name'       => $teacher->full_name ?? null,
                        'teacher_father'     => $teacher->father_name ?? null,
                        'teacher_time_zone'  => $teacher->time_zone ?? null,
                        'slot_code'          => $slotCode,
                    ];
                    $hasClass = true;
                } else {
                    $class = null;
                }

                $dayClasses[] = [
                    'enrollment_id' => $enrollment->id ?? null,
                    'slot_id' => null,
                    'day' => $day,
                    'slot_code' => $slotCode,
                    'class' => $class,
                ];
            }

            // Only add this slot if at least one class exists in any day
            if ($hasClass) {
                $slots[] = [
                    'slot_number' => $classNum,
                    'days' => $dayClasses,
                ];
            }
        }

        return response()->json($slots); */



    public function getTimeTableByTeacher($teacherId)
    {
        // Get all enrollments for the given teacher
        $enrollments = Enrollment::with(['student', 'course', 'teacher'])
            ->where('teacher_id', $teacherId)
            ->get();

        // Eager load the rescheduleSlot relation for these enrollments
        
        $enrollments->load('rescheduledSlot');

        $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        $totalClassesPerDay = 48;
        $slots = [];

        // For each slot number (1-48)
        for ($classNum = 1; $classNum <= $totalClassesPerDay; $classNum++) {
            $dayClasses = [];
            $hasClass = false;

            foreach ($days as $day) {
            $slotCode = $day . $classNum;

            // Find the first enrollment for this slot (regular)
            $enrollment = $enrollments->first(function ($enrollment) use ($slotCode) {
                return isset($enrollment->slots) && is_array($enrollment->slots) && in_array($slotCode, $enrollment->slots);
            });

            // Check if any enrollment has a rescheduledSlot for this slot
            $rescheduledEnrollment = $enrollments->first(function ($enrollment) use ($slotCode) {
                return $enrollment->rescheduledSlot && $enrollment->rescheduledSlot->slot_code === $slotCode;
            });

            if ($rescheduledEnrollment) {
                $student = $rescheduledEnrollment->student;
                $course = $rescheduledEnrollment->course;
                $teacher = $rescheduledEnrollment->teacher;

                $class = [
                'student_id'         => $rescheduledEnrollment->student_id ?? null,
                'course_id'          => $rescheduledEnrollment->course_id ?? null,
                'teacher_id'         => $rescheduledEnrollment->teacher_id ?? null,
                'enrollment_date'    => $rescheduledEnrollment->enrollment_date ?? null,
                'starting_date'      => $rescheduledEnrollment->starting_date ?? null,
                'ending_date'        => $rescheduledEnrollment->ending_date ?? null,
                'registration_no'    => $student->registration_no ?? null,
                'student_name'       => $student->full_name ?? null,
                'student_father'     => $student->father_name ?? null,
                'student_country'    => $student->country ?? null,
                'student_time_zone'  => $student->time_zone ?? null,
                'course_code'        => $course->course_code ?? null,
                'course_name'        => $course->course_name ?? null,
                'employee_id'        => $teacher->employee_id ?? null,
                'teacher_name'       => $teacher->full_name ?? null,
                'teacher_father'     => $teacher->father_name ?? null,
                'teacher_time_zone'  => $teacher->time_zone ?? null,
                'slot_code'          => $slotCode,
                'status'             => 'rescheduled',
                'enrollment_status'  => $rescheduledEnrollment->status ?? null,
                ];
                $enrollmentId = $rescheduledEnrollment->id ?? null;
                $hasClass = true;
            } elseif ($enrollment) {
                $student = $enrollment->student;
                $course = $enrollment->course;
                $teacher = $enrollment->teacher;

                $class = [
                'student_id'         => $enrollment->student_id ?? null,
                'course_id'          => $enrollment->course_id ?? null,
                'teacher_id'         => $enrollment->teacher_id ?? null,
                'enrollment_date'    => $enrollment->enrollment_date ?? null,
                'starting_date'      => $enrollment->starting_date ?? null,
                'ending_date'        => $enrollment->ending_date ?? null,
                'registration_no'    => $student->registration_no ?? null,
                'student_name'       => $student->full_name ?? null,
                'student_father'     => $student->father_name ?? null,
                'student_country'    => $student->country ?? null,
                'student_time_zone'  => $student->time_zone ?? null,
                'course_code'        => $course->course_code ?? null,
                'course_name'        => $course->course_name ?? null,
                'employee_id'        => $teacher->employee_id ?? null,
                'teacher_name'       => $teacher->full_name ?? null,
                'teacher_father'     => $teacher->father_name ?? null,
                'teacher_time_zone'  => $teacher->time_zone ?? null,
                'slot_code'          => $slotCode,
                'status'             => 'regular',
                'enrollment_status'  => $enrollment->status ?? null,
                ];
                $enrollmentId = $enrollment->id ?? null;
                $hasClass = true;
            } else {
                $class = null;
                $enrollmentId = null;
            }

            $dayClasses[] = [
                'enrollment_id' => $enrollmentId,
                'slot_id' => null,
                'day' => $day,
                'slot_code' => $slotCode,
                'class' => $class,
            ];
            }

            // Only add this slot if at least one class exists in any day
            if ($hasClass) {
            $slots[] = [
                'slot_number' => $classNum,
                'days' => $dayClasses,
            ];
            }
        }

        return response()->json($slots);
    }

   

    public function getTimeTableBySlotNumber($slotNumber)
    {
        // Validate slot number
        if ($slotNumber < 1 || $slotNumber > 48) {
            return response()->json(['error' => 'Invalid slot number.'], 400);
        }

        $teachers = Teacher::all();

        // Get all enrollments with relations
        $enrollments = Enrollment::with(['student', 'course', 'teacher', 'rescheduledSlot'])->get();

        $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];

        $weeklyClasses = [];

        foreach ($teachers as $teacher) {
            $teacherClasses = [];

            foreach ($days as $day) {
            $slotCode = $day . $slotNumber;

            // Find rescheduled enrollment for this teacher and slot
            $rescheduledEnrollment = $enrollments->first(function ($enrollment) use ($teacher, $slotCode) {
                return $enrollment->teacher_id == $teacher->id &&
                $enrollment->rescheduledSlot &&
                $enrollment->rescheduledSlot->slot_code === $slotCode;
            });

            // Find regular enrollment for this teacher and slot
            $regularEnrollment = $enrollments->first(function ($enrollment) use ($teacher, $slotCode) {
                return $enrollment->teacher_id == $teacher->id &&
                isset($enrollment->slots) &&
                is_array($enrollment->slots) &&
                in_array($slotCode, $enrollment->slots);
            });

            // Prefer rescheduled over regular
            $class = $rescheduledEnrollment ?: $regularEnrollment;

            if ($class) {
                $student = $class->student;
                $course = $class->course;
                $teacherObj = $class->teacher;

                $classData = [
                'student_id'         => $class->student_id ?? null,
                'course_id'          => $class->course_id ?? null,
                'teacher_id'         => $class->teacher_id ?? null,
                'enrollment_date'    => $class->enrollment_date ?? null,
                'starting_date'      => $class->starting_date ?? null,
                'ending_date'        => $class->ending_date ?? null,
                'registration_no'    => $student->registration_no ?? null,
                'student_name'       => $student->full_name ?? null,
                'student_father'     => $student->father_name ?? null,
                'student_country'    => $student->country ?? null,
                'student_time_zone'  => $student->time_zone ?? null,
                'course_code'        => $course->course_code ?? null,
                'course_name'        => $course->course_name ?? null,
                'employee_id'        => $teacherObj->employee_id ?? null,
                'teacher_name'       => $teacherObj->full_name ?? null,
                'teacher_father'     => $teacherObj->father_name ?? null,
                'teacher_time_zone'  => $teacherObj->time_zone ?? null,
                'slot_code'          => $slotCode,
                'status'             => $rescheduledEnrollment ? 'rescheduled' : 'regular',
                'enrollment_status'  => $class->status ?? null,

                ];
            } else {
                $classData = null;
            }

            $teacherClasses[] = [
                'day' => $day,
                'slot_code' => $slotCode,
                'class' => $classData,
            ];
            }

            // Sort teacherClasses by days order
            usort($teacherClasses, function ($a, $b) use ($days) {
            return array_search($a['day'], $days) <=> array_search($b['day'], $days);
            });

            $weeklyClasses[] = [
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->full_name,
            'employee_id' => $teacher->employee_id,
            'weekly_classes' => $teacherClasses,
            ];
        }

        // Sort weeklyClasses by teacher_name if needed
        usort($weeklyClasses, function ($a, $b) {
            return strcmp($a['teacher_name'], $b['teacher_name']);
        });

        return response()->json($weeklyClasses);
    }


    public function getTimeTableByStudent($studentId)
    {
        // Get all enrollments for the given student
        $enrollments = Enrollment::with(['student', 'course', 'teacher', 'rescheduledSlot'])
            ->where('student_id', $studentId)
            ->get();

        $timetable = [];
        $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        $totalClassesPerDay = 48;

        foreach ($days as $day) {
            for ($slotNum = 1; $slotNum <= $totalClassesPerDay; $slotNum++) {
                $slotCode = $day . $slotNum;

                // Prefer rescheduled slot if exists
                $rescheduledEnrollment = $enrollments->first(function ($enrollment) use ($slotCode) {
                    return $enrollment->rescheduledSlot && $enrollment->rescheduledSlot->slot_code === $slotCode;
                });

                $enrollment = $enrollments->first(function ($enrollment) use ($slotCode) {
                    return isset($enrollment->slots) && is_array($enrollment->slots) && in_array($slotCode, $enrollment->slots);
                });

                if ($rescheduledEnrollment) {
                    $student = $rescheduledEnrollment->student;
                    $teacher = $rescheduledEnrollment->teacher;
                    $course = $rescheduledEnrollment->course;

                    $class = [
                        'student_id'         => $rescheduledEnrollment->student_id ?? null,
                        'course_id'          => $rescheduledEnrollment->course_id ?? null,
                        'teacher_id'         => $rescheduledEnrollment->teacher_id ?? null,
                        'enrollment_date'    => $rescheduledEnrollment->enrollment_date ?? null,
                        'starting_date'      => $rescheduledEnrollment->starting_date ?? null,
                        'ending_date'        => $rescheduledEnrollment->ending_date ?? null,
                        'registration_no'    => $student->registration_no ?? null,
                        'student_name'       => $student->full_name ?? null,
                        'student_father'     => $student->father_name ?? null,
                        'student_country'    => $student->country ?? null,
                        'student_time_zone'  => $student->time_zone ?? null,
                        'course_code'        => $course->course_code ?? null,
                        'course_name'        => $course->course_name ?? null,
                        'employee_id'        => $teacher->employee_id ?? null,
                        'teacher_name'       => $teacher->full_name ?? null,
                        'teacher_father'     => $teacher->father_name ?? null,
                        'teacher_time_zone'  => $teacher->time_zone ?? null,
                        'slot_code'          => $slotCode,
                        'status'             => 'rescheduled',
                        'enrollment_status'  => $rescheduledEnrollment->status ?? null,
                    ];

                    $timetable[] = [
                        'day' => $day,
                        'slot_number' => $slotNum,
                        'slot_code' => $slotCode,
                        'class' => $class,
                    ];
                } elseif ($enrollment) {
                    $student = $enrollment->student;
                    $teacher = $enrollment->teacher;
                    $course = $enrollment->course;

                    $class = [
                        'student_id'         => $enrollment->student_id ?? null,
                        'course_id'          => $enrollment->course_id ?? null,
                        'teacher_id'         => $enrollment->teacher_id ?? null,
                        'enrollment_date'    => $enrollment->enrollment_date ?? null,
                        'starting_date'      => $enrollment->starting_date ?? null,
                        'ending_date'        => $enrollment->ending_date ?? null,
                        'registration_no'    => $student->registration_no ?? null,
                        'student_name'       => $student->full_name ?? null,
                        'student_father'     => $student->father_name ?? null,
                        'student_country'    => $student->country ?? null,
                        'student_time_zone'  => $student->time_zone ?? null,
                        'course_code'        => $course->course_code ?? null,
                        'course_name'        => $course->course_name ?? null,
                        'employee_id'        => $teacher->employee_id ?? null,
                        'teacher_name'       => $teacher->full_name ?? null,
                        'teacher_father'     => $teacher->father_name ?? null,
                        'teacher_time_zone'  => $teacher->time_zone ?? null,
                        'slot_code'          => $slotCode,
                        'status'             => 'regular',
                        'enrollment_status'  => $enrollment->status ?? null,
                    ];

                    $timetable[] = [
                        'day' => $day,
                        'slot_number' => $slotNum,
                        'slot_code' => $slotCode,
                        'class' => $class,
                    ];
                }
            }
        }

        // Sort timetable by day (starting from Monday) and then by slot_number
        $daysOrder = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        usort($timetable, function ($a, $b) use ($daysOrder) {
            $dayCmp = array_search($a['day'], $daysOrder) <=> array_search($b['day'], $daysOrder);
            if ($dayCmp === 0) {
            return $a['slot_number'] <=> $b['slot_number'];
            }
            return $dayCmp;
        });

        return response()->json($timetable);
    }





    /* public function getTimeTableBySlotNumber($slotNumber)
    {

        // Validate slot number
        if ($slotNumber < 1 || $slotNumber > 48) {
            return response()->json(['error' => 'Invalid slot number.'], 400);
        }

        $teachers = Teacher::all();

        // Get all enrollments with relations
        // Get all enrollments where any slot matches the given slot number (e.g., MON1, TUE1, etc.)
        $enrollments = Enrollment::with(['student', 'course', 'teacher'])->get();
        $enrollments->load('rescheduledSlot');



        $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];

        $weeklyClasses = [];

        foreach ($teachers as $teacher) {
            $teacherClasses = [];

            foreach ($days as $day) {
                $slotCode = $day . $slotNumber;

                $class = $enrollments->first(function ($enrollment) use ($teacher, $slotCode) {
                    return $enrollment->teacher_id == $teacher->id &&
                        isset($enrollment->slots) &&
                        is_array($enrollment->slots) &&
                        in_array($slotCode, $enrollment->slots);
                });

                if ($class) {
                    $student = $class->student;
                    $course = $class->course;
                    $teacherObj = $class->teacher;

                    $classData = [
                        'student_id'         => $class->student_id ?? null,
                        'course_id'          => $class->course_id ?? null,
                        'teacher_id'         => $class->teacher_id ?? null,
                        'enrollment_date'    => $class->enrollment_date ?? null,
                        'starting_date'      => $class->starting_date ?? null,
                        'ending_date'        => $class->ending_date ?? null,
                        'registration_no'    => $student->registration_no ?? null,
                        'student_name'       => $student->full_name ?? null,
                        'student_father'     => $student->father_name ?? null,
                        'student_country'    => $student->country ?? null,
                        'student_time_zone'  => $student->time_zone ?? null,
                        'course_code'        => $course->course_code ?? null,
                        'course_name'        => $course->course_name ?? null,
                        'employee_id'        => $teacherObj->employee_id ?? null,
                        'teacher_name'       => $teacherObj->full_name ?? null,
                        'teacher_father'     => $teacherObj->father_name ?? null,
                        'teacher_time_zone'  => $teacherObj->time_zone ?? null,
                    ];
                } else {
                    $classData = null;
                }

                $teacherClasses[] = [
                    'day' => $day,
                    'slot_code' => $slotCode,
                    'class' => $classData,
                ];
            }

            $weeklyClasses[] = [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->full_name,
                'employee_id' => $teacher->employee_id,
                'weekly_classes' => $teacherClasses,
            ];
        }

        return response()->json($weeklyClasses);
        
    } */



}
