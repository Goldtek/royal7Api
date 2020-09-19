<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\SchoolAdministrator;
use App\Models\CreateAccount;
use App\Models\RolePermission;
use App\Models\ClassInfo;
use App\Models\Subject;
use App\Models\AssignedSubject;
use App\Models\ClassSubjects;
use App\Models\TimeTable;


class SchoolController extends ApiController
{
        public function createAdminMail(Request $request){
            //recieved email
            if(empty($request->email)) {
                return $this->missingField("email is required!");
            }
            try {
                // generate random code
                $code = md5(uniqid().time());
                $email = $request->email;
                Mail::to($email)->send(new SchoolAdministrator($email,$code));
                //store in database
                $createAccount = new CreateAccount;
                $createAccount->code = $code;
                $createAccount->email = $email;
                $createAccount->save();
        
                return $this->success('Email has been successfully sent to '.$email.'.');
            } catch (\Exception $e) {
                return $this->fail("Invalid Account, Please Try Again.");
            }
        }

        public function confirmEmail(Request $request) {
            if(empty($request->email)) {
                return $this->missingField("email Field is required!");
            } else if(empty($request->code)){
                return $this->missingField("code Field is required!");
            } 
            try {
                $account =  DB::table('create_accounts')->where('email',$request->email)->where('code',$request->code)->first();
                if($account) {
                    DB::table('create_accounts')->where('email',$request->email)->where('code',$request->code)->delete();
                    return $this->success('Email Confirmation complete');
                } else {
                    return $this->fail("Invalid confirmation code.");
                }
            } catch (\Exception $e) {
                return $this->fail("Invalid Account, Please Try Again.");
            }

        }

        public function createSchool(Request $request){
        
            if(empty($request->schoolName)) {
                return $this->missingField("shool name of school is required!");
            } else if(empty($request->email)){
                return $this->missingField("email address is required!");
            } else if(empty($request->address)){
                return $this->missingField("school address field is required!");
            } else if(empty($request->phone)){
                return $this->missingField("phone field is required!");
            } else if(empty($request->about)){
                return $this->missingField("about field of school is required!");
            } else if(empty($request->password)){
                return $this->missingField("password field is required!");
            }

            try {
                $school = new School;
                $school->name = $request->schoolName;
                $school->address = $request->address;
                $school->about = $request->about;
                $school->phone = $request->phone;

                if($school->save()) {
                    $user = new User;
                    $user->school_id = $school->id;
                    $user->email = $request->email;
                    if(!empty($request->classId)){
                        // if the user is a student, he will have a class id
                        $user->classId = $request->classId;
                    }
                    $user->role_id = 1;
                    $user->password = Hash::make($request->password);
                    $user->save();
                    $token = $user->createToken('authToken')->accessToken;
                    
                    return response()->json([
                    //   'user' => $user,
                        'token' => $token,
                        'permissions' => $user->UserRoles(),
                        'success' => true,
                        'mesage' => 'School account has been successfully created.'
                    ]);
                }
            } catch (\Exception $e) {
                return $this->fail("Error: ".$e);
            }

        }
        // assign subject to teacher
        public function assignSubject(Request $request){
            if(empty($request->userId)) {
                return $this->missingField("userId is required!");
            } else if(empty($request->subjectId)){
                return $this->missingField("Subject Id is required!");
            } else if(empty($request->classId)){
                return $this->missingField("Class Id field is required!");
            } else if(empty($request->sessionId)){
                return $this->missingField("Session Id is required!");
            } else if (empty($request->schoolId))  {
                return $this->missingField("school Id is required!");
            }

            try {
                $assignedSubject = new AssignedSubject;
                $assignedSubject->userId = $request->userId;
                $assignedSubject->subjectId = $request->subjectId;
                $assignedSubject->classId = $request->classId;
                $assignedSubject->sessionId = $request->sessionId;
                $assignedSubject->school_id = $request->schoolId;

                if($assignedSubject->save()) {
                    return $this->success('Subject successfuly assigned');
                }

            } catch (\Exception $e) {
                return $this->fail("Unable to assign Subject to, Please try again.");
            }
        }

        // teachers ability to view subjects assigned to him/her
        public function assignedSubjects(Request $request){
            if (empty($request->userId)) {
                return $this->missingField("userId is required!");
            } else if (empty($request->sessionId)){
                return $this->missingField("Session Id is required!");
            } else if (empty($request->schoolId))  {
                return $this->missingField("school Id is required!");
            }
            try {
                $assigned =  DB::table('assigned_subjects')
                ->join('class_subjects', function($join){
                        $join->on('subjects.id', '=', 'assigned_subjects.subjectId')
                        ->on('subjects.school_id', '=' ,'assigned_subjects.schoolId');
                    })
                    ->join('subjects', 'subjects.id', '=','class_subjects.subjectId')
                    ->join('users','id', '=' ,'assigned_subjects.userId')
                    ->where('userId',$request->userId)
                    ->where('sessionId',$request->sessionId)
                    ->where('school_id',$request->schoolId)
                    ->select('users.firstname', 'users.midname','users.lastname','subjects.name')
                ->get();
                return response()->json([
                        'assignedSubjects' => $assigned,
                        'success' => true
                    ]);

            } catch (\Exception $e) {
                return $this->fail("Unable to fetched assigned subjects, Please try again.");
            }
        }

        // view subjects in a class
        public function viewSubjectsForClass(Request $request){
            if (empty($request->schoolId)) {
                return $this->missingField("School Id is required!");
            } else if (empty($request->classId)){
                return $this->missingField("Class Id is required!");
            }
            try {

                $classSubjects =  DB::table('class_subjects')
                ->where('class_id',$request->classId)
                ->where('school_id',$request->schoolId)
                ->select('subjects.name','class_subjects.id') 
                ->get();

                return response()->json([
                        'subjectsinClass' => $classSubjects,
                        'success' => true
                        ]);
            } catch (\Exception $e) {
                return $this->fail("Error viewing subjects. ".$e->getMessage());
            }
        }


       // view students in a class
        public function viewStudentsInClass(Request $request){
            if (empty($request->schoolId)) {
                return $this->missingField("School Id is required!");
            } else if (empty($request->classId)){
                return $this->missingField("Class Id is required!");
            } else if (empty($request->sessionId)){
                return $this->missingField("session Id is required!");
            }
        
            try {
              
                $students =  DB::table('students')
                ->join('users','id', '=' ,'students.userId')
                ->where('class_id',$request->classId)
                ->where('session_id',$request->sessionId)
                ->where('school_id',$request->schoolId)
                ->select('users.*') 
                ->get();

                return response()->json([
                        'students' => $students,
                        'success' => true
                        ]);

            } catch (\Exception $e) {
                return $this->fail("Error viewing student. ".$e->getMessage());
            }
        }

         // view the grades of a student in a class
       
        public function profilePage(Request $request){
            try {


            } catch (\Exception $e) {
                return $this->fail("Error viewing Profile Page. ".$e->getMessage());
            }
        }


            // view all students in a school
        public function viewAllStudents(Request $request){
                if (empty($request->schoolId)) {
                    return $this->missingField("School Id is required!");
                } else if (empty($request->sessionId)){
                    return $this->missingField("session Id is required!");
                }
                try {
    
                    $students =  DB::table('students')
                    ->join('users','id', '=' ,'students.userId')
                    ->where('session_id',$request->sessionId)
                    ->where('school_id',$request->schoolId)
                    ->select('users.*') 
                    ->get();
    
                    return response()->json([
                            'allStudents' => $students,
                            'success' => true
                            ]);

            } catch (\Exception $e) {
                return $this->fail("Error viewing students. ".$e->getMessage());
            }
        }

    

        // grade each student
        public function gradeStudent(Request $request){
            try {
                if(empty($request->class_id)) {
                    return $this->missingField("Class field is required!");
                }else if(empty($request->session_id)) {
                    return $this->missingField("Session field is required!");
                } else if(empty($request->student_id)) {
                    return $this->missingField("Student field is required!");
                } else if(empty($request->school_id)) {
                    return $this->missingField("School field is required!");
                } else if(empty($request->subject_id)) {
                    return $this->missingField("Subject field is required!");
                } else if(empty($request->exam)) {
                    return $this->missingField("Exam field is required!");
                } else if(empty($request->section_id)) {
                    return $this->missingField("Section field is required!");
                }


            } catch (\Exception $e) {
                return $this->fail("Error viewing subjects. ".$e->getMessage());
            }
        }


        public function setTimeTable(Request $request, $id){
            if(empty($request->classId)) {
                return $this->missingField("Class field is required!");
            }else if(empty($request->sessionId)) {
                return $this->missingField("Session field is required!");
            } else if(empty($request->start_time)) {
                return $this->missingField("StartTime field is required!");
            } else if(empty($request->end_time)) {
                return $this->missingField("EndTime field is required!");
            } else if(empty($request->date)) {
                return $this->missingField("Date field is required!");
            } else if(empty($request->userId)) {
                return $this->missingField("Teacher field is required!");
            } else if(empty($request->subjectId)) {
                return $this->missingField("Subject field is required!");
            }else if(empty($request->schoolId)) {
                return $this->missingField("SchoolId field is required!");
            }
            try {
                $timetable = new TimeTable;
                $timetable->school_id = $request->schoolId;
                $timetable->start_time = $request->start_time;
                $timetable->end_time = $request->end_time;
                $timetable->date = $request->date;
                $timetable->userId = $request->userId;
                $timetable->sessionId = $request->sessionId;
                $timetable->subjectId = $request->subjectId;
                $timetable->classId = $request->classId;
                
            
                if($timetable->save()){
                    return $this->success('Exam TimeTable has been created. ');
                }
            } catch (\Exception $e) {
                return $this->fail("Unable to create Class ".$e->getMessage());
            }
        }

        public function TimeTable(Request $request){
            try {
                $timetable = DB::table('times_table')
                ->join('class_subjects', 'class_subjects.id', '=', 'times_table.subjectId')
                ->join('subjects', 'class_subjects.subjectId', '=', 'subjects.id')
                ->join('users', 'users.id', '=', 'times_table.userId')
                ->select('users.firstname', 'users.midname', 'users.lastname', 'subjects.name',
                'times_table.start_time','times_table.end_time', 'times_table.date')
                ->orderBy('teambudget.id','DESC')
                ->where('times_table.id',$request->schoolId)
                ->where('times_table.id',$request->sessionId)
                ->get();

                return response()->json([
                    'timetable' => $timetable,
                    'success' => true
                    ]);
            } catch (\Exception $e) {
                return $this->fail("Error viewing TimeTable. ".$e->getMessage());
            }

        }




        public function createRolePermission (Request $request) {
            try {
                if(empty($request->permissions)){
                    return $this->notFound("permissions are required");
                }
                foreach($request->permissions as $data) {
                    $pem = new RolePermission();
                    $pem ->role_id = $data['rolePermission']['roleId'];
                    $row->save();   
                }
                return $this->success('Role Permissions successfully created.');
            } catch (\Exception $e) {
                return $this->fail("Error creating role permissions. ".$e->getMessage());
            }
    
        }

        public function createClass(Request $request){
            if(empty($request->name)){
                return $this->missingField('Name Field is missing.');
            }

            try {
                $class = new ClassInfo;
                $class->school_id = $request->schoolId;
                $class->name = $request->name;
                
                if($class->save()){
                    return $this->success('Class has been created for '.$request->name);
                }
            } catch (\Exception $e) {
                return $this->fail("Unable to create Class ".$e->getMessage());
            }
        }

        public function getClasses(Request $request){
            if(empty($request->schoolId)){
                return $this->missingField('School Id Field is missing.');
            }

            try {
                $classes = ClassInfo::where('school_id', '=', $request->schoolId)->paginate(15);

                return response()->json([
                    'classes' => $classes,
                    'success' => true
                ]);

            } catch (\Exception $e) {
                return $this->fail("Unable to create Class ".$e->getMessage());
            }
        }

        //create subjects in each class
        public function createSubjectInClass(Request $request){
            if(empty($request->name)){
                return $this->missingField('Name Field is missing.');
            } else if(empty($request->subjectCode)) {
                return $this->missingField('Subject Code Field is missing.');
            } else if(empty($request->schoolId)) {
                return $this->missingField('School ID Field is missing.');
            } else if(empty($request->classId)) {
                return $this->missingField('Class ID Field is missing.');
            }  else if(empty($request->subjectId)) {
                return $this->missingField('Subject ID Field is missing.');
            }

            try {
                $class = new ClassSubjects;
                $class->school_id = $request->schoolId;
                $class->class_id = $request->classId;
                $class->subject_id = $request->subjectId;
                $class->code = $request->subjectCode;
            
                if($class->save()){
                    return $this->success('Class Subject has been created for '.$request->name);
                }
            } catch (\Exception $e) {
                return $this->fail("Unable to create Class Subject ".$e->getMessage());
            }
        }

        public function createSubject(Request $request){
            if(empty($request->name)){
                return $this->missingField('Name Field is missing.');
            }  else if(empty($request->schoolId)) {
                return $this->missingField('School ID Field is missing.');
            } else if(empty($request->userId)) {
                return $this->missingField('User ID Field is missing.');
            } 

            try {
                $subject = new Subject;
                $subject->name = $request->name;
                $subject->school_id = $request->schoolId;
                $subject->createdBy = $request->userId;
                
                if($subject->save()){
                    return $this->success(''.$request->name." has been created.");
                }
            } catch (\Exception $e) {
                return $this->fail("Unable to create subject ".$e->getMessage());
            }
        }

        public function destroy($id){

            // if ($request->hasFile('photo')) {

            //     if ($request->file('photo')->isValid()) {
            //         $file = $request->file('photo');
            //         $path = $request->photo->path();
            //         // $school->active=$request['logo'];
            //         //store photo
            //         $path = $request->photo->store('images');
            //     }
            // }

        }
}


// check the migration and confirm the ids are auto-increment
// remote date_created and updated from roles creation or add carbon:now to it