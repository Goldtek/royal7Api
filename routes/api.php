<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Api for royal 7
|
*/

Route::get('/', function () {
    $result = ['result' => 'OK',
               'data' => 'No Data Yet'];
    $response = \Response::json($result)->setStatusCode(200, 'Success');
    return $response;
  });



// route to generate confirmation mail for school admin
Route::post('send/mail', 'SchoolController@createAdminMail');


// route to confirm school admin email
Route::post('admin/confirm', 'SchoolController@confirmEmail');

//route to create school and admin
Route::post('school/admin', 'SchoolController@createSchool');

//route to login
Route::post('user/login', 'UserController@login');

//route to logout
Route::post('user/logout', 'UserController@logOut');


  Route::middleware('auth:api')->group( function () {
    
    //route to create use[teacher/student/staff]
    Route::post('user/create', 'UserController@createUser');

    //route to assign teacher to subject
    Route::post('assign/subject', 'SchoolController@assignSubject');

   

    //route to view all students
    Route::post('view/students', 'UserController@ViewAllStudents');

    //route to view all students in a specific class
    Route::post('view/students/class', 'UserController@ViewStudentsInClass');

    //route to view all teachers
    Route::post('view/teachers', 'UserController@viewAllTeachers');

    // route create permission
    Route::post('permission/create', 'UserController@createPermission');

    // route to Update Permission setting
    Route::post('permission/create', 'UserController@UpdatePermission');

    // route for role creation
    Route::post('role/permission/create', 'SchoolController@createRolePermission');

    // route for fetchpermissions for role
    Route::post('roles/permission/fetch', 'UserController@FetchPermissionsForRole');

    // route to view roles
    Route::get('roles/view', 'UserController@viewRoles');

    // route for creation of subject
    Route::post('subject/create', 'SchoolController@createSubject');

    // route for  creation of class
    Route::post('class/create', 'SchoolController@createClass');

    // route for  add subject of class
    Route::post('class/subject/create', 'SchoolController@createSubjectInClass');

     //route to view all subjects added to a class
     Route::post('subjects', 'SchoolController@viewSubjectsForClass');

    // route for teachers ability to view subjects assigned to him/her
    Route::post('teachers/view/assigned', 'SchoolController@assignedSubjects');

     // route for setExamTimeTable
     Route::post('exam/timetable/create', 'SchoolController@setTimeTable');

    // route for view Exam TimeTable
     Route::post('exam/timetable/view', 'SchoolController@TimeTable');

     // route for view all Classes For a School
     Route::post('classes/view', 'SchoolController@getClasses');

 });

 