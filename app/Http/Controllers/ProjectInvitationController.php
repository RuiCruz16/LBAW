<?php

namespace App\Http\Controllers;

use App\Mail\ProjectInvitationMail;
use App\Models\ProjectInvitation;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProjectInvitationController extends Controller
{

    public function create(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:project,id',
            'receiver_id' => 'required|exists:users,id',
            'invitation_message' => 'string',
        ]);

        $project = Project::findOrFail($request->project_id);
        $receiver = User::findOrFail($request->receiver_id);

        if($receiver->is_admin) {
            return redirect()->back()->with('error', 'You cannot invite an admin');
        }

        if ($project->users->contains($receiver->id)) {
            return redirect()->back()->with('error', 'The user is already a member');
        }

        $existingInvitation = ProjectInvitation::where('project_id', $request->project_id)
            ->where('receiver_id', $request->receiver_id)
            ->where('response', false)
            ->first();

        if ($existingInvitation) {
            return redirect()->back()->with('error', 'User is allread invited');
        }

        ProjectInvitation::create([
            'project_id' => $request->project_id,
            'receiver_id' => $request->receiver_id,
            'invitation_message' => $request->invitation_message,
            'sender_id' => auth()->id(),
            'sent_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Invitation sent successfully');
    }
    
    public function show($projectId)
    {
        $project = Project::findOrFail($projectId);
        $users = User::where('id', '!=', auth()->id())->get(); 
        return view('pages.invite_user', compact('project', 'users'));
    }
    
    public function index(Request $request)
    {
        $user = auth()->user();
        $invitations_received = ProjectInvitation::where('receiver_id', $user->id)
            ->where('response', false)
            ->paginate(2, ['*'], 'page_invitations_received');

        $invitations_sent = ProjectInvitation::where('sender_id', $user->id)
            ->where('response', false)
            ->paginate(2, ['*'], 'page_invitations_sent');

        $responses = ProjectInvitation::where('receiver_id', $user->id)
            ->where('response', true)
            ->paginate(1, ['*'], 'page_responses');
            
    

        return view('pages.project_notifications', compact('invitations_received', 'invitations_sent', 'responses'));
    }

    public function accept(ProjectInvitation $invitation)
    {
        $user = auth()->user();
        
        if ($user->id !== $invitation->receiver_id) {
            return redirect()->back()->with('error', 'You do not have the permission');
        }

        $project = Project::find($invitation->project_id);
        
        if (!$project->users->contains($user->id)) {
            $project->users()->attach($user->id);
        }
        
        ProjectInvitation::create([
            'project_id' => $invitation->project_id,
            'receiver_id' => $invitation->sender_id, 
            'sender_id' => $user->id,
            'invitation_message' => "The user " . $user->username . " accepted the invitation.",
            'response' => true,
            'sent_at' => now(),
        ]);

        $invitation->delete();

        return redirect()->back()->with('success', 'Invite accepted');
    }

    public function createByEmail(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:project,id',
            'email' => 'required|email',
            'invitation_message' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No user found with this email.'
            ], 404);
        }

        $token = Str::random(60);

        DB::table('project_mail_invitation')->updateOrInsert(
            ['email' => $request->email, 'project_id' => $request->project_id],
            ['token' => $token, 'created_at' => now()]
        );

        $mailData = [
            'project_name' => Project::find($request->project_id)->project_name,
            'sender_name' => Auth::user()->username,
            'message' => $request->invitation_message ?? 'You have been invited to join the project.',
            'token' => $token,
        ];

        try {
            Mail::to($request->email)->send(new ProjectInvitationMail($mailData));
            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invitation email.'
            ], 500);
        }
    }

    public function acceptByEmail($token)
    {
        $invitation = DB::table('project_mail_invitation')->where('token', $token)->first();

        if (!$invitation) {
            return back()->with('error', 'Invalid or expired invitation.');
        }

        $user = User::where('email', $invitation->email)->first();

        DB::table('project_role')->insert([
            'user_id' => $user->id,
            'project_id' => $invitation->project_id,
            'user_role' => 'ProjectMember',
        ]);

        DB::table('project_mail_invitation')->where('token', $token)->delete();

        $project = Project::find($invitation->project_id);

        return view('pages.invite_confirmation', [
            'project_name' => $project->project_name,
            'user_name' => $user->username
        ]);
    }
    
    public function reject(ProjectInvitation $invitation)
    {
        $user = auth()->user();
        
        if ($user->id !== $invitation->receiver_id) {
            return redirect()->back()->with('error', 'You do not have the permission');
        }
        
        ProjectInvitation::create([
            'project_id' => $invitation->project_id,
            'receiver_id' => $invitation->sender_id, 
            'sender_id' => $user->id,
            'invitation_message' => "The user " . $user->username . " rejected your invite.",
            'response' => true,
            'sent_at' => now(),
        ]);
        
        $invitation->delete();

        return redirect()->back()->with('success', 'Invite rejected');
    }

        
    public function deleteResponse(ProjectInvitation $invitation)
    {
        $user = auth()->user();
        
        if ($user->id !== $invitation->receiver_id) {
            return redirect()->back()->with('error', 'You do not have the permission');
        }
        
        $invitation->delete();
        
        return redirect()->back()->with('success', 'Response deleted');
    }

    public function deleteInvitation(ProjectInvitation $invitation)
    {
        $user = auth()->user();
        
        if ($user->id !== $invitation->sender_id) {
            return redirect()->back()->with('error', 'You do not have the permission');
        }
        
        $invitation->delete();
        
        return redirect()->back()->with('success', 'Invitation deleted');
    }
}

