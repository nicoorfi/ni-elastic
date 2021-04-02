<?php

declare(strict_types=1);

namespace App\Notifications\Cluster;

use App\Models\User;
use App\Notifications\UserNotification;

class ClusterIsRunning extends UserNotification
{
    public function __construct(private string $clusterName, private string $projectName)
    {
    }

    /**
     * @param User $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Cluster',
            'body' => "Your cluster <b>{$this->clusterName}</b> is up and running.",
            'project' => $this->projectName
        ];
    }
}
