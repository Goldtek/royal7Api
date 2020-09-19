<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\RolePermission;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{

    
    public function createUser(Request $request){
        if(empty($request->email)){
            return $this->missingField('Email address is missing.');
        } else if(empty($request->firstname)){
            return $this->missingField('Firstname is missing.');
        }else if(empty($request->lastname)){
            return $this->missingField('Lastname is missing.');
        }else if(empty($request->roleId)){
            return $this->missingField('Role is missing.');
        }else if(empty($request->schoolId)){
            return $this->missingField('school ID is missing.');
        }else if(empty($request->password)){
            return $this->missingField('Password is missing.');
        } else if(empty($request->phone)){
            return $this->missingField('Phone is missing.');
        }
        else if(empty($request->religion)){
            return $this->missingField('Religion is missing.');
        }
        else if(empty($request->bloodgroup)){
            return $this->missingField('Blood group is missing.');
        } else if(empty($request->midname)){
            return $this->missingField('Middle name is missing.');
        } else if(empty($request->gender)){
            return $this->missingField('Gender is missing.');
        }
        // validation if the user created is a student using role
        if($request->roleId === 4){
            if(empty($request->classId)){
                return $this->missingField('Student cannot be created without been assigned to a class.');
            } else if(empty($request->sessionId)){
                return $this->missingField('Student cannot be created without a session');
            }
        }

        try {
            //upload photo

            $user = new User;
            $user->firstname = $request->firstname;
            $user->midname = $request->midname;
            $user->lastname = $request->lastname;
            $user->birthday = $request->birthday;
            $user->phone = $request->phone;
            $user->bloodgroup = $request->bloodgroup;
            $user->gender = $request->gender;
            $user->religion = $request->religion;
            $user->role_id = $request->roleId;
            $user->school_id = $request->schoolId;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            // $user->photo = $request->photoPath;
            // apply carbon here
            if($user->save()){
                if($request->roleId === 4){
                    // create student if the role is a student
                    $student = new Student;
                    $student->class_id = $request->classId;
                    $student->session_id = $request->sessionId;
                    $student->user_id = $user->id;
                    $student->school_id = $request->schoolId;
                    $student->save();
                }
                return $this->success('Account has been created for '.$request->firstname." ".$request->lastname);
            }
        } catch (\Exception $e) {
            return $this->fail("Unable to create user Account, Please try again.".$e->getMessage());
        }
    }

    public function viewAllTeachers(Request $request){
        try {
            $teachers = User::where('roleId', '=', 3)->paginate(15);
            return response()->json([
                'teachers' => $teachers,
                'success' => true
                ]);

        } catch (\Exception $e) {
            return $this->fail("Error viewing all teachers. ".$e->getMessage());
        }
    }

    public function createPermission(Request $request){ 
        try {
            if(empty($request->name)){
                return $this->missingField("The Permission Name is required!");
             }
             $permission = new Permission;
             $permission->name = $request->name;
             if($permission->save()){
                return $this->success($request->name.' Permission has been successfully created'); 
             }

        } catch (\Exception $e) {
            return $this->fail("Error fetching permissions for Role. ".$e->getMessage());
        }
    }

    public function UpdatePermission(Request $request){ 
        try {
            if(empty($request->id)){
                return $this->missingField("The Permission id is required!");
             } else if(empty($request->name)){
                return $this->missingField("The Permission  name is required for update.");
             }
             $permission = Permission::find($request->id);
             if(!$permission){
                return $this->fail("Permission does not exist");
             }
             
             if($permission->update($request)){
                return $this->success($request->name.' Permission has been successfully created'); 
             }

        } catch (\Exception $e) {
            return $this->fail("Error updating permission. ".$e->getMessage());
        }
    }

    public function FetchPermissionsForRole(Request $request){ 
        try {
            if(empty($request->roleId)){
                return $this->miuserIdssingField("The Role Id is required!");
             }
             $permissions = RolePermission::where('role_id', '=',$request->roleId)->get();

             return response()->json([
                'permissions' => $permissions,
                'success' => true
                ]);
             // pass it into a resource

        } catch (\Exception $e) {
            return $this->fail("Error fetching permissions for Role. ".$e->getMessage());
        }
    }

    public function login(Request $request){
        try {
            $credentials = $request->only('email', 'password'); 
            $token =  Hash::make($request->password);
            if(Auth::attempt($credentials)){ 
                // get user details and permission of the user based on the role
                return response()->json([
                    'user' => Auth()->user(),
                    'token' => Auth()->user()->createToken('authToken')->accessToken,
                    'permissions' => Auth()->user()->UserRoles(),
                     'success' => true,
                ]);
            }

            return $this->fail("User Login failed");
        } catch (\Exception $e) {
            return $this->fail("Error Logging in. ".$e->getMessage());
        }
    
    }

    public function viewRoles(Request $request) {
        $roles = Role::get();
        return response()->json([
            'roles' => $roles,
             'success' => true,
        ]);
    }

    public function logOut(){
        Auth::logout();
        return $this->success('User logged Out');
    }
    
}
