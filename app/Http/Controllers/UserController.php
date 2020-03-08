<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['store']]);
    }

    /**
     * Get all users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        
        return UserResource::collection($users)
            ->additional(['success' => true ]);
    }

    /**
     * Create a user
     * 
     * @todo no spaces allwed in the username
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedInput = $this->validateInput($request);

        $validatedInput['password'] = app('hash')->make($validatedInput['password']);

        if ($request->has('profile_photo')) {
            $validatedInput['profile_photo'] = $this->saveProfilePhoto($request);
        }

        $user = User::create($validatedInput);

        return new UserResource($user);
    }

    /**
     * Get a user
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return new UserResource($user);
    }

    /**
     * Edit a user's information
     *
     * @todo if the user has updoaded a new profile photo, remove the old one form the iamge folder
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedInput = $this->validateInput($request, $user);

        if ($request->filled('password')) {
            $validatedInput['password'] = app('hash')->make($validatedInput['password']);
        }

        if ($request->has('profile_photo')) {
            $validatedInput['profile_photo'] = $this->saveProfilePhoto($request);
        }

        $user->update($validatedInput);

        return new UserResource($user);
    }

    /**
     * Delete a user
     *
     * @todo also remove the photo form image folder. the same functinality is needed in update. 
     *          this could be handled in PhotoService.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();
    }

    /**
     * Saves the profile photo.
     * 
     * Saves the profile photo in the /images/profiles folder and returns the file path.
     *
     * @todo make this a service: PhotoService
     * @param  \Illuminate\Http\Request  $request
     * @return string  profile photo path
     */
    public function saveProfilePhoto(Request $request)
    {
        $image = $request['profile_photo'];

        $imageFileExtention =   $image->guessExtension()        ?? 
                                $image->guessClientExtension()  ?? 
                                $image->getClientOriginalExtension();
        
        $imageFileName = $request['lastname'] . '-' . time() . '.' . $imageFileExtention;
        
        $destinationFolder = app()->basePath('public') . "/images/profiles";

        $isProfilePhotoSaved = $image->move($destinationFolder, $imageFileName);

        if ($isProfilePhotoSaved) {
            return "images/profiles/$imageFileName";
        }
    }

    /**
     * Validates user's input
     * 
     * @todo improve the algorithm is possible
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return Array  validated input
     */
    private function validateInput($request, $user = null)
    {
        $rules = [
            'firstname'     => ['required', 'string',' min:3', 'regex:/^[A-Za-z]+$/'],
            'lastname'      => ['required', 'string', 'min:3', 'regex:/^[A-Za-z]+$/'],
            'email'         => ['required', 'email', 'unique:users'],
            'username'      => ['required', 'string', 'unique:users', 'min:3', 'regex:/^\S*$/'],
            'password'      => ['required', 'min:4', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120']
        ];

        if ($request->isMethod('put')) {

            $rules['email'][2] = Rule::unique('users')->ignore($user);
            $rules['username'][2] = Rule::unique('users')->ignore($user);
            
            foreach($rules as $key => $value) {
                if ($rules[$key][0] === 'required') {
                    $rules[$key][0] = 'sometimes';
                }
            }
            
        }

        return $this->validate($request, $rules);
    }
}
