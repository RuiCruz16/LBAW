<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Project;
use App\Models\User;

class ProjectInvitationEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $project;
    public $inviter;
    public $invitee;

    public function __construct(Project $project, User $inviter, User $invitee)
    {
        $this->project = $project;
        $this->inviter = $inviter;
        $this->invitee = $invitee;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->invitee->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'inviter_username' => $this->inviter->username,
            'message' => "{$this->inviter->username} convidou você para o projeto '{$this->project->name}'.",
            'invited_at' => now()->toDateTimeString(),
        ];
    }
}
