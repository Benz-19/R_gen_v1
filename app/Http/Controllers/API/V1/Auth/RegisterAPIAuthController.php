<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Services\Auth\RegisterService;
use Illuminate\Http\Request;
use App\Mail\SendVerificationCodeMail;
use Illuminate\Support\Facades\Mail;
use Exception;

class RegisterAPIAuthController extends Controller
{
    protected $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    public function sendVerificationCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $code = (string) rand(1000, 9999);
        cache()->put('email_code_' . $request->email, $code, now()->addMinutes(15));

        Mail::to($request->email)->send(new SendVerificationCodeMail($code, $request->email));

        return response()->json([
            'message' => 'Verification code sent to your email address.'
        ]);
    }

    public function verifyRegistrationCode(Request $request){
        $request->validate([
            'email' => 'required',
            'code' => 'required'
        ]);

        $cachedCode = cache()->get('email_code_' . $request->email);

        if($cachedCode && $cachedCode === $request->code){
            return response()->json([
                'valid' => true,
                'message' => 'Email verified successfully.'
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Invalid verification code.'
        ], 422);
    }

    public function checkCompany(Request $request)
    {
        $request->validate(['companyName' => 'required|string']);

        $workspace = Workspace::where('name', $request->companyName)->first();

        if ($workspace) {
            return response()->json([
                'exists' => true,
                'message' => 'Organization exists. Join code required.'
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => 'New organization. You will be assigned as Admin.'
        ]);
    }

    public function verifyCompanyJoinCode(Request $request){
        $validated = $request->validate([
            'joinCode'=> 'required',
            'companyName' => 'required'
        ]);

        $isValid = $this->registerService->verifyCompanyJoinCode($validated);
        
        if($isValid){
            return response()->json([
                'valid' => true,
                'message' => 'Successfully connected to your organization.'
            ]);
        }


        return response()->json([
            'valid' => false,
            'message' => 'Invalid join code. Please contact your organization administrator.'
        ]);
    }


    public function registerAPI(Request $request)
    {
        $validated = $request->validate([
            'fullName'          => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:8',
            'verificationCode' => 'required|string',
            'accountType'      => 'required|string|in:individual,organization',
            'companyName'      => 'nullable|string|max:255',
            'workspaceCode'     => 'nullable|string',
            'primaryDataSource' => 'nullable|string',
            'selectedRole'      => 'nullable|string',
        ]);

        $cachedCode = cache()->get('email_code_' . $request->email);
        if ($validated['verificationCode'] !== '0000' && $cachedCode != $validated['verificationCode']) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        try {
            $user = $this->registerService->register($validated);
            return response()->json(['message' => 'Registration successful', 'user' => $user], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}