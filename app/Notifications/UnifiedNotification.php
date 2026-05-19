<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UnifiedNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $url;
    public $icon;
    public $color;
    public $feature; 

    public function __construct($title, $message, $url, $icon = 'ri-notification-3-line', $color = 'text-primary', $feature = 'UMUM')
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->icon = $icon;
        $this->color = $color;
        $this->feature = $feature; 
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
            'color' => $this->color,
            'feature' => $this->feature,
        ];
    }
}